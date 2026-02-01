<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KecamatanController extends Controller
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
                return ['data' => []];
            }
        });
    }

    public function index(Request $request)
    {
        $query = Kecamatan::with('kabupaten')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama_kecamatan', 'like', "%$search%")
                  ->orWhereHas('kabupaten', function($q) use ($search) {
                      $q->where('nama_kabupaten', 'like', "%$search%");
                  });
        }

        $kecamatans = $query->paginate(10)->withQueryString();

        return view('admin.kecamatan.index', compact('kecamatans'));
    }

    public function create()
    {
        $kabupatens = $this->fetchWilayahData(
            'regencies/' . self::PROVINCE_ID . '.json',
            'kabupatens_jateng'
        );

        return view('admin.kecamatan.create', compact('kabupatens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kabupaten' => 'required|string',
            'kecamatan' => 'required|string',
        ]);

        $kabupatenFull = explode('_', $request->kabupaten)[1] ?? $request->kabupaten;
        $kecamatanName = explode('_', $request->kecamatan)[1] ?? $request->kecamatan;

        $jenis = 'kabupaten';
        $namaKabupaten = $kabupatenFull;
        
        if (stripos($kabupatenFull, 'Kabupaten ') === 0) {
            $jenis = 'kabupaten';
            $namaKabupaten = trim(substr($kabupatenFull, 10));
        } elseif (stripos($kabupatenFull, 'Kota ') === 0) {
            $jenis = 'kota';
            $namaKabupaten = trim(substr($kabupatenFull, 5));
        }

        $kabupaten = Kabupaten::firstOrCreate(
            ['nama_kabupaten' => $namaKabupaten, 'jenis' => $jenis],
            ['nama_kabupaten' => $namaKabupaten, 'jenis' => $jenis]
        );

        $exists = Kecamatan::where('nama_kecamatan', $kecamatanName)
                           ->where('kabupaten_id', $kabupaten->id)
                           ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Kecamatan tersebut sudah terdaftar.');
        }

        Kecamatan::create([
            'kabupaten_id' => $kabupaten->id,
            'nama_kecamatan' => $kecamatanName,
        ]);

        return redirect()->route('admin.kecamatan.index')->with('success', 'Kecamatan berhasil ditambahkan.');
    }

    public function edit(Kecamatan $kecamatan)
    {
        $kabupatens = $this->fetchWilayahData(
            'regencies/' . self::PROVINCE_ID . '.json',
            'kabupatens_jateng'
        );

        return view('admin.kecamatan.edit', compact('kecamatan', 'kabupatens'));
    }

    public function update(Request $request, Kecamatan $kecamatan)
    {
        $request->validate([
            'kabupaten' => 'required|string',
            'kecamatan' => 'required|string',
        ]);

        $kabupatenFull = explode('_', $request->kabupaten)[1] ?? $request->kabupaten;
        $kecamatanName = explode('_', $request->kecamatan)[1] ?? $request->kecamatan;

        $jenis = 'kabupaten';
        $namaKabupaten = $kabupatenFull;
        
        if (stripos($kabupatenFull, 'Kabupaten ') === 0) {
            $jenis = 'kabupaten';
            $namaKabupaten = trim(substr($kabupatenFull, 10));
        } elseif (stripos($kabupatenFull, 'Kota ') === 0) {
            $jenis = 'kota';
            $namaKabupaten = trim(substr($kabupatenFull, 5));
        }

        $kabupaten = Kabupaten::firstOrCreate(
            ['nama_kabupaten' => $namaKabupaten, 'jenis' => $jenis],
            ['nama_kabupaten' => $namaKabupaten, 'jenis' => $jenis]
        );

        $kecamatan->update([
            'kabupaten_id' => $kabupaten->id,
            'nama_kecamatan' => $kecamatanName,
        ]);

        return redirect()->route('admin.kecamatan.index')->with('success', 'Data kecamatan berhasil diperbarui.');
    }

    public function destroy(Kecamatan $kecamatan)
    {
        $kecamatan->delete();
        return redirect()->route('admin.kecamatan.index')->with('success', 'Kecamatan berhasil dihapus.');
    }
}
