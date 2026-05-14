<?php

namespace App\Services;

use App\Models\Donacion;
use Illuminate\Support\Facades\Log;

class TraceabilityService
{
    /**
     * Registra la trazabilidad inicial de una donación.
     *
     * Versión puente:
     * En T-34/T-35 se reemplazará esta lógica para guardar un registro real
     * en la tabla transacciones con UUID.
     */
    public function registrarDonacionCreada(Donacion $donacion): array
    {
        $metadata = [
            'evento' => 'donacion_creada',
            'origen' => 'turista',
            'destino' => 'campana:' . $donacion->campana_id,
            'estado' => $donacion->estado_pago,
            'donacion_id' => $donacion->id,
            'campana_id' => $donacion->campana_id,
            'tipo_pago_id' => $donacion->tipo_pago_id,
            'visitante_id' => $donacion->visitante_id,
            'monto' => (float) $donacion->monto,
            'referencia_pago' => $donacion->referencia_pago,
        ];

        /*
         * Por ahora lo dejamos registrado en log para no adelantar
         * la migración de transacciones que corresponde a T-34.
         */
        Log::info('Trazabilidad de donación creada', $metadata);

        return $metadata;
    }
}