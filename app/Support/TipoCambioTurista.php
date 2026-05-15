<?php

namespace App\Support;

/**
 * Conversión Bs → USD referencial para turistas (T-A24).
 */
final class TipoCambioTurista
{
    public static function usdPorBoliviano(): float
    {
        return max((float) config('wayna.usd_por_bs', 0), 0.0);
    }

    public static function estaActivo(): bool
    {
        return self::usdPorBoliviano() > 0;
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
     * @return array{usd_por_bs: float, activo: bool}
     */
    public static function paraFrontend(): array
    {
        $usdPorBs = self::usdPorBoliviano();

        return [
            'usd_por_bs' => $usdPorBs,
            'activo' => $usdPorBs > 0,
        ];
    }
}
