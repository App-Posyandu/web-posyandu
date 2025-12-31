<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use App\Imports\PosyanduImport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PosyanduTemplateExport;
use App\Models\UserHistory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PosyanduController extends Controller
{
    const PROVINCE_ID = 33;
    const API_TIMEOUT = 10;
    const CACHE_TTL = 3600;

    private function fetchWilayahData($endpoint, $cacheKey)
    {
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($endpoint) {
            try {
                $response = Http::timeout(self::API_TIMEOUT)
                    ->retry(2, 100)
                    ->get(env('API_WILAYAH_URL') . $endpoint);

                if ($response->successful()) {
                    return $response->json();
                }

                return ['data' => []];
            } catch (\Exception $e) {
                Log::error("Wilayah API Error: {$endpoint}", ['error' => $e->getMessage()]);
                return ['data' => []];
            }
        });
    }

    public function index(Request $request)
    {
        $query = Posyandu::with('users')->latest();

        if ($request->filled('search')) {
            $query->where('nama_posyandu', 'like', '%' . $request->search . '%')
                ->orWhere('desa', 'like', '%' . $request->search . '%')
                ->orWhere('kecamatan', 'like', '%' . $request->search . '%')
                ->orWhere('kabupaten', 'like', '%' . $request->search . '%')
                ->orWhereHas('users', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->where('role', 'ketua-kader');
                });
        }
        $posyandus = $query->paginate(10)->withQueryString();

        return view('admin.posyandu.index', compact('posyandus'));
    }

    public function create()
    {
        $currentUser = Auth::user();

        // ✅ Jika user adalah Kabid, kabupaten/kota sudah fixed
        if ($currentUser->role === 'kabid' && $currentUser->kabupaten) {
            $kabupatens = null; // Tidak perlu dropdown kabupaten
            $fixedWilayah = [
                'nama' => $currentUser->kabupaten,
                'jenis' => $currentUser->jenis_wilayah
            ];
        } else {
            // Untuk admin atau role lain, tampilkan semua kabupaten
            $kabupatens = $this->fetchWilayahData(
                'regencies/' . self::PROVINCE_ID . '.json',
                'kabupatens_jateng'
            );
            $fixedWilayah = null;
        }

        $availableKetuas = User::where('role', 'ketua-kader')
            ->whereNull('posyandu_id')
            ->orderBy('name')
            ->get();

        $allKecamatan = Posyandu::select('kecamatan')
            ->distinct()
            ->orderBy('kecamatan')
            ->pluck('kecamatan')
            ->map(fn($kec) => trim(str_ireplace('KECAMATAN', '', $kec)))
            ->filter()
            ->values();

        $allDesa = Posyandu::select('desa')
            ->distinct()
            ->orderBy('desa')
            ->pluck('desa')
            ->map(function ($desa) {
                $cleaned = str_ireplace(['DESA', 'KELURAHAN'], '', $desa);
                return trim($cleaned);
            })
            ->filter()
            ->values();

        if ($allKecamatan->isEmpty()) {
            $allKecamatan = collect([
                'ADIMULYO',
                'ALIAN',
                'AMBAL',
                'AYAH',
                'BONOROWO',
                'BULUSPESANTREN',
                'BUAYAN',
                'GOMBONG',
                'KARANGANYAR',
                'KARANGGAYAM',
                'KARANGSAMBUNG',
                'KEBUMEN',
                'KLIRONG',
                'KUWARASAN',
                'KUTOWINANGUN',
                'MIRIT',
                'PADURESO',
                'PEJAGOAN',
                'PETANAHAN',
                'PONCOWARNO',
                'PREMBUN',
                'PURING',
                'ROWOKELE',
                'SADANG',
                'SEMPOR',
                'SRUWENG'
            ]);
        }

        if ($allDesa->isEmpty()) {
            $allDesa = collect([]);
        }

        return view('admin.posyandu.create', compact(
            'kabupatens',
            'availableKetuas',
            'allKecamatan',
            'allDesa'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_posyandu' => 'required|string|max:255',
            'kabupaten' => 'required|string',
            'kecamatan' => 'required|string',
            'desa' => 'required|string',
        ]);

        $kabupatenName = explode('_', $request->kabupaten)[1] ?? $request->kabupaten;
        $kecamatanName = explode('_', $request->kecamatan)[1] ?? $request->kecamatan;
        $desaName = explode('_', $request->desa)[1] ?? $request->desa;

        // ✅ Buat Posyandu
        $posyandu = Posyandu::create([
            'nama_posyandu' => $request->nama_posyandu,
            'kabupaten' => $kabupatenName,
            'kecamatan' => $kecamatanName,
            'desa' => $desaName,
        ]);

        // ✅ Auto-generate 6 Kader (1 untuk setiap bidang)
        $this->createKadersForPosyandu($posyandu);

        return redirect()
            ->route('admin.posyandu.index')
            ->with('success', 'Posyandu dan 6 akun kader berhasil dibuat.');
    }

    /**
     * ✅ Generate 6 akun kader untuk posyandu baru
     */
    private function createKadersForPosyandu(Posyandu $posyandu)
    {
        // Ambil semua bidang
        $bidangs = \App\Models\BidangPengajuan::orderBy('nama_bidang')->get();

        if ($bidangs->count() !== 6) {
            Log::warning("Expected 6 bidangs but found {$bidangs->count()}");
        }

        $createdKaders = [];

        foreach ($bidangs as $index => $bidang) {
            // Generate username unik
            // Format: kader-{bidang_slug}-{posyandu_id_short}
            $bidangSlug = Str::slug($bidang->nama_bidang);
            $posyanduShort = substr($posyandu->id, 0, 8);
            $username = "kader-{$bidangSlug}-{$posyanduShort}";

            // Generate password default
            $defaultPassword = 'kader123';

            // Buat akun kader
            $kader = User::create([
                'name' => "Kader " . $bidang->nama_bidang . " - " . $posyandu->nama_posyandu,
                'email' => null, // Email opsional
                'no_telepon' => null, // Bisa diisi nanti
                'password' => Hash::make($defaultPassword),
                'role' => 'kader',
                'bidang_id' => $bidang->id,
                'posyandu_id' => $posyandu->id,
                'kabupaten' => $posyandu->kabupaten,
                'kecamatan' => $posyandu->kecamatan,
                'verified_at' => now(), // Langsung terverifikasi
                'verified_by' => Auth::id(),
                'is_active' => true,
                'nik' => null, // Bisa diisi nanti
                'alamat' => "Posyandu {$posyandu->nama_posyandu}",
                'tempat_lahir' => null,
                'tanggal_lahir' => null,
                'jenis_kelamin' => null,
            ]);

            // Log untuk tracking
            UserHistory::create([
                'user_id' => $kader->id,
                'action_by' => Auth::id(),
                'action_type' => 'created',
                'description' => "Akun kader auto-generated untuk {$bidang->nama_bidang} di {$posyandu->nama_posyandu}",
                'new_data' => [
                    'username' => $username,
                    'default_password' => $defaultPassword,
                    'bidang' => $bidang->nama_bidang,
                    'posyandu' => $posyandu->nama_posyandu,
                ],
            ]);

            $createdKaders[] = [
                'kader' => $kader,
                'username' => $username,
                'password' => $defaultPassword,
            ];
        }

        // Simpan info kader ke session untuk ditampilkan
        session()->flash('created_kaders', $createdKaders);

        return $createdKaders;
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'kecamatan' => 'required|string',
            'desa' => 'required|string'
        ]);

        try {
            $file = $request->file('file');
            $kecamatan = $request->input('kecamatan');
            $desa = $request->input('desa');

            $desaFormatted = strtoupper($desa);
            if (stripos($desaFormatted, 'DESA') === false && stripos($desaFormatted, 'KELURAHAN') === false) {
                $desaFormatted = 'DESA ' . $desaFormatted;
            }

            $kecamatanFormatted = strtoupper($kecamatan);
            if (stripos($kecamatanFormatted, 'KECAMATAN') === false) {
                $kecamatanFormatted = 'KECAMATAN ' . $kecamatanFormatted;
            }

            $countBefore = Posyandu::count();
            Excel::import(new PosyanduImport(), $file);
            $countAfter = Posyandu::count();
            $imported = $countAfter - $countBefore;

            Posyandu::whereNull('kecamatan')
                ->orWhere('kecamatan', '')
                ->orWhereNull('desa')
                ->orWhere('desa', '')
                ->update([
                    'kecamatan' => $kecamatanFormatted,
                    'desa' => $desaFormatted,
                    'kabupaten' => 'KEBUMEN'
                ]);

            $message = $imported > 0
                ? "Berhasil import {$imported} posyandu ke {$desa}, {$kecamatan}"
                : "Import selesai. Data mungkin sudah ada atau tidak valid.";

            return response()->json([
                'success' => true,
                'message' => $message,
                'imported' => $imported
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];

            foreach ($failures as $failure) {
                $errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal pada beberapa baris.',
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            Log::error('Import Posyandu Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Posyandu $posyandu) {}

    public function edit(Posyandu $posyandu)
    {
        $kabupatens = $this->fetchWilayahData(
            'regencies/' . self::PROVINCE_ID . '.json',
            'kabupatens_jateng'
        );

        $currentKetua = $posyandu->users()->where('role', 'ketua-kader')->first();

        $unassignedKetuas = User::where('role', 'ketua-kader')
            ->whereNull('posyandu_id')
            ->orderBy('name')
            ->get();

        $availableKetuas = $unassignedKetuas;
        if ($currentKetua && !$unassignedKetuas->contains($currentKetua)) {
            $availableKetuas->prepend($currentKetua);
        }

        return view('admin.posyandu.edit', compact('posyandu', 'kabupatens', 'availableKetuas', 'currentKetua'));
    }

    public function update(Request $request, Posyandu $posyandu)
    {
        $request->validate([
            'nama_posyandu' => 'required|string|max:255',
            'kabupaten' => 'required|string',
            'kecamatan' => 'required|string',
            'desa' => 'required|string',
            'ketua_kader_id' => ['nullable', 'uuid', 'exists:users,id'],
        ]);

        $posyandu->update($request->only('nama_posyandu', 'kabupaten', 'kecamatan', 'desa'));

        $currentKetua = $posyandu->users()->where('role', 'ketua-kader')->first();
        $newKetuaId = $request->ketua_kader_id;

        if ($newKetuaId && $newKetuaId != $currentKetua?->id) {
            if ($currentKetua) {
                $currentKetua->update(['posyandu_id' => null]);
            }
            $newKetua = User::find($newKetuaId);
            $newKetua->update(['posyandu_id' => $posyandu->id]);
        } else if (is_null($newKetuaId) && $currentKetua) {
            $currentKetua->update(['posyandu_id' => null]);
        }

        return redirect()->route('admin.posyandu.index')->with('success', 'Data Posyandu berhasil diperbarui.');
    }

    public function destroy(Posyandu $posyandu)
    {
        if ($posyandu->users()->count() > 0) {
            return redirect()->back()->with('error', 'Posyandu tidak bisa dihapus karena masih terhubung dengan data user.');
        }

        $posyandu->delete();
        return redirect()->route('admin.posyandu.index')->with('success', 'Posyandu berhasil dihapus.');
    }

    public function getKecamatan(Request $request)
    {
        $kabupatenId = $request->query('kab_id');

        if (!$kabupatenId) {
            return response()->json(['data' => []], 400);
        }

        $kecamatans = $this->fetchWilayahData(
            "districts/{$kabupatenId}.json",
            "kecamatans_{$kabupatenId}"
        );

        return response()->json($kecamatans);
    }

    public function getDesa(Request $request)
    {
        $kecamatanId = $request->query('kec_id');

        if (!$kecamatanId) {
            return response()->json(['data' => []], 400);
        }

        $desas = $this->fetchWilayahData(
            "villages/{$kecamatanId}.json",
            "desas_{$kecamatanId}"
        );

        return response()->json($desas);
    }

    public function getPosyanduByDesa(Request $request)
    {
        $desa = $request->query('desa');

        $posyandus = Posyandu::where('desa', 'LIKE', "%{$desa}%")
            ->orderBy('nama_posyandu')
            ->get(['id', 'nama_posyandu']);

        return response()->json($posyandus);
    }

    public function getPosyanduByWilayah(Request $request)
    {
        $request->validate([
            'kabupaten' => 'required|string',
            'kecamatan' => 'required|string',
            'desa' => 'required|string',
        ]);

        $kabupatenName = $request->query('kabupaten');
        $kecamatanName = $request->query('kecamatan');
        $desaName = $request->query('desa');
        $search = $request->query('search', '');

        $posyandus = Posyandu::where('kabupaten', $kabupatenName)
            ->where('kecamatan', $kecamatanName)
            ->where('desa', $desaName)
            ->when($search, function ($query, $search) {
                return $query->where('nama_posyandu', 'like', "%{$search}%");
            })
            ->orderBy('nama_posyandu')
            ->get(['id', 'nama_posyandu']);

        return response()->json($posyandus);
    }

    public function clearWilayahCache()
    {
        Cache::forget('kabupatens_jateng');

        $keys = Cache::get('wilayah_cache_keys', []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }

        return redirect()->back()->with('success', 'Cache wilayah berhasil dibersihkan.');
    }

    /**
     * ✅ FIXED: Export dengan data wilayah yang sudah di-fetch dari API
     */
    public function exportByDesaKecamatan($desa, $kecamatan)
    {
        $desaName = urldecode($desa);
        $kecamatanName = urldecode($kecamatan);

        Log::info('Export Template Request', [
            'desa' => $desaName,
            'kecamatan' => $kecamatanName
        ]);

        try {
            $apiUrl = env('API_WILAYAH_URL', 'https://wilayah.id/api/');
            $dataRows = [];

            // 1. Fetch kabupaten untuk dapat ID Kebumen
            $kabupatenUrl = $apiUrl . 'regencies/33.json';
            Log::info('Fetching Kabupaten', ['url' => $kabupatenUrl]);

            $kabupatenResponse = Http::timeout(20)->get($kabupatenUrl);

            Log::info('Kabupaten Response', [
                'status' => $kabupatenResponse->status(),
                'successful' => $kabupatenResponse->successful(),
            ]);

            $kabupatens = $kabupatenResponse->json()['data'] ?? [];

            // ✅ FIX: Cari berdasarkan nama yang mengandung "KEBUMEN"
            $kebumen = collect($kabupatens)->first(function ($kab) {
                return stripos($kab['name'], 'KEBUMEN') !== false;
            });

            // ✅ FIX: Gunakan format code yang benar (33.05 bukan 3404)
            $kabupatenId = $kebumen['code'] ?? '33.05';

            Log::info('Kabupaten ID', [
                'id' => $kabupatenId,
                'kebumen_found' => !is_null($kebumen),
            ]);

            // SKENARIO 1: SEMUA KECAMATAN + SEMUA DESA
            if (
                ($kecamatanName === 'all' || $kecamatanName === 'SEMUA KECAMATAN') &&
                ($desaName === 'all' || $desaName === 'SEMUA DESA')
            ) {
                Log::info('Scenario: SEMUA KECAMATAN + SEMUA DESA');

                // 2. Fetch semua kecamatan di Kebumen
                $kecamatanUrl = $apiUrl . "districts/{$kabupatenId}.json";
                Log::info('Fetching Kecamatan', ['url' => $kecamatanUrl]);

                $kecamatanResponse = Http::timeout(15)->get($kecamatanUrl);

                Log::info('Kecamatan Response', [
                    'status' => $kecamatanResponse->status(),
                    'successful' => $kecamatanResponse->successful(),
                    'body_preview' => substr($kecamatanResponse->body(), 0, 200)
                ]);

                $kecamatans = $kecamatanResponse->json()['data'] ?? [];

                Log::info('Total Kecamatan', [
                    'count' => count($kecamatans),
                    'sample' => array_slice($kecamatans, 0, 2)
                ]);

                // 3. Loop setiap kecamatan, fetch semua desa
                foreach ($kecamatans as $index => $kec) {
                    $desaUrl = $apiUrl . "villages/{$kec['code']}.json";
                    Log::info("Fetching Desa [{$index}]", [
                        'kecamatan' => $kec['name'],
                        'url' => $desaUrl
                    ]);

                    $desaResponse = Http::timeout(15)->get($desaUrl);

                    if (!$desaResponse->successful()) {
                        Log::warning("Failed to fetch desa", [
                            'kecamatan' => $kec['name'],
                            'status' => $desaResponse->status()
                        ]);
                        continue;
                    }

                    $desas = $desaResponse->json()['data'] ?? [];

                    Log::info("Kecamatan: {$kec['name']}", [
                        'total_desa' => count($desas),
                        'sample_desa' => array_slice($desas, 0, 2)
                    ]);

                    // 4. Setiap desa = 1 baris dengan kolom desa dan kecamatan auto-fill
                    foreach ($desas as $ds) {
                        $dataRows[] = [
                            'desa' => $this->cleanDesaName($ds['name']),
                            'kecamatan' => $this->cleanKecamatanName($kec['name'])
                        ];
                    }
                }
            }
            // SKENARIO 2: KECAMATAN SPESIFIK + SEMUA DESA
            else if ($desaName === 'all' || $desaName === 'SEMUA DESA') {
                Log::info('Scenario: KECAMATAN SPESIFIK + SEMUA DESA', ['kecamatan' => $kecamatanName]);

                // 2. Fetch semua kecamatan
                $kecamatanResponse = Http::timeout(15)->get($apiUrl . "districts/{$kabupatenId}.json");
                $kecamatans = $kecamatanResponse->json()['data'] ?? [];

                // 3. Cari kecamatan yang dipilih
                $selectedKec = collect($kecamatans)->first(function ($kec) use ($kecamatanName) {
                    $cleanKecName = $this->cleanKecamatanName($kec['name']);
                    $cleanInput = $this->cleanKecamatanName($kecamatanName);
                    return stripos($cleanKecName, $cleanInput) !== false ||
                        stripos($cleanInput, $cleanKecName) !== false;
                });

                if ($selectedKec) {
                    // 4. Fetch semua desa di kecamatan ini
                    $desaResponse = Http::timeout(15)->get($apiUrl . "villages/{$selectedKec['code']}.json");
                    $desas = $desaResponse->json()['data'] ?? [];

                    Log::info("Kecamatan: {$selectedKec['name']}", ['total_desa' => count($desas)]);

                    // 5. Setiap desa = 1 baris
                    foreach ($desas as $ds) {
                        $dataRows[] = [
                            'desa' => $this->cleanDesaName($ds['name']),
                            'kecamatan' => $this->cleanKecamatanName($selectedKec['name'])
                        ];
                    }
                }
            }
            // SKENARIO 3: KECAMATAN SPESIFIK + DESA SPESIFIK
            else {
                Log::info('Scenario: KECAMATAN SPESIFIK + DESA SPESIFIK', [
                    'kecamatan' => $kecamatanName,
                    'desa' => $desaName
                ]);

                // Generate 20 baris dengan data yang sama
                for ($i = 0; $i < 20; $i++) {
                    $dataRows[] = [
                        'desa' => $this->cleanDesaName($desaName),
                        'kecamatan' => $this->cleanKecamatanName($kecamatanName)
                    ];
                }
            }

            Log::info('Total Rows Generated', ['count' => count($dataRows)]);

            // Fallback jika tidak ada data
            if (empty($dataRows)) {
                for ($i = 0; $i < 10; $i++) {
                    $dataRows[] = [
                        'desa' => $this->cleanDesaName($desaName),
                        'kecamatan' => $this->cleanKecamatanName($kecamatanName)
                    ];
                }
            }

            // Generate filename
            $filename = 'Template_Posyandu_';
            if ($kecamatanName === 'all' || $kecamatanName === 'SEMUA KECAMATAN') {
                $filename .= 'Semua_Kecamatan_';
            } else {
                $filename .= $this->cleanKecamatanName($kecamatanName) . '_';
            }

            if ($desaName === 'all' || $desaName === 'SEMUA DESA') {
                $filename .= 'Semua_Desa_';
            } else {
                $filename .= $this->cleanDesaName($desaName) . '_';
            }

            $filename .= date('Y-m-d_His') . '.xlsx';

            // Export ke Excel dengan data yang sudah auto-fill
            return Excel::download(
                new PosyanduTemplateExport($dataRows),
                $filename
            );
        } catch (\Exception $e) {
            Log::error('Export Template Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Fallback
            $fallbackRows = [];
            for ($i = 0; $i < 10; $i++) {
                $fallbackRows[] = [
                    'desa' => $this->cleanDesaName($desaName),
                    'kecamatan' => $this->cleanKecamatanName($kecamatanName)
                ];
            }

            return Excel::download(
                new PosyanduTemplateExport($fallbackRows),
                'Template_Posyandu_Fallback_' . date('Y-m-d_His') . '.xlsx'
            );
        }
    }

    private function cleanDesaName($name)
    {
        if ($name === 'all' || $name === 'SEMUA DESA') {
            return '';
        }

        $cleaned = str_ireplace(['DESA ', 'KELURAHAN '], '', $name);
        return strtoupper(trim($cleaned));
    }

    private function cleanKecamatanName($name)
    {
        if ($name === 'all' || $name === 'SEMUA KECAMATAN') {
            return '';
        }

        $cleaned = str_ireplace('KECAMATAN ', '', $name);
        return strtoupper(trim($cleaned));
    }

    public function exportAllPosyandu()
    {
        return $this->exportByDesaKecamatan('all', 'all');
    }

    public function exportByKecamatan($kecamatan)
    {
        return $this->exportByDesaKecamatan('all', $kecamatan);
    }

    public function exportByDesa($desa)
    {
        return $this->exportByDesaKecamatan($desa, 'all');
    }
}
