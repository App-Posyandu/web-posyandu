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

        if (!in_array($user->role, $roles)) {
            abort(403, 'AKSES DITOLAK: ROLE TIDAK SESUAI.');
        }

        // Pengecekan 1: Role
        if (!in_array($user->role, $roles)) {
            abort(403, 'AKSES DITOLAK: ANDA TIDAK MEMILIKI ROLE YANG SESUAI.');
        }

        // Pengecekan 2: Status Verifikasi untuk role tertentu
        if ($user->role === 'kader' && is_null($user->verified_at)) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum diverifikasi oleh Ketua Kader.');
        }

        // Jika semua lolos, izinkan akses
        return $next($request);
    }
}
