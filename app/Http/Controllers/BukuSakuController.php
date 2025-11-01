<?php

namespace App\Http\Controllers;

use App\Models\BukuSaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BukuSakuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bukuSaku = BukuSaku::with('user')->latest()->first();
        return view('admin.bukuSaku.index', compact('bukuSaku'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.bukuSaku.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $oldBukuSaku = BukuSaku::first();

        $path = $request->file('file')->store('buku_saku', 'public');

        if ($oldBukuSaku) {
            Storage::disk('public')->delete($oldBukuSaku->file_path);
        }

        BukuSaku::truncate();

        BukuSaku::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
        ]);

        return redirect()->route('buku-saku.index')->with('success', 'Buku Saku berhasil diperbarui.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BukuSaku $bukuSaku)
    {
        return view('admin.bukuSaku.edit', compact('bukuSaku'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BukuSaku $bukuSaku)
    {
        return view('admin.bukuSaku.edit', compact('bukuSaku'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BukuSaku $bukuSaku)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // File opsional saat update
        ]);

        $data = $request->only('title', 'description');

        if ($request->hasFile('file')) {
            // Hapus file lama
            Storage::disk('public')->delete($bukuSaku->file_path);
            // Simpan file baru
            $data['file_path'] = $request->file('file')->store('buku_saku', 'public');
        }

        $bukuSaku->update($data);

        return redirect()->route('admin.bukuSaku.index')->with('success', 'Buku Saku berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BukuSaku $bukuSaku)
    {
        Storage::disk('public')->delete($bukuSaku->file_path);

        // Hapus data dari database
        $bukuSaku->delete();

        return redirect()->back()->with('success', 'Buku Saku berhasil dihapus.');
    }
}
