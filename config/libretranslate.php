<?php

return [
    'enabled' => env('LIBRETRANSLATE_ENABLED', false),

    'api_url' => env('LIBRETRANSLATE_URL', 'http://127.0.0.1:5000/translate'),

    'source_language' => env('LIBRETRANSLATE_SOURCE_LANGUAGE', 'es'),

    'api_key' => env('LIBRETRANSLATE_API_KEY'),
];