<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Conversión WebP (T-A11)
    |--------------------------------------------------------------------------
    |
    | Al subir JPG/PNG/WebP en el admin, se guarda como WebP en disco public.
    | Si PHP no tiene GD con soporte WebP, se guarda el archivo original.
    |
    */

    'webp_quality' => (int) env('IMAGE_WEBP_QUALITY', 85),

    'max_width' => (int) env('IMAGE_MAX_WIDTH', 1600),

    'max_height' => (int) env('IMAGE_MAX_HEIGHT', 1600),

];
