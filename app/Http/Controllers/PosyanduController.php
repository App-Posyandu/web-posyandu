<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use App\Http\Requests\StorePosyanduRequest;
use App\Http\Requests\UpdatePosyanduRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PosyanduController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    const PROVINCE_ID = 33;
    public function index(Request $request)
    {
        $query = Posyandu::latest();
        if ($request->filled('search')) {
            $query->where('nama_posyandu', 'like', '%' . $request->search . '%')
                ->orWhere('desa', 'like', '%' . $request->search . '%')
                ->orWhere('kecamatan', 'like', '%' . $request->search . '%')
                ->orWhere('kabupaten', 'like', '%' . $request->search . '%');
        }
        $posyandus = $query->paginate(10)->withQueryString();
        return view('admin.posyandu.index', compact('posyandus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kabupatens = Http::get(env('API_WILAYAH_URL') . 'regencies/' . self::PROVINCE_ID . '.json')->json();
        return view('admin.posyandu.create', compact('kabupatens'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_posyandu' => 'required|string|max:255',
            'kabupaten' => 'required|string',
            'kecamatan' => 'required|string',
            'desa' => 'required|string',
        ]);

        Posyandu::create($request->all());

        return redirect()->route('admin.posyandu.index')->with('success', 'Posyandu baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Posyandu $posyandu)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Posyandu $posyandu)
    {
        // Ambil daftar kabupaten di Jawa Tengah
        $kabupatens = Http::get(env('API_WILAYAH_URL') . 'regencies/' . self::PROVINCE_ID . '.json')->json();

        return view('admin.posyandu.edit', [
            'posyandu' => $posyandu,
            'kabupatens' => $kabupatens,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Posyandu $posyandu)
    {
        $request->validate([
            'nama_posyandu' => 'required|string|max:255',
            'kabupaten' => 'required|string',
            'kecamatan' => 'required|string',
            'desa' => 'required|string',
        ]);

        $posyandu->update($request->all());

        return redirect()->route('admin.posyandu.index')->with('success', 'Data Posyandu berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
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
        $kecamatans = Http::get(env('API_WILAYAH_URL') . "districts/{$kabupatenId}.json")->json();
        return response()->json($kecamatans);
    }

    public function getDesa(Request $request)
    {
        $kecamatanId = $request->query('kec_id');
        $desas = Http::get(env('API_WILAYAH_URL') . "villages/{$kecamatanId}.json")->json();
        return response()->json($desas);
    }
}
