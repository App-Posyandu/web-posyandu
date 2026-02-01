<?php

namespace App\Http\Controllers;

use App\Models\BukuSaku;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BukuSakuController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', BukuSaku::class);
        $bukuSaku = BukuSaku::with('user')->latest()->paginate(10);
        return view('admin.bukuSaku.index', compact('bukuSaku'));
    }

    public function create()
    {
        $this->authorize('create', BukuSaku::class);
        return view('admin.bukuSaku.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', BukuSaku::class);
        $user = Auth::user();
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $path = $request->file('file')->store('buku_saku', 'public');

        BukuSaku::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
        ]);

        return redirect()->route('buku_saku.index')->with('success', 'Buku Saku baru berhasil diunggah.');
    }

    public function show(BukuSaku $bukuSaku)
    {
        $this->authorize('viewBukuSaku', $bukuSaku);
        return view('admin.bukuSaku.edit', compact('bukuSaku'));
    }

    public function edit(BukuSaku $bukuSaku)
    {
        $this->authorize('update', $bukuSaku);
        return view('admin.bukuSaku.edit', compact('bukuSaku'));
    }

    public function update(Request $request, BukuSaku $bukuSaku)
    {
        $this->authorize('update', $bukuSaku);
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $data = $request->only('title', 'description');

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($bukuSaku->file_path);
            $data['file_path'] = $request->file('file')->store('buku_saku', 'public');
        }

        $bukuSaku->update($data);

        return redirect()->route('buku_saku.index')->with('success', 'Buku Saku berhasil diperbarui.');
    }

    public function destroy(BukuSaku $bukuSaku)
    {
        $this->authorize('delete', $bukuSaku);
        Storage::disk('public')->delete($bukuSaku->file_path);

        $bukuSaku->delete();

        return redirect()->back()->with('success', 'Buku Saku berhasil dihapus.');
    }

    public function stream(): StreamedResponse|RedirectResponse
    {
        $this->authorize('viewAny', BukuSaku::class);
        $bukuSaku = BukuSaku::latest()->first();

        if (!$bukuSaku || !Storage::disk('public')->exists($bukuSaku->file_path)) {
            return redirect()->route('buku_saku.index')->with('error', 'File Buku Saku tidak ditemukan.');
        }

        $path = $bukuSaku->file_path;

        $stream = Storage::disk('public')->readStream($path);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
        ]);
    }

    public function streamFile(BukuSaku $bukuSaku): StreamedResponse|RedirectResponse
    {
        $this->authorize('viewBukuSaku', $bukuSaku);

        if (!Storage::disk('public')->exists($bukuSaku->file_path)) {
            abort(404, 'File Buku Saku tidak ditemukan.');
        }

        $path = $bukuSaku->file_path;
        $stream = Storage::disk('public')->readStream($path);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
        ]);
    }
}
