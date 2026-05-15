<?php

namespace App\Services;

use App\Models\Donacion;
use Illuminate\Support\Facades\DB;

class DonacionRevisionMasivaService
{
    public function __construct(
        protected TraceabilityService $traceabilityService,
    ) {
    }

    /**
     * Valida o rechaza en bloque solo donaciones pendientes (T-A18).
     *
     * Usa lockForUpdate por fila para evitar doble revisión concurrente.
     *
     * @param  list<int>  $ids
     * @return array{procesadas: int, omitidas: int, monto_total: float}
     */
    public function aplicar(array $ids, string $estadoNuevo, ?int $adminUserId = null): array
    {
        $ids = array_values(array_unique(array_map(intval(...), $ids)));

        if ($ids === []) {
            return [
                'procesadas' => 0,
                'omitidas' => 0,
                'monto_total' => 0.0,
            ];
        }

        $procesadas = 0;
        $omitidas = 0;
        $montoTotal = 0.0;

        DB::transaction(function () use ($ids, $estadoNuevo, $adminUserId, &$procesadas, &$omitidas, &$montoTotal): void {
            $donaciones = Donacion::query()
                ->whereIn('id', $ids)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($ids as $id) {
                $donacion = $donaciones->get($id);

                if (! $donacion || $donacion->estado_pago !== Donacion::ESTADO_PENDIENTE) {
                    $omitidas++;

                    continue;
                }

                $estadoAnterior = $donacion->estado_pago;

                $donacion->update(['estado_pago' => $estadoNuevo]);

                $this->traceabilityService->registrarRevisionDonacionPorAdmin(
                    $donacion->fresh(),
                    $estadoAnterior,
                    $estadoNuevo,
                    $adminUserId,
                );

                $procesadas++;
                $montoTotal += (float) $donacion->monto;
            }
        });

        return [
            'procesadas' => $procesadas,
            'omitidas' => $omitidas,
            'monto_total' => round($montoTotal, 2),
        ];
    }
}
