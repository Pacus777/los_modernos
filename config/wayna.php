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
