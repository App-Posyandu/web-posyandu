<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePosyanduRequest;
use App\Http\Requests\UpdatePosyanduRequest;
use App\Models\Posyandu;
use App\Imports\PosyanduImport;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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
    use AuthorizesRequests;

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

    private function authorizeAccessToPosyandu($user, Posyandu $posyandu)
    {
        if (in_array($user->role, ['admin', 'ketua-timpembina-posyandu'])) {
            return true;
        }

        if ($user->role === 'operator-desa') {
            if (
                $posyandu->kabupaten !== $user->kabupaten ||
                $posyandu->kecamatan !== $user->kecamatan ||
                $posyandu->desa !== $user->desa
            ) {
                abort(403, 'Anda hanya dapat mengelola posyandu di desa Anda sendiri.');
            }
            return true;
        }

        if ($user->role === 'admin-kabupaten') {
            if ($posyandu->kabupaten !== $user->kabupaten) {
                abort(403, 'Anda hanya dapat mengelola posyandu di kabupaten Anda sendiri.');
            }
            return true;
        }

        if ($user->role === 'ketua-posyandu') {
            if ($posyandu->id !== $user->posyandu_id) {
                abort(403, 'Anda hanya dapat mengelola posyandu Anda sendiri.');
            }
            return true;
        }

        if ($user->role === 'admin-kecamatan') {
            if ($user->kecamatan_id) {
                if ($posyandu->kecamatan_id !== $user->kecamatan_id) {
                    abort(403, 'Anda hanya dapat mengelola posyandu di kecamatan Anda sendiri.');
                }
            } else {
                if ($posyandu->kecamatan !== $user->kecamatan) {
                    abort(403, 'Anda hanya dapat mengelola posyandu di kecamatan Anda sendiri.');
                }
            }
            return true;
        }

        if ($user->role === 'kabid') {
            if ($user->kabupaten_id) {
                if ($posyandu->kabupaten_id !== $user->kabupaten_id) {
                    abort(403, 'Anda hanya dapat mengelola posyandu di kabupaten Anda sendiri.');
                }
            } else {
                if ($posyandu->kabupaten !== $user->kabupaten) {
                    abort(403, 'Anda hanya dapat mengelola posyandu di kabupaten Anda sendiri.');
                }
            }
            return true;
        }

        abort(403, 'Anda tidak memiliki akses untuk mengelola posyandu ini.');
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\pN\s@\._\-,()]+$/u'],
        ]);

        $currentUser = Auth::user();
        $query = Posyandu::with('users')->visibleTo($currentUser)->latest();

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_posyandu', 'like', '%' . $searchTerm . '%')
                    ->orWhere('desa', 'like', '%' . $searchTerm . '%')
                    ->orWhere('kecamatan', 'like', '%' . $searchTerm . '%')
                    ->orWhere('kabupaten', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('users', function ($subQ) use ($searchTerm) {
                        $subQ->where('name', 'like', '%' . $searchTerm . '%')
                            ->where('role', 'ketua-posyandu');
                    });
            });
        }

        $posyandus = $query->paginate(10)->withQueryString();

        return view('admin.posyandu.index', compact('posyandus'));
    }

    public function create()
    {
        $currentUser = Auth::user();

        if (!in_array($currentUser->role, ['admin', 'operator-desa'])) {
            abort(403, 'Anda tidak memiliki akses untuk membuat posyandu.');
        }

        if ($currentUser->role === 'kabid' && $currentUser->kabupaten) {
            $kabupatens = null;
            $fixedWilayah = [
                'nama' => $currentUser->kabupaten,
                'jenis' => $currentUser->jenis_wilayah
            ];
        } else {
            $kabupatens = $this->fetchWilayahData(
                'regencies/' . self::PROVINCE_ID . '.json',
                'kabupatens_jateng'
            );
            $fixedWilayah = null;
        }

        $availableKetuas = User::where('role', 'ketua-posyandu')
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

    public function store(StorePosyanduRequest $request)
    {
        $currentUser = Auth::user();

        if (!in_array($currentUser->role, ['admin', 'operator-desa'])) {
            abort(403, 'Anda tidak memiliki akses untuk membuat posyandu.');
        }

        $validated = $request->validated();

        $kabupatenName = explode('_', $validated['kabupaten'])[1] ?? $validated['kabupaten'];
        $kecamatanName = explode('_', $validated['kecamatan'])[1] ?? $validated['kecamatan'];
        $desaName = explode('_', $validated['desa'])[1] ?? $validated['desa'];

        if (!empty($validated['rt_mapping'])) {
            $totalRt = 0;
            foreach ($validated['rt_mapping'] as $rw => $rtList) {
                $totalRt += count($rtList);
            }

            if ($totalRt > 53) {
                return redirect()->back()
                    ->withErrors(['rt_mapping' => 'Total RT tidak boleh lebih dari 53 (saat ini: ' . $totalRt . ')'])
                    ->withInput();
            }
        }

        try {
            $posyandu = Posyandu::create([
                'nama_posyandu' => $validated['nama_posyandu'],
                'kabupaten' => $kabupatenName,
                'kecamatan' => $kecamatanName,
                'desa' => $desaName,
                'rw_list' => $validated['rw_list'] ?? [],
                'rt_mapping' => $validated['rt_mapping'] ?? [],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal membuat posyandu', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated,
            ]);
            return redirect()->back()
                ->withErrors(['nama_posyandu' => 'Gagal menyimpan posyandu: ' . $e->getMessage()])
                ->withInput();
        }

        try {
            $this->createKadersForPosyandu($posyandu);
        } catch (\Exception $e) {
            Log::error('Gagal membuat kader otomatis', [
                'posyandu_id' => $posyandu->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Posyandu tetap berhasil dibuat, kader bisa dibuat manual nanti
        }

        $rwCount = count($validated['rw_list'] ?? []);
        $rtCount = !empty($validated['rt_mapping']) ? array_sum(array_map('count', $validated['rt_mapping'])) : 0;

        try {
            UserHistory::create([
                'user_id' => Auth::id(),
                'action_by' => Auth::id(),
                'action_type' => 'created',
                'description' => "Posyandu {$posyandu->nama_posyandu} berhasil dibuat dengan {$rwCount} RW dan {$rtCount} RT",
                'new_data' => [
                    'nama_posyandu' => $posyandu->nama_posyandu,
                    'total_rw' => $rwCount,
                    'total_rt' => $rtCount,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan history pembuatan posyandu', [
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('admin.posyandu.index')
            ->with('success', 'Posyandu, mapping RW/RT, dan 6 akun kader berhasil dibuat.');
    }

    private function createKadersForPosyandu(Posyandu $posyandu)
    {
        $bidangs = \App\Models\BidangPengajuan::orderBy('nama_bidang')->get();

        if ($bidangs->isEmpty()) {
            Log::warning("Tidak ada data bidang_pengajuans di database. Kader tidak dapat dibuat.");
            return [];
        }

        if ($bidangs->count() !== 6) {
            Log::warning("Expected 6 bidangs but found {$bidangs->count()}");
        }

        $createdKaders = [];
        $defaultPassword = 'password123';

        foreach ($bidangs as $index => $bidang) {
            try {
                $bidangSlug = Str::slug($bidang->nama_bidang);
                $posyanduShort = substr($posyandu->id, 0, 8);

                $email = "kader.{$bidangSlug}.{$posyanduShort}@posyandu.local";

                // Cek apakah email sudah ada (hindari unique constraint violation)
                if (User::where('email', $email)->exists()) {
                    Log::warning("Email kader sudah ada, skip: {$email}");
                    continue;
                }

                $kader = User::create([
                    'name' => "Kader " . $bidang->nama_bidang . " - " . $posyandu->nama_posyandu,
                    'email' => $email,
                    'password' => $defaultPassword,
                    'role' => 'kader',
                    'bidang_id' => $bidang->id,
                    'posyandu_id' => $posyandu->id,
                    'kabupaten' => $posyandu->kabupaten,
                    'kabupaten_id' => $posyandu->kabupaten_id,
                    'kecamatan' => $posyandu->kecamatan,
                    'kecamatan_id' => $posyandu->kecamatan_id,
                    'desa' => $posyandu->desa,
                    'verified_at' => now(),
                    'verified_by' => Auth::id(),
                    'is_active' => true,
                    'nik' => null,
                    'alamat' => "Posyandu {$posyandu->nama_posyandu}, {$posyandu->desa}",
                    'tempat_lahir' => null,
                    'tanggal_lahir' => null,
                    'jenis_kelamin' => null,
                ]);

                UserHistory::create([
                    'user_id' => $kader->id,
                    'action_by' => Auth::id(),
                    'action_type' => 'created',
                    'description' => "Akun kader auto-generated untuk {$bidang->nama_bidang} di {$posyandu->nama_posyandu}",
                    'new_data' => [
                        'email' => $email,
                        'default_password' => $defaultPassword,
                        'bidang' => $bidang->nama_bidang,
                        'posyandu' => $posyandu->nama_posyandu,
                    ],
                ]);

                $createdKaders[] = [
                    'email' => $email,
                    'password' => $defaultPassword,
                    'bidang' => $bidang->nama_bidang,
                ];
            } catch (\Exception $e) {
                Log::error("Gagal membuat kader untuk bidang {$bidang->nama_bidang}", [
                    'posyandu_id' => $posyandu->id,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        session()->flash('created_kaders', $createdKaders);
        session()->flash('posyandu_name', $posyandu->nama_posyandu);
        session()->flash('posyandu_id', $posyandu->id);

        return $createdKaders;
    }

    public function editRwRt(Posyandu $posyandu)
    {
        $this->authorize('update', $posyandu);
        return view('admin.posyandu.edit-rw-rt', compact('posyandu'));
    }

    public function updateRwRt(Request $request, Posyandu $posyandu)
    {
        $this->authorize('update', $posyandu);

        $request->validate([
            'rw_list' => 'required|array|max:15',
            'rw_list.*' => 'required|string|regex:/^RW\d{2}$/',
            'rt_mapping' => 'required|array',
            'rt_mapping.*' => 'array',
            'rt_mapping.*.*' => 'required|string|regex:/^RT\d{3}$/',
        ], [
            'rw_list.max' => 'Maksimal 15 RW per posyandu',
            'rw_list.*.regex' => 'Format RW harus: RW01, RW02, dst',
            'rt_mapping.*.*.regex' => 'Format RT harus: RT001, RT002, dst',
        ]);

        $totalRt = 0;
        foreach ($request->rt_mapping as $rw => $rtList) {
            $totalRt += count($rtList);
        }

        if ($totalRt > 53) {
            return redirect()->back()
                ->withErrors(['rt_mapping' => 'Total RT tidak boleh lebih dari 53 (saat ini: ' . $totalRt . ')'])
                ->withInput();
        }

        foreach (array_keys($request->rt_mapping) as $rw) {
            if (!in_array($rw, $request->rw_list)) {
                return redirect()->back()
                    ->withErrors(['rt_mapping' => "RW {$rw} tidak ada dalam daftar RW yang aktif"])
                    ->withInput();
            }
        }

        $posyandu->update([
            'rw_list' => $request->rw_list,
            'rt_mapping' => $request->rt_mapping,
        ]);

        UserHistory::create([
            'user_id' => Auth::id(),
            'action_by' => Auth::id(),
            'action_type' => 'updated',
            'description' => "Mapping RW/RT posyandu {$posyandu->nama_posyandu} diperbarui. Total RW: " . count($request->rw_list) . ", Total RT: {$totalRt}",
            'new_data' => json_encode([
                'rw_list' => $request->rw_list,
                'total_rt' => $totalRt,
            ]),
        ]);

        return redirect()->route('admin.posyandu.index')
            ->with('success', 'Mapping RW/RT berhasil diperbarui.');
    }

    public function manageRwRt(Posyandu $posyandu)
    {
        $this->authorize('view', $posyandu);

        return view('admin.posyandu.manage-rw-rt', compact('posyandu'));
    }

    public function saveRwRt(Request $request, Posyandu $posyandu)
    {
        $this->authorize('update', $posyandu);

        $request->validate([
            'rw_list' => 'required|array|min:1',
            'rw_list.*' => 'required|string',
            'rt_mapping' => 'required|array',
            'rt_mapping.*' => 'array',
            'rt_mapping.*.*' => 'required|string',
        ], [
            'rw_list.required' => 'Minimal pilih 1 RW',
            'rw_list.min' => 'Minimal pilih 1 RW',
            'rt_mapping.required' => 'Setiap RW harus memiliki minimal 1 RT',
        ]);

        foreach ($request->rw_list as $rw) {
            if (!preg_match('/^RW(0[1-9]|1[0-5])$/', $rw)) {
                return redirect()->back()
                    ->withErrors(['rw_list' => "Format RW tidak valid: {$rw}. Harus RW01-RW15"])
                    ->withInput();
            }
        }

        foreach ($request->rt_mapping as $rw => $rtList) {
            if (!is_array($rtList) || empty($rtList)) {
                return redirect()->back()
                    ->withErrors(['rt_mapping' => "RW {$rw} harus memiliki minimal 1 RT"])
                    ->withInput();
            }

            foreach ($rtList as $rt) {
                if (!preg_match('/^RT(0[0-4][0-9]|05[0-3])$/', $rt)) {
                    return redirect()->back()
                        ->withErrors(['rt_mapping' => "Format RT tidak valid: {$rt}. Harus RT001-RT053"])
                        ->withInput();
                }
            }
        }

        foreach ($request->rw_list as $rw) {
            if (!isset($request->rt_mapping[$rw]) || empty($request->rt_mapping[$rw])) {
                return redirect()->back()
                    ->withErrors(['rt_mapping' => "RW {$rw} harus memiliki minimal 1 RT"])
                    ->withInput();
            }
        }

        $totalRt = 0;
        foreach ($request->rt_mapping as $rtList) {
            if (is_array($rtList)) {
                $totalRt += count($rtList);
            }
        }

        $posyandu->update([
            'rw_list' => $request->rw_list,
            'rt_mapping' => $request->rt_mapping,
        ]);

        UserHistory::create([
            'user_id' => Auth::id(),
            'action_by' => Auth::id(),
            'action_type' => 'updated',
            'description' => "Mapping RW/RT posyandu {$posyandu->nama_posyandu} diperbarui. Total RW: " . count($request->rw_list) . ", Total RT: {$totalRt}",
            'new_data' => json_encode([
                'rw_list' => $request->rw_list,
                'total_rw' => count($request->rw_list),
                'total_rt' => $totalRt,
            ]),
        ]);

        return redirect()->route('admin.posyandu.index')
            ->with('success', "Mapping RW/RT berhasil disimpan. Posyandu melayani " . count($request->rw_list) . " RW dengan total {$totalRt} RT.");
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
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'operator-desa', 'admin-kabupaten'])) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit posyandu.');
        }

        if ($user->role === 'admin-kabupaten') {
            // Admin kabupaten can edit in their kabupaten
            if ($user->kabupaten !== $posyandu->kabupaten) {
                abort(403, 'Anda tidak memiliki akses untuk mengedit posyandu di kabupaten lain.');
            }
        }

        if ($user->role === 'operator-desa') {
            if (
                $posyandu->kabupaten !== $user->kabupaten ||
                $posyandu->kecamatan !== $user->kecamatan ||
                $posyandu->desa !== $user->desa
            ) {
                abort(403, 'Anda hanya dapat mengedit posyandu di desa Anda.');
            }
        }

        $kabupatens = $this->fetchWilayahData(
            'regencies/' . self::PROVINCE_ID . '.json',
            'kabupatens_jateng'
        );

        $currentKetua = $posyandu->users()->where('role', 'ketua-posyandu')->first();

        $unassignedKetuas = User::where('role', 'ketua-posyandu')
            ->whereNull('posyandu_id')
            ->orderBy('name')
            ->get();

        $availableKetuas = $unassignedKetuas;
        if ($currentKetua && !$unassignedKetuas->contains($currentKetua)) {
            $availableKetuas->prepend($currentKetua);
        }

        return view('admin.posyandu.edit', compact('posyandu', 'kabupatens', 'availableKetuas', 'currentKetua'));
    }

    public function update(UpdatePosyanduRequest $request, Posyandu $posyandu)
    {
        $user = Auth::user();

        // Only admin and operator-desa can update posyandu
        if (!in_array($user->role, ['admin', 'operator-desa'])) {
            abort(403, 'Anda tidak memiliki akses untuk mengupdate posyandu.');
        }

        // operator-desa can only update posyandu in their desa
        if ($user->role === 'operator-desa') {
            if (
                $posyandu->kabupaten !== $user->kabupaten ||
                $posyandu->kecamatan !== $user->kecamatan ||
                $posyandu->desa !== $user->desa
            ) {
                abort(403, 'Anda hanya dapat mengupdate posyandu di desa Anda.');
            }
        }

        $validated = $request->validated();

        $kabupatenName = explode('_', $validated['kabupaten'])[1] ?? $validated['kabupaten'];
        $kecamatanName = explode('_', $validated['kecamatan'])[1] ?? $validated['kecamatan'];
        $desaName = explode('_', $validated['desa'])[1] ?? $validated['desa'];

        if (!empty($validated['rt_mapping'])) {
            $totalRt = 0;
            foreach ($validated['rt_mapping'] as $rw => $rtList) {
                $totalRt += count($rtList);
            }

            if ($totalRt > 53) {
                return redirect()->back()
                    ->withErrors(['rt_mapping' => 'Total RT tidak boleh lebih dari 53 (saat ini: ' . $totalRt . ')'])
                    ->withInput();
            }
        }

        $posyandu->update([
            'nama_posyandu' => $validated['nama_posyandu'],
            'kabupaten' => $kabupatenName,
            'kecamatan' => $kecamatanName,
            'desa' => $desaName,
            'rw_list' => $validated['rw_list'] ?? [],
            'rt_mapping' => $validated['rt_mapping'] ?? [],
        ]);

        $rwCount = count($validated['rw_list'] ?? []);
        $rtCount = !empty($validated['rt_mapping']) ? array_sum(array_map('count', $validated['rt_mapping'])) : 0;

        UserHistory::create([
            'user_id' => Auth::id(),
            'action_by' => Auth::id(),
            'action_type' => 'updated',
            'description' => "Data posyandu {$posyandu->nama_posyandu} diperbarui. Total RW: {$rwCount}, Total RT: {$rtCount}",
            'new_data' => [
                'nama_posyandu' => $posyandu->nama_posyandu,
                'total_rw' => $rwCount,
                'total_rt' => $rtCount,
            ],
        ]);

        return redirect()->route('admin.posyandu.index')
            ->with('success', 'Data Posyandu dan mapping RW/RT berhasil diperbarui.');
    }

    public function destroy(Posyandu $posyandu)
    {
        $user = Auth::user();

        // Only admin and operator-desa can delete posyandu
        if (!in_array($user->role, ['admin', 'operator-desa'])) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus posyandu.');
        }

        // operator-desa can only delete posyandu in their desa
        if ($user->role === 'operator-desa') {
            if (
                $posyandu->kabupaten !== $user->kabupaten ||
                $posyandu->kecamatan !== $user->kecamatan ||
                $posyandu->desa !== $user->desa
            ) {
                abort(403, 'Anda hanya dapat menghapus posyandu di desa Anda.');
            }
        }

        if ($posyandu->users()->count() > 0) {
            return redirect()->back()->with('error', 'Posyandu tidak bisa dihapus karena masih terhubung dengan data user.');
        }

        $posyandu->delete();
        return redirect()->route('admin.posyandu.index')->with('success', 'Posyandu berhasil dihapus.');
    }

    public function getKecamatan(Request $request)
    {
        $validated = $request->validate([
            'kab_id' => ['required', 'string', 'regex:/^\d{2}\.\d{2}$/'],
        ]);

        $kabupatenId = $validated['kab_id'];

        $kecamatans = $this->fetchWilayahData(
            "districts/{$kabupatenId}.json",
            "kecamatans_{$kabupatenId}"
        );

        return response()->json($kecamatans);
    }

    public function getDesa(Request $request)
    {
        $validated = $request->validate([
            'kec_id' => ['required', 'string', 'regex:/^\d{2}\.\d{2}\.\d{2}$/'],
        ]);

        $kecamatanId = $validated['kec_id'];

        $desas = $this->fetchWilayahData(
            "villages/{$kecamatanId}.json",
            "desas_{$kecamatanId}"
        );

        return response()->json($desas);
    }

    public function getPosyanduByDesa(Request $request)
    {
        $validated = $request->validate([
            'desa' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9\s\.\-]+$/'],
        ]);

        $desa = $validated['desa'];

        $posyandus = Posyandu::where('desa', 'LIKE', "%{$desa}%")
            ->orderBy('nama_posyandu')
            ->get(['id', 'nama_posyandu']);

        return response()->json($posyandus);
    }

    public function getPosyanduByWilayah(Request $request)
    {
        $validated = $request->validate([
            'kabupaten' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9\s\.\-]+$/'],
            'kecamatan' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9\s\.\-]+$/'],
            'desa' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9\s\.\-]+$/'],
            'search' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9\s\.\-]*$/'],
        ]);

        $kabupatenName = $validated['kabupaten'];
        $kecamatanName = $validated['kecamatan'];
        $desaName = $validated['desa'];
        $search = $validated['search'] ?? '';

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

            $kabupatenUrl = $apiUrl . 'regencies/33.json';
            Log::info('Fetching Kabupaten', ['url' => $kabupatenUrl]);

            $kabupatenResponse = Http::timeout(20)->get($kabupatenUrl);

            Log::info('Kabupaten Response', [
                'status' => $kabupatenResponse->status(),
                'successful' => $kabupatenResponse->successful(),
            ]);

            $kabupatens = $kabupatenResponse->json()['data'] ?? [];

            $kebumen = collect($kabupatens)->first(function ($kab) {
                return stripos($kab['name'], 'KEBUMEN') !== false;
            });

            $kabupatenId = $kebumen['code'] ?? '33.05';

            Log::info('Kabupaten ID', [
                'id' => $kabupatenId,
                'kebumen_found' => !is_null($kebumen),
            ]);

            if (
                ($kecamatanName === 'all' || $kecamatanName === 'SEMUA KECAMATAN') &&
                ($desaName === 'all' || $desaName === 'SEMUA DESA')
            ) {
                Log::info('Scenario: SEMUA KECAMATAN + SEMUA DESA');

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

                    foreach ($desas as $ds) {
                        $dataRows[] = [
                            'desa' => $this->cleanDesaName($ds['name']),
                            'kecamatan' => $this->cleanKecamatanName($kec['name'])
                        ];
                    }
                }
            } else if ($desaName === 'all' || $desaName === 'SEMUA DESA') {
                Log::info('Scenario: KECAMATAN SPESIFIK + SEMUA DESA', ['kecamatan' => $kecamatanName]);

                $kecamatanResponse = Http::timeout(15)->get($apiUrl . "districts/{$kabupatenId}.json");
                $kecamatans = $kecamatanResponse->json()['data'] ?? [];

                $selectedKec = collect($kecamatans)->first(function ($kec) use ($kecamatanName) {
                    $cleanKecName = $this->cleanKecamatanName($kec['name']);
                    $cleanInput = $this->cleanKecamatanName($kecamatanName);
                    return stripos($cleanKecName, $cleanInput) !== false ||
                        stripos($cleanInput, $cleanKecName) !== false;
                });

                if ($selectedKec) {
                    $desaResponse = Http::timeout(15)->get($apiUrl . "villages/{$selectedKec['code']}.json");
                    $desas = $desaResponse->json()['data'] ?? [];

                    Log::info("Kecamatan: {$selectedKec['name']}", ['total_desa' => count($desas)]);

                    foreach ($desas as $ds) {
                        $dataRows[] = [
                            'desa' => $this->cleanDesaName($ds['name']),
                            'kecamatan' => $this->cleanKecamatanName($selectedKec['name'])
                        ];
                    }
                }
            } else {
                Log::info('Scenario: KECAMATAN SPESIFIK + DESA SPESIFIK', [
                    'kecamatan' => $kecamatanName,
                    'desa' => $desaName
                ]);

                for ($i = 0; $i < 20; $i++) {
                    $dataRows[] = [
                        'desa' => $this->cleanDesaName($desaName),
                        'kecamatan' => $this->cleanKecamatanName($kecamatanName)
                    ];
                }
            }

            Log::info('Total Rows Generated', ['count' => count($dataRows)]);

            if (empty($dataRows)) {
                for ($i = 0; $i < 10; $i++) {
                    $dataRows[] = [
                        'desa' => $this->cleanDesaName($desaName),
                        'kecamatan' => $this->cleanKecamatanName($kecamatanName)
                    ];
                }
            }

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

            return Excel::download(
                new PosyanduTemplateExport($dataRows),
                $filename
            );
        } catch (\Exception $e) {
            Log::error('Export Template Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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

    public function printKaderCredentials(Posyandu $posyandu)
    {
        $this->authorizeAccessToPosyandu(Auth::user(), $posyandu);

        $kaders = User::where('posyandu_id', $posyandu->id)
            ->where('role', 'kader')
            ->with('bidang')
            ->orderBy('created_at')
            ->get(['name', 'email', 'no_telepon', 'bidang_id'])
            ->map(function ($user) {
                return [
                    'nama_lengkap' => $user->name,
                    'email' => $user->email,
                    'no_hp' => $user->no_telepon,
                    'username' => $user->email,
                    'password' => 'password123',
                    'bidang' => $user->bidang->nama_bidang ?? '-'
                ];
            })
            ->toArray();

        if (empty($kaders)) {
            return redirect()->route('admin.posyandu.index')
                ->with('error', 'Posyandu ini belum memiliki kader.');
        }

        $posyanduName = $posyandu->nama_posyandu;
        $currentUser = Auth::user();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.posyandu.print_credentials', [
            'kaders' => $kaders,
            'posyanduName' => $posyanduName,
            'currentUser' => $currentUser,
            'printDate' => now()->format('d F Y H:i')
        ]);

        $pdf->setPaper('A4', 'portrait');

        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        return $pdf->stream('credentials_kader_' . Str::slug($posyanduName) . '_' . now()->format('YmdHis') . '.pdf');
    }
}