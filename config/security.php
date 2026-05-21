<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cabeceras de seguridad (S3-07)
    |--------------------------------------------------------------------------
    */

    'csp_enabled' => env('WAYNA_CSP_ENABLED', true),

    /** Si true, envía Content-Security-Policy-Report-Only (no bloquea). */
    'csp_report_only' => env('WAYNA_CSP_REPORT_ONLY', false),

    /** Host del dev server Vite (Laragon local). */
    'vite_dev_origin' => env('WAYNA_VITE_DEV_ORIGIN', 'http://127.0.0.1:5173'),

];
