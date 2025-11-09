<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use App\Http\Requests\StorePosyanduRequest;
use App\Http\Requests\UpdatePosyanduRequest;
use App\Models\User;
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kabupatens = Http::get(env('API_WILAYAH_URL') . 'regencies/' . self::PROVINCE_ID . '.json')->json();
        $availableKetuas = User::where('role', 'ketua-kader')
            ->whereNull('posyandu_id')
            ->orderBy('name')
            ->get();
        return view('admin.posyandu.create', compact('kabupatens', 'availableKetuas'));
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
            'ketua_kader_id' => ['nullable', 'uuid', 'exists:users,id'],
        ]);

        $posyandu = Posyandu::create([
            'nama_posyandu' => $request->nama_posyandu,
            'kabupaten' => $request->kabupaten,
            'kecamatan' => $request->kecamatan,
            'desa' => $request->desa,
        ]);

        if ($request->filled('ketua_kader_id')) {
            $ketuaKader = User::find($request->ketua_kader_id);
            if ($ketuaKader) {
                $ketuaKader->update(['posyandu_id' => $posyandu->id]);
            }
        }

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
