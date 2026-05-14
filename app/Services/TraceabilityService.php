<?php

namespace App\Services;

use App\Models\Donacion;
use App\Models\Transaccion;
use Illuminate\Support\Facades\App;

class TraceabilityService
{
    /**
     * Registra en `transacciones` la trazabilidad inicial de una donación (PB-12 / T-35).
     *
     * Debe ejecutarse dentro de la misma transacción de base de datos que la donación
     * (p. ej. desde DonacionService::registrar).
     *
     * El UUID de la fila lo genera el modelo (HasUuids) si no se pasa `id` explícito.
     */
    public function registrarDonacionCreada(Donacion $donacion): Transaccion
    {
        return Transaccion::create([
            'origen' => 'turista',
            'destino' => 'campana:'.$donacion->campana_id,
            'estado' => $donacion->estado_pago,
            'metadatos' => [
                'evento' => 'donacion_creada',
                'donacion_id' => $donacion->id,
                'campana_id' => $donacion->campana_id,
                'tipo_pago_id' => $donacion->tipo_pago_id,
                'visitante_id' => $donacion->visitante_id,
                'monto' => (float) $donacion->monto,
                'referencia_pago' => $donacion->referencia_pago,
                'locale' => App::getLocale(),
            ],
        ]);
    }

    /**
     * Registra en `transacciones` cuando un admin valida o rechaza una donación (T-38).
     */
    public function registrarRevisionDonacionPorAdmin(
        Donacion $donacion,
        string $estadoAnterior,
        string $estadoNuevo,
        ?int $adminUserId = null,
    ): Transaccion {
        return Transaccion::create([
            'origen' => 'admin',
            'destino' => 'donacion:'.$donacion->id,
            'estado' => $estadoNuevo,
            'metadatos' => [
                'evento' => 'donacion_revision_admin',
                'donacion_id' => $donacion->id,
                'campana_id' => $donacion->campana_id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $estadoNuevo,
                'monto' => (float) $donacion->monto,
                'admin_user_id' => $adminUserId,
                'locale' => App::getLocale(),
            ],
        ]);
    }

    /**
     * Registra en `transacciones` cuando un cajero confirma efectivo (T-39, paso 4).
     */
    public function registrarRevisionDonacionPorCajero(
        Donacion $donacion,
        string $estadoAnterior,
        string $estadoNuevo,
        ?int $cajeroUserId = null,
    ): Transaccion {
        return Transaccion::create([
            'origen' => 'cajero',
            'destino' => 'donacion:'.$donacion->id,
            'estado' => $estadoNuevo,
            'metadatos' => [
                'evento' => 'donacion_revision_cajero',
                'donacion_id' => $donacion->id,
                'campana_id' => $donacion->campana_id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $estadoNuevo,
                'monto' => (float) $donacion->monto,
                'cajero_user_id' => $cajeroUserId,
                'locale' => App::getLocale(),
            ],
        ]);
    }
}
