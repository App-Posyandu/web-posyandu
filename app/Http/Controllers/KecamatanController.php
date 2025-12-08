<?php

namespace App\Http\Controllers;

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

    // Helper untuk fetch API (sama seperti di PosyanduController)
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
        $query = Kecamatan::latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama_kecamatan', 'like', "%$search%")
                  ->orWhere('kabupaten', 'like', "%$search%");
        }

        $kecamatans = $query->paginate(10)->withQueryString();

        return view('admin.kecamatan.index', compact('kecamatans'));
    }

    public function create()
    {
        // Ambil data Kabupaten dari API untuk dropdown
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

        // Ambil nama bersih dari format "ID_NAMA"
        $kabupatenName = explode('_', $request->kabupaten)[1] ?? $request->kabupaten;
        $kecamatanName = explode('_', $request->kecamatan)[1] ?? $request->kecamatan;

        // Cek duplikasi
        $exists = Kecamatan::where('nama_kecamatan', $kecamatanName)
                           ->where('kabupaten', $kabupatenName)
                           ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Kecamatan tersebut sudah terdaftar.');
        }

        Kecamatan::create([
            'kabupaten' => $kabupatenName,
            'nama_kecamatan' => $kecamatanName,
        ]);

        return redirect()->route('admin.kecamatan.index')->with('success', 'Kecamatan berhasil ditambahkan.');
    }

    public function edit(Kecamatan $kecamatan)
    {
        // Logic edit mirip create, kirim data lama
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

        $kabupatenName = explode('_', $request->kabupaten)[1] ?? $request->kabupaten;
        $kecamatanName = explode('_', $request->kecamatan)[1] ?? $request->kecamatan;

        $kecamatan->update([
            'kabupaten' => $kabupatenName,
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
