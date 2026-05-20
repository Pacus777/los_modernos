<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\PreventDuplicatePayment;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/turista.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'check.role' => CheckRole::class,
            'nocache' => \App\Http\Middleware\PreventSensitivePageCache::class,
            'guest.redirect' => \App\Http\Middleware\RedirectAuthenticatedFromGuest::class,
            'prevent.duplicate.payment' => PreventDuplicatePayment::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
