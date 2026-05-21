<?php

namespace App\Support;

use App\Services\TipoCambioService;

/**
 * Conversión Bs → USD con el valor referencial del dólar del BCB (bcb.gob.bo).
 */
final class TipoCambioTurista
{
    /**
     * @return array<string, mixed>
     */
    public static function datosReferenciales(): array
    {
        try {
            return app(TipoCambioService::class)->obtenerUsdBobReferencial();
        } catch (\Throwable) {
            return [
                'activo' => false,
                'usd_por_bs' => 0.0,
                'usd_to_bob' => null,
            ];
        }
    }

    public static function usdPorBoliviano(): float
    {
        $datos = self::datosReferenciales();

        return max((float) ($datos['usd_por_bs'] ?? 0), 0.0);
    }

    public static function estaActivo(): bool
    {
        $datos = self::datosReferenciales();

        return (bool) ($datos['activo'] ?? false) && self::usdPorBoliviano() > 0;
    }

    public static function bolivianosAUsd(float|int|string|null $montoBs): ?float
    {
        if (! self::estaActivo()) {
            return null;
        }

        $monto = (float) $montoBs;

        if ($monto <= 0 || ! is_finite($monto)) {
            return null;
        }

        return round($monto * self::usdPorBoliviano(), 2);
    }

    /**
     * @return array<string, mixed>
     */
    public static function paraFrontend(): array
    {
        $datos = self::datosReferenciales();

        return [
            'usd_por_bs' => (float) ($datos['usd_por_bs'] ?? 0),
            'usd_to_bob' => isset($datos['usd_to_bob']) ? (float) $datos['usd_to_bob'] : null,
            'compra' => isset($datos['compra']) ? (float) $datos['compra'] : null,
            'venta' => isset($datos['venta']) ? (float) $datos['venta'] : null,
            'activo' => (bool) ($datos['activo'] ?? false) && ((float) ($datos['usd_por_bs'] ?? 0)) > 0,
            'label' => $datos['label'] ?? 'Dólar referencial BCB',
            'institucion' => $datos['source'] ?? (string) config('tipocambio.institucion_label'),
            'tipo_bcb' => $datos['tipo_bcb'] ?? 'referencial',
            'updated_at' => $datos['updated_at'] ?? null,
        ];
    }
}
