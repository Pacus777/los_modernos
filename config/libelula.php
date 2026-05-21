<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Libélula / Todotix — REGISTRAR DEUDA (S2-01 / S2-03)
    |--------------------------------------------------------------------------
    |
    | Manual: https://libelula.bo — POST /rest/deuda/registrar
    |
    */

    'app_key' => env('LIBELULA_APP_KEY'),

    'base_url' => rtrim((string) env('LIBELULA_BASE_URL', 'https://api.todotix.com'), '/'),

    'sandbox' => (bool) env('LIBELULA_SANDBOX', true),

    'sandbox_base_url' => rtrim((string) env('LIBELULA_SANDBOX_BASE_URL', 'http://www.todotix.com:10888'), '/'),

    'email_fallback' => env('LIBELULA_EMAIL_FALLBACK', 'donaciones@wayna.local'),

    /**
     * Si no hay app_key, simular respuesta (tests y desarrollo local).
     */
    'fake_when_unconfigured' => (bool) env('LIBELULA_FAKE_WHEN_UNCONFIGURED', true),

    'timeout_seconds' => (int) env('LIBELULA_TIMEOUT_SECONDS', 30),

    /*
    |--------------------------------------------------------------------------
    | Webhook Libélula
    |--------------------------------------------------------------------------
    |
    | Si Libélula entrega una firma HMAC, se valida con este secreto.
    | En desarrollo local puede quedar null para permitir pruebas con curl/Postman.
    |
    */

    'webhook_secret' => env('LIBELULA_WEBHOOK_SECRET'),

];
