<?php

namespace App\Providers;

use App\Models\Campana;
use App\Models\Donacion;
use App\Models\EmprendedorPost;
use App\Observers\DonacionObserver;
use App\Observers\EmprendedorPostObserver;
use App\Observers\MetaObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Listeners\NotifyTelegramOnBackupResult;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        Vite::prefetch(concurrency: 3);

        Donacion::observe(DonacionObserver::class);
        EmprendedorPost::observe(EmprendedorPostObserver::class);
        Campana::observe(MetaObserver::class);

        $this->registerBackupTelegramListeners();
    }

    /**
     * Alertas Telegram tras backup:run (S3-08).
     */
    protected function registerBackupTelegramListeners(): void
    {
        $listener = NotifyTelegramOnBackupResult::class;

        Event::listen(BackupWasSuccessful::class, [$listener, 'onSuccess']);
        Event::listen(BackupHasFailed::class, [$listener, 'onFailure']);
    }

    /**
     * Límites de peticiones por endpoint (S3-03).
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('wayna-login', function (Request $request) {
            return $this->limitFromConfig('login', $request);
        });

        RateLimiter::for('wayna-donaciones', function (Request $request) {
            return $this->limitFromConfig('donaciones', $request);
        });

        RateLimiter::for('wayna-webhooks', function (Request $request) {
            return $this->limitFromConfig('webhooks', $request);
        });
    }

    private function limitFromConfig(string $endpoint, Request $request): Limit
    {
        $config = config("wayna.rate_limit.{$endpoint}", []);

        return Limit::perMinutes(
            max(1, (int) ($config['decay_minutes'] ?? 1)),
            max(1, (int) ($config['max_attempts'] ?? 60)),
        )->by($request->ip());
    }
}
