<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AuthorizeAdminRoute
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $route = $request->route();
        $routeName = $route?->getName();
        $uri = $route?->uri() ?? $request->path();
        $ability = $this->resolveAbility($routeName, $uri);

        if (!$user) {
            $this->logAttempt($request, null, $routeName, $uri, $ability, 401);
            abort(401, 'Unauthenticated.');
        }

        if (!$ability) {
            $this->logAttempt($request, $user, $routeName, $uri, null, 403);
            abort(403, 'Akses admin tidak diizinkan untuk route ini.');
        }

        if (Gate::denies($ability)) {
            $this->logAttempt($request, $user, $routeName, $uri, $ability, 403);
            abort(403, 'Akses ditolak.');
        }

        try {
            $response = $next($request);
            $this->logAttempt($request, $user, $routeName, $uri, $ability, $response->getStatusCode());

            return $response;
        } catch (HttpExceptionInterface $exception) {
            $this->logAttempt($request, $user, $routeName, $uri, $ability, $exception->getStatusCode());
            throw $exception;
        }
    }

    private function resolveAbility(?string $routeName, string $uri): ?string
    {
        if ($routeName === 'dashboard.partials.pilih-user' || Str::is('admin/ajuan/pilih-user', $uri)) {
            return 'admin.ajuan.pilih-user';
        }

        if ($routeName && Str::startsWith($routeName, 'admin.settings.')) {
            return 'admin.settings.manage';
        }

        if ($routeName && (Str::startsWith($routeName, 'admin.users.') || Str::startsWith($routeName, 'admin.import.'))) {
            return 'admin.users.manage';
        }

        if ($routeName && Str::startsWith($routeName, 'admin.posyandu.')) {
            return 'admin.posyandu.manage';
        }

        if ($routeName && Str::startsWith($routeName, 'admin.laporan.')) {
            return 'admin.laporan.view';
        }

        return null;
    }

    private function logAttempt(Request $request, ?object $user, ?string $routeName, string $uri, ?string $ability, int $status): void
    {
        Log::channel('daily')->info('admin.route.access', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => $user?->id,
            'role' => $user?->role,
            'route_name' => $routeName,
            'uri' => $uri,
            'method' => $request->method(),
            'ip' => $request->ip(),
            'ability' => $ability,
            'status' => $status,
        ]);
    }
}