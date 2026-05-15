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

];
