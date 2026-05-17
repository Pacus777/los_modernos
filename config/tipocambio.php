<?php

return [
    'enabled' => env('TIPO_CAMBIO_ENABLED', true),

    'provider' => env('TIPO_CAMBIO_PROVIDER', 'dolarapi'),

    'cache_minutes' => (int) env('TIPO_CAMBIO_CACHE_MINUTES', 60),

    'fallback_usd_to_bob' => (float) env('TIPO_CAMBIO_FALLBACK_USD_TO_BOB', 10.25),

    'api_url' => env('TIPO_CAMBIO_API_URL', 'https://bo.dolarapi.com/v1/dolares'),
];