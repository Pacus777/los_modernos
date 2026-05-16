<?php

return [
    'enabled' => env('DEEPL_ENABLED', false),

    'auth_key' => env('DEEPL_AUTH_KEY'),

    'api_url' => env('DEEPL_API_URL', 'https://api-free.deepl.com/v2/translate'),

    'source_language' => env('DEEPL_SOURCE_LANGUAGE', 'ES'),
];