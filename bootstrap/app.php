<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'jwt.admin.verify' => \App\Http\Middleware\JwtAdminMiddleware::class,
            'jwt.auth' => \PHPOpenSourceSaver\JWTAuth\Middleware\GetUserFromToken::class,
            'jwt.refresh' => \PHPOpenSourceSaver\JWTAuth\Middleware\RefreshToken::class,
            'tenant' => \App\Http\Middleware\IdentifyTenant::class,
            'quota' => \App\Http\Middleware\EnforceTenantQuota::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\IdentifyTenant::class,
            \App\Http\Middleware\AdminAuthGates::class,
        ]);

        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
