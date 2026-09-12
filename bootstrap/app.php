<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/admin/login');
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'business.access' => \App\Http\Middleware\BusinessAccessMiddleware::class,
            'module' => \App\Http\Middleware\EnsureModuleAccess::class,
            'subscription.active' => \App\Http\Middleware\EnsureActiveSubscription::class,
        ]);

        // Pesapal calls these endpoints directly with no Laravel session/CSRF token.
        $middleware->validateCsrfTokens(except: [
            'subscriptions/ipn',
            'subscriptions/callback',
        ]);

        // Trust all proxies (needed for cPanel/shared hosting)
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
