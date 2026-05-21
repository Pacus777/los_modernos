<?php

return [
    'enabled' => env('TIPO_CAMBIO_ENABLED', true),

    /**
     * bcb = API BCB (bcb.cucu.bo) — valor referencial u oficial del Banco Central.
     * dolarapi = respaldo (solo trae «oficial» y «binance», no el referencial 9,92).
     */
    'provider' => env('TIPO_CAMBIO_PROVIDER', 'bcb'),

    /**
     * referencial = «Valor referencial del dólar» (ej. compra 9,92 / venta 10,13 Bs).
     * oficial = «Tipo de cambio» BCB (ej. compra 6,86 / venta 6,96 Bs).
     */
    'tipo' => env('TIPO_CAMBIO_TIPO', 'referencial'),

    /** Solo si provider=dolarapi */
    'casa_preferida' => env('TIPO_CAMBIO_CASA', 'oficial'),

    'cache_minutes' => (int) env('TIPO_CAMBIO_CACHE_MINUTES', 60),

    'bcb_api_url_referencial' => env('TIPO_CAMBIO_BCB_URL_REFERENCIAL', 'https://bcb.cucu.bo/api/v1/tc/usd'),

    'bcb_api_url_oficial' => env('TIPO_CAMBIO_BCB_URL_OFICIAL', 'https://bcb.cucu.bo/api/v1/tc/oficial'),

    'api_url' => env('TIPO_CAMBIO_API_URL', 'https://bo.dolarapi.com/v1/dolares'),

    /** Respaldo: venta referencial BCB aproximada */
    'fallback_usd_to_bob' => (float) env('TIPO_CAMBIO_FALLBACK_USD_TO_BOB', 10.13),

    'fallback_compra' => (float) env('TIPO_CAMBIO_FALLBACK_COMPRA', 9.92),

    'institucion_label' => env('TIPO_CAMBIO_INSTITUCION', 'Banco Central de Bolivia (BCB)'),
];
