<?php

namespace App\Support;

use App\Models\TipoPago;

class TipoPagoTurista
{
    public static function nombreParaLocale(TipoPago|array $tipo, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $codigo = is_array($tipo) ? ($tipo['codigo'] ?? '') : $tipo->codigo;
        $nombre = is_array($tipo) ? ($tipo['nombre'] ?? '') : $tipo->nombre;

        if ($locale === 'es' || $codigo === '') {
            return $nombre;
        }

        $desdeArchivo = __("payment_types.{$codigo}", [], $locale);

        if ($desdeArchivo !== "payment_types.{$codigo}") {
            return $desdeArchivo;
        }

        $glosario = config('traducciones.glosario.en', []);

        if (isset($glosario[$nombre])) {
            return $glosario[$nombre];
        }

        return $nombre;
    }
}
