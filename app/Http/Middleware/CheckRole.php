<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        if ($user->role === 'admin') {
            return $next($request);
        }

        // Pengecekan 1: Role
        if (!in_array($user->role, $roles)) {
            abort(403, 'AKSES DITOLAK: ANDA TIDAK MEMILIKI ROLE YANG SESUAI.');
        }

        // Pengecekan 2: Status Verifikasi untuk role tertentu
        if (($user->role === 'kader' || $user->role === 'ketua-kader') && is_null($user->verified_at)) {
            // Jika belum diverifikasi, "pental" ke dashboard dengan pesan error
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum diverifikasi oleh atasan untuk mengakses halaman ini.');
        }

        // Jika semua lolos, izinkan akses
        return $next($request);
    }
}
