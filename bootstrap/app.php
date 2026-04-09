<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'admin.access' => \App\Http\Middleware\AuthorizeAdminRoute::class,
        ]);
    })->withProviders([
        App\Providers\AuthServiceProvider::class
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage() ?: 'Not Found',
                ], 404);
            }

            return response()->view('errors.404', [
                'exception' => $exception,
            ], 404);
        });
    })->create();
