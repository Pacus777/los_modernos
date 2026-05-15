<?php

namespace App\Support;

use App\Models\Donacion;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Plazo visual para completar un pago pendiente (T-A26).
 *
 * Solo informa al turista; no cambia estado_pago ni elimina donaciones.
 */
final class PagoPendienteTurista
{
    public static function minutosPlazo(): int
    {
        return max(1, (int) config('wayna.pago_pendiente_minutos', 15));
    }

    public static function venceEn(CarbonInterface $creadoEn): Carbon
    {
        return Carbon::parse($creadoEn)->addMinutes(self::minutosPlazo());
    }

    public static function plazoVencido(CarbonInterface $creadoEn, ?CarbonInterface $ahora = null): bool
    {
        $referencia = $ahora ?? now();

        return $referencia->greaterThanOrEqualTo(self::venceEn($creadoEn));
    }

    public static function segundosRestantes(CarbonInterface $creadoEn, ?CarbonInterface $ahora = null): int
    {
        if (self::plazoVencido($creadoEn, $ahora)) {
            return 0;
        }

        $referencia = $ahora ?? now();
        $vence = self::venceEn($creadoEn);

        return (int) max(0, $vence->getTimestamp() - $referencia->getTimestamp());
    }

    /**
     * @return array{
     *     minutos_plazo: int,
     *     created_at: string,
     *     vence_at: string,
     *     plazo_vencido: bool,
     *     segundos_restantes: int
     * }
     */
    public static function paraConfirmacion(Donacion $donacion, ?CarbonInterface $ahora = null): array
    {
        $creadoEn = $donacion->created_at ?? now();
        $referencia = $ahora ?? now();

        return [
            'minutos_plazo' => self::minutosPlazo(),
            'created_at' => $creadoEn->toIso8601String(),
            'vence_at' => self::venceEn($creadoEn)->toIso8601String(),
            'plazo_vencido' => self::plazoVencido($creadoEn, $referencia),
            'segundos_restantes' => self::segundosRestantes($creadoEn, $referencia),
        ];
    }
}
