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
        $user = Auth::user();
        if (!$user) {
            return redirect('login');
        }

        if (in_array($user->role, ['admin', 'kabid'])) {
            return $next($request);
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'AKSES DITOLAK: ROLE TIDAK SESUAI.');
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'AKSES DITOLAK: ANDA TIDAK MEMILIKI ROLE YANG SESUAI.');
        }

        if ($user->role === 'kader' && is_null($user->verified_at)) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum diverifikasi oleh Ketua Kader.');
        }

        return $next($request);
    }
}
