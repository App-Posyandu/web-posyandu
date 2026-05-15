<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use App\Support\AccessAudit;

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
            AccessAudit::record($request, null, 'admin_route', $ability ?? 'unknown', false, 401, [
                'route_name' => $routeName,
                'uri' => $uri,
            ]);
            abort(401, 'Unauthenticated.');
        }

        if (!$ability) {
            AccessAudit::record($request, $user, 'admin_route', 'unknown', false, 403, [
                'route_name' => $routeName,
                'uri' => $uri,
            ]);
            abort(403, 'Akses admin tidak diizinkan untuk route ini.');
        }

        if (Gate::denies($ability)) {
            AccessAudit::record($request, $user, 'admin_route', $ability, false, 403, [
                'route_name' => $routeName,
                'uri' => $uri,
            ]);
            abort(403, 'Akses ditolak.');
        }

        try {
            $response = $next($request);
            AccessAudit::record($request, $user, 'admin_route', $ability, true, $response->getStatusCode(), [
                'route_name' => $routeName,
                'uri' => $uri,
            ]);

            return $response;
        } catch (HttpExceptionInterface $exception) {
            AccessAudit::record($request, $user, 'admin_route', $ability, false, $exception->getStatusCode(), [
                'route_name' => $routeName,
                'uri' => $uri,
            ]);
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

}