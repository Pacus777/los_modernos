<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Código de pago referencial WAYNA (T-A25).
 *
 * Formato: WAYNA-20260515-ABCDE
 */
final class ReferenciaPagoWayna
{
    public static function generar(?\DateTimeInterface $fecha = null): string
    {
        $dia = ($fecha ?? now())->format('Ymd');
        $sufijo = Str::upper(Str::random(5));

        return "WAYNA-{$dia}-{$sufijo}";
    }

    public static function esFormatoValido(?string $referencia): bool
    {
        if ($referencia === null || $referencia === '') {
            return false;
        }

        return (bool) preg_match('/^WAYNA-\d{8}-[A-Z0-9]{5}$/', $referencia);
    }
}
