<?php

return [
    'enabled' => env('GOOGLE_TRANSLATE_ENABLED', false),

    'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),

    'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),

    'location' => env('GOOGLE_TRANSLATE_LOCATION', 'global'),

    'source_language' => env('GOOGLE_TRANSLATE_SOURCE_LANGUAGE', 'es'),
];