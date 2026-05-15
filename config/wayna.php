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

];
