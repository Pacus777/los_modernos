<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tipo de cambio referencial turista (T-A24)
    |--------------------------------------------------------------------------
    |
    | Cuántos dólares estadounidenses equivale 1 boliviano (Bs).
    | Ejemplo: 0.145 → Bs 50 ≈ USD 7.25
    | Sin API externa; ajustar en .env (WAYNA_USD_POR_BS).
    |
    */

    'usd_por_bs' => (float) env('WAYNA_USD_POR_BS', 0.145),

    /*
    |--------------------------------------------------------------------------
    | Plazo para completar pago pendiente (T-A26)
    |--------------------------------------------------------------------------
    |
    | Minutos mostrados al turista tras registrar la donación. Al vencer solo
    | se informa en pantalla; la donación sigue pendiente para revisión manual.
    |
    */

    'pago_pendiente_minutos' => (int) env('WAYNA_PAGO_PENDIENTE_MINUTOS', 15),

    /*
    |--------------------------------------------------------------------------
    | Candado anti pago duplicado (S3-01)
    |--------------------------------------------------------------------------
    |
    | payment_uuid del cliente + store Redis (recomendado en producción).
    | En tests usar array vía phpunit.xml (WAYNA_PAYMENT_LOCK_STORE=array).
    |
    */

    // redis en producción; database o array si no tenés extensión phpredis en PHP local
    'payment_lock_store' => env('WAYNA_PAYMENT_LOCK_STORE', 'redis'),

    'payment_lock_ttl_seconds' => (int) env('WAYNA_PAYMENT_LOCK_TTL_SECONDS', 900),

    /*
    |--------------------------------------------------------------------------
    | Webhook Libélula (S3-02)
    |--------------------------------------------------------------------------
    |
    | Si está vacío, no se valida firma (solo desarrollo local).
    | En producción definir WAYNA_WEBHOOK_LIBELULA_SECRET.
    |
    */

    'webhook_libelula_secret' => env('WAYNA_WEBHOOK_LIBELULA_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Rate limiting por endpoint (S3-03)
    |--------------------------------------------------------------------------
    |
    | max_attempts por decay_minutes, agrupado por IP.
    | login.failed_max_attempts: límite adicional tras contraseña incorrecta (email+IP).
    |
    */

    /*
    |--------------------------------------------------------------------------
    | 2FA admin — Google Authenticator (S3-04)
    |--------------------------------------------------------------------------
    */

    'two_factor' => [
        'issuer' => env('WAYNA_2FA_ISSUER', 'WAYNA Admin'),
        'window' => (int) env('WAYNA_2FA_WINDOW', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sesión admin por inactividad (S3-05)
    |--------------------------------------------------------------------------
    */

    'admin_session_lifetime_minutes' => (int) env('WAYNA_ADMIN_SESSION_MINUTES', 30),
    'admin_session_warn_minutes' => (int) env('WAYNA_ADMIN_SESSION_WARN_MINUTES', 5),

    /*
    |--------------------------------------------------------------------------
    | Respaldos automáticos (S3-08 — spatie/laravel-backup)
    |--------------------------------------------------------------------------
    |
    | Requiere `php artisan schedule:run` cada minuto (cron o Task Scheduler).
    | En Laragon/Windows: habilitar extensión zip en php.ini.
    |
    */

    'backup' => [
        'enabled' => env('WAYNA_BACKUP_ENABLED', true),
        'cleanup_at' => env('WAYNA_BACKUP_CLEANUP_AT', '01:00'),
        'run_at' => env('WAYNA_BACKUP_RUN_AT', '01:30'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Auditoría de acciones críticas (S3-09)
    |--------------------------------------------------------------------------
    */

    'audit_log' => [
        'enabled' => env('WAYNA_AUDIT_LOG_ENABLED', true),
    ],

    'rate_limit' => [
        'login' => [
            'max_attempts' => (int) env('WAYNA_RATE_LIMIT_LOGIN_MAX', 10),
            'decay_minutes' => (int) env('WAYNA_RATE_LIMIT_LOGIN_DECAY', 1),
            'failed_max_attempts' => (int) env('WAYNA_RATE_LIMIT_LOGIN_FAILED_MAX', 5),
        ],
        'donaciones' => [
            'max_attempts' => (int) env('WAYNA_RATE_LIMIT_DONACIONES_MAX', 20),
            'decay_minutes' => (int) env('WAYNA_RATE_LIMIT_DONACIONES_DECAY', 1),
        ],
        'webhooks' => [
            'max_attempts' => (int) env('WAYNA_RATE_LIMIT_WEBHOOKS_MAX', 120),
            'decay_minutes' => (int) env('WAYNA_RATE_LIMIT_WEBHOOKS_DECAY', 1),
        ],
    ],

];
