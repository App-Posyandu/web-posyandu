<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use App\Http\Requests\StorePosyanduRequest;
use App\Http\Requests\UpdatePosyanduRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PosyanduController extends Controller
{
    const PROVINCE_ID = 33;
    const API_TIMEOUT = 10; // 10 detik timeout
    const CACHE_TTL = 3600; // Cache 1 jam

    /**
     * Helper: Fetch data dari API dengan caching dan timeout
     */
    private function fetchWilayahData($endpoint, $cacheKey)
    {
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($endpoint) {
            try {
                $response = Http::timeout(self::API_TIMEOUT)
                    ->retry(2, 100) // Retry 2x dengan delay 100ms
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
        // Fetch kabupaten dengan caching
        $kabupatens = $this->fetchWilayahData(
            'regencies/' . self::PROVINCE_ID . '.json',
            'kabupatens_jateng'
        );

        $availableKetuas = User::where('role', 'ketua-kader')
            ->whereNull('posyandu_id')
            ->orderBy('name')
            ->get();

        return view('admin.posyandu.create', compact('kabupatens', 'availableKetuas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_posyandu' => 'required|string|max:255',
            'kabupaten' => 'required|string',
            'kecamatan' => 'required|string',
            'desa' => 'required|string',
            'ketua_kader_id' => ['nullable', 'uuid', 'exists:users,id'],
        ]);

        $kabupatenName = explode('_', $request->kabupaten)[1] ?? $request->kabupaten;
        $kecamatanName = explode('_', $request->kecamatan)[1] ?? $request->kecamatan;
        $desaName = explode('_', $request->desa)[1] ?? $request->desa;

        $posyandu = Posyandu::create([
            'nama_posyandu' => $request->nama_posyandu,
            'kabupaten' => $kabupatenName,
            'kecamatan' => $kecamatanName,
            'desa' => $desaName,
        ]);

        if ($request->filled('ketua_kader_id')) {
            $ketuaKader = User::find($request->ketua_kader_id);
            if ($ketuaKader) {
                $ketuaKader->update(['posyandu_id' => $posyandu->id]);
            }
        }

        return redirect()->route('admin.posyandu.index')->with('success', 'Posyandu baru berhasil ditambahkan.');
    }

    public function show(Posyandu $posyandu)
    {
        //
    }

    public function edit(Posyandu $posyandu)
    {
        // Fetch kabupaten dengan caching
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

    /**
     * Get Kecamatan by Kabupaten ID (dengan caching)
     */
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

    /**
     * Get Desa by Kecamatan ID (dengan caching)
     */
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

    /**
     * Clear cache wilayah (untuk admin)
     */
    public function clearWilayahCache()
    {
        Cache::forget('kabupatens_jateng');

        // Clear semua cache kecamatan & desa
        $keys = Cache::get('wilayah_cache_keys', []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }

        return redirect()->back()->with('success', 'Cache wilayah berhasil dibersihkan.');
    }
}
