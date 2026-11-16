<?php

namespace App\Http\Controllers;

use App\Models\SyncDirective;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyncDirectiveController extends Controller
{
    /**
     * Halaman setting sinkronisasi.
     * - admin-kabupaten: mengatur kabupatennya sendiri.
     * - admin (super): memilih kabupaten mana pun yang punya operator-desa.
     */
    public function edit(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role === 'admin';

        // Daftar kabupaten yang punya operator-desa (untuk dipilih super admin).
        $kabupatenOptions = $isSuperAdmin
            ? \App\Models\User::where('role', 'operator-desa')
                ->whereNotNull('kabupaten')->where('kabupaten', '!=', '')
                ->distinct()->orderBy('kabupaten')->pluck('kabupaten')
            : collect();

        if ($isSuperAdmin) {
            $kabupaten = trim((string) $request->query('kabupaten', ''));
            if ($kabupaten === '' && $kabupatenOptions->isNotEmpty()) {
                $kabupaten = (string) $kabupatenOptions->first();
            }
        } else {
            $kabupaten = trim((string) $user->kabupaten);
            if ($kabupaten === '') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akun Anda belum memiliki data kabupaten, tidak dapat mengatur sinkronisasi.');
            }
        }

        $directive = $kabupaten !== '' ? SyncDirective::where('kabupaten', $kabupaten)->first() : null;

        // Ringkasan status operator-desa di kabupaten ini.
        $operators = $kabupaten !== ''
            ? \App\Models\User::where('role', 'operator-desa')
                ->where('kabupaten', $kabupaten)
                ->orderBy('name')
                ->get(['id', 'name', 'desa', 'sync_done_at'])
            : collect();

        return view('admin.sync-settings.edit', compact(
            'directive', 'kabupaten', 'operators', 'isSuperAdmin', 'kabupatenOptions'
        ));
    }

    /**
     * Simpan setting: aktifkan/nonaktifkan + pesan broadcast.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role === 'admin';

        $rules = [
            'is_active' => ['nullable', 'boolean'],
            'message'   => ['nullable', 'string', 'max:1000'],
        ];
        if ($isSuperAdmin) {
            $rules['kabupaten'] = ['required', 'string', 'max:255'];
        }

        $validated = $request->validate($rules, [
            'message.max'        => 'Pesan maksimal 1000 karakter.',
            'kabupaten.required' => 'Silakan pilih kabupaten/kota terlebih dahulu.',
        ]);

        $kabupaten = $isSuperAdmin
            ? trim($validated['kabupaten'])
            : trim((string) $user->kabupaten);

        if ($kabupaten === '') {
            return redirect()->route('dashboard')
                ->with('error', 'Kabupaten tidak valid.');
        }

        $isActive = $request->boolean('is_active');

        $existing = SyncDirective::where('kabupaten', $kabupaten)->first();

        // activated_at diperbarui ke "sekarang" setiap kali diaktifkan — ini me-reset status
        // semua operator-desa menjadi "belum sinkron" untuk versi terbaru.
        $activatedAt = $isActive ? now() : ($existing->activated_at ?? null);

        SyncDirective::updateOrCreate(
            ['kabupaten' => $kabupaten],
            [
                'is_active'    => $isActive,
                'message'      => $validated['message'] ?? null,
                'activated_at' => $activatedAt,
                'created_by'   => Auth::id(),
            ]
        );

        $msg = $isActive
            ? 'Sinkronisasi diaktifkan. Operator-desa akan melihat pemberitahuan untuk melakukan sinkronisasi.'
            : 'Sinkronisasi dinonaktifkan.';

        return redirect()
            ->route('admin.sync-settings.edit', $isSuperAdmin ? ['kabupaten' => $kabupaten] : [])
            ->with('success', $msg);
    }
}
