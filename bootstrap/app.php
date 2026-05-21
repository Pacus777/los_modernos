<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureIsEmprendedor;
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\EnsureAdminTwoFactorPassed;
use App\Http\Middleware\TouchAdminSessionActivity;
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

            Route::middleware('web')
                ->group(base_path('routes/webhooks.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * CSRF (S3-07): rutas web protegidas por defecto.
         * Excepción única: webhooks de pasarela (sin sesión, firma HMAC).
         */
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        $middleware->alias([
            'check.role' => CheckRole::class,
            'emprendedor' => EnsureIsEmprendedor::class,
            'emprendedor.force_password' => ForcePasswordChange::class,
            'emprendedor.profile_complete' => \App\Http\Middleware\EnsureProfileComplete::class,
            'nocache' => \App\Http\Middleware\PreventSensitivePageCache::class,
            'guest.redirect' => \App\Http\Middleware\RedirectAuthenticatedFromGuest::class,
            'prevent.duplicate.payment' => PreventDuplicatePayment::class,
            'admin.two_factor' => EnsureAdminTwoFactorPassed::class,
            'admin.session' => TouchAdminSessionActivity::class,
        ]);

        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        if (! config('wayna.backup.enabled', true)) {
            return;
        }

        $horaLimpieza = config('wayna.backup.cleanup_at', '01:00');
        $horaRespaldo = config('wayna.backup.run_at', '01:30');

        $schedule->command('backup:clean')->dailyAt($horaLimpieza);
        $schedule->command('backup:run')->dailyAt($horaRespaldo);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
