<?php

declare(strict_types=1);

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
        $middleware->trustProxies(at: '*');

        $middleware->encryptCookies(except: [
            'fms_workstation_token',
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
            'api/v1/ingest/*',
        ]);

        $middleware->redirectTo(
            guests: fn (Request $request) => route('login'),
            users:  fn (Request $request) => match (auth()->user()?->role) {
                'Cashier' => route('collection.cashier-desk'),
                default   => route('accounting.dashboard'),
            },
        );

        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);

        $middleware->alias([
            'role'                => \App\Http\Middleware\RoleAuthorization::class,
            'must-change-password'=> \App\Http\Middleware\MustChangePassword::class,
            '2fa'                 => \App\Http\Middleware\EnsureTwoFactorAuthenticated::class,
            'idle.timeout'        => \App\Http\Middleware\IdleSessionTimeout::class,
            'single.session'      => \App\Http\Middleware\EnforceSingleActiveSession::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\MustChangePassword::class,
            \App\Http\Middleware\EnsureTwoFactorAuthenticated::class,
            \App\Http\Middleware\IdleSessionTimeout::class,
            \App\Http\Middleware\EnforceSingleActiveSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

