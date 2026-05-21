<?php

namespace App\Services;

use App\Models\Donacion;
use App\Models\Transaccion;
use Illuminate\Support\Facades\App;

/**
 * Registro interno en `transacciones` (T-A20: activo aunque la vista no esté en el menú admin).
 */
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

    /**
     * Registra confirmación automática vía webhook de pasarela (S3-02).
     */
    public function registrarConfirmacionWebhook(
        Donacion $donacion,
        string $estadoAnterior,
        string $proveedor,
    ): Transaccion {
        return Transaccion::create([
            'origen' => 'webhook',
            'destino' => 'donacion:'.$donacion->id,
            'estado' => $donacion->estado_pago,
            'metadatos' => [
                'evento' => 'donacion_confirmada_webhook',
                'donacion_id' => $donacion->id,
                'campana_id' => $donacion->campana_id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $donacion->estado_pago,
                'proveedor' => $proveedor,
                'transaction_id' => $donacion->transaction_id,
                'locale' => App::getLocale(),
            ],
        ]);
    }

    /**
     * Texto de la última revisión (admin o cajero) por donación, para el detalle en panel (T-A17).
     *
     * @param  list<int>  $donacionIds
     * @return array<int, string>
     */
    public function observacionesValidacionPorDonaciones(array $donacionIds): array
    {
        if ($donacionIds === []) {
            return [];
        }

        $destinos = array_map(
            static fn (int $id): string => 'donacion:'.$id,
            $donacionIds,
        );

        $transacciones = Transaccion::query()
            ->whereIn('destino', $destinos)
            ->whereIn('origen', ['admin', 'cajero'])
            ->orderByDesc('created_at')
            ->get();

        $observaciones = [];

        foreach ($transacciones as $transaccion) {
            if (! preg_match('/^donacion:(\d+)$/', (string) $transaccion->destino, $coincidencias)) {
                continue;
            }

            $donacionId = (int) $coincidencias[1];

            if (isset($observaciones[$donacionId])) {
                continue;
            }

            $observaciones[$donacionId] = $this->formatearObservacionValidacion($transaccion);
        }

        return $observaciones;
    }

    private function formatearObservacionValidacion(Transaccion $transaccion): string
    {
        $metadatos = is_array($transaccion->metadatos) ? $transaccion->metadatos : [];
        $estadoAnterior = (string) ($metadatos['estado_anterior'] ?? '—');
        $estadoNuevo = (string) ($metadatos['estado_nuevo'] ?? $transaccion->estado);

        $actor = match ($transaccion->origen) {
            'admin' => 'Administrador',
            'cajero' => 'Cajero',
            default => ucfirst((string) $transaccion->origen),
        };

        $fecha = $transaccion->created_at?->timezone('America/La_Paz')->format('d/m/Y H:i');

        $texto = "{$actor}: {$estadoAnterior} → {$estadoNuevo}";

        return $fecha ? "{$texto} ({$fecha})" : $texto;
    }
}
