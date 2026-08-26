<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleAuthorization::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (env('APP_DEBUG', true) || $request->has('debug') || !app()->isProduction()) {
                return response(
                    "<h1>Deployment Debug Exception:</h1>" .
                    "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>" .
                    "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>" .
                    "<pre style='background:#f4f4f4;padding:15px;border-radius:6px;overflow-x:auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>",
                    500
                );
            }
        });
    })->create();
