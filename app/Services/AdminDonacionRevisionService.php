<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\Donacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminDonacionRevisionService
{
    public function __construct(
        protected TraceabilityService $traceabilityService,
        protected AuditLogService $auditLogService,
    ) {
    }

    /**
     * Confirma una donación pendiente (estado_pago = validado).
     *
     * S2-08/T-38: Evita condiciones de carrera y dobles confirmaciones/rechazos.
     *
     * @throws ValidationException
     */
    public function confirmar(Donacion $donacion, User $actor): Donacion
    {
        return DB::transaction(function () use ($donacion, $actor) {
            // Lock for update para garantizar exclusión mutua
            $donacionBloqueada = Donacion::query()
                ->whereKey($donacion->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($donacionBloqueada->estado_pago !== Donacion::ESTADO_PENDIENTE) {
                throw ValidationException::withMessages([
                    'donacion' => 'La donación ya no está en estado pendiente y no puede ser confirmada.',
                ]);
            }

            $anterior = $donacionBloqueada->estado_pago;

            // Conservar y mezclar metadatos de pago
            $metadata = $donacionBloqueada->metadata_pago ?? [];
            $metadata['revision_admin'] = [
                'accion' => 'confirmada',
                'user_id' => $actor->id,
                'user_email' => $actor->email,
                'motivo' => null,
                'fecha' => now()->toIso8601String(),
            ];

            $donacionBloqueada->update([
                'estado_pago' => Donacion::ESTADO_VALIDADO,
                'pagado_en' => $donacionBloqueada->pagado_en ?? now(),
                'estado_proveedor' => 'validado_admin',
                'metadata_pago' => $metadata,
            ]);

            $donacionFresh = $donacionBloqueada->fresh();

            // Traceability
            $this->traceabilityService->registrarRevisionDonacionPorAdmin(
                $donacionFresh,
                $anterior,
                Donacion::ESTADO_VALIDADO,
                $actor->id
            );

            // Audit Log
            $this->auditLogService->registrar(
                AuditAction::AdminDonationValidated,
                subject: $donacionFresh,
                actor: $actor,
                metadata: [
                    'estado_anterior' => $anterior,
                    'monto' => (float) $donacionFresh->monto,
                ]
            );

            return $donacionFresh;
        });
    }

    /**
     * Rechaza una donación pendiente (estado_pago = rechazado).
     *
     * S2-08/T-38: Evita condiciones de carrera y dobles confirmaciones/rechazos.
     *
     * @throws ValidationException
     */
    public function rechazar(Donacion $donacion, User $actor, ?string $motivo = null): Donacion
    {
        return DB::transaction(function () use ($donacion, $actor, $motivo) {
            // Lock for update para garantizar exclusión mutua
            $donacionBloqueada = Donacion::query()
                ->whereKey($donacion->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($donacionBloqueada->estado_pago !== Donacion::ESTADO_PENDIENTE) {
                throw ValidationException::withMessages([
                    'donacion' => 'La donación ya no está en estado pendiente y no puede ser rechazada.',
                ]);
            }

            $anterior = $donacionBloqueada->estado_pago;

            // Conservar y mezclar metadatos de pago
            $metadata = $donacionBloqueada->metadata_pago ?? [];
            $metadata['revision_admin'] = [
                'accion' => 'rechazada',
                'user_id' => $actor->id,
                'user_email' => $actor->email,
                'motivo' => $motivo,
                'fecha' => now()->toIso8601String(),
            ];

            $donacionBloqueada->update([
                'estado_pago' => Donacion::ESTADO_RECHAZADO,
                'pagado_en' => null,
                'estado_proveedor' => 'rechazado_admin',
                'metadata_pago' => $metadata,
            ]);

            $donacionFresh = $donacionBloqueada->fresh();

            // Traceability
            $this->traceabilityService->registrarRevisionDonacionPorAdmin(
                $donacionFresh,
                $anterior,
                Donacion::ESTADO_RECHAZADO,
                $actor->id
            );

            // Audit Log
            $this->auditLogService->registrar(
                AuditAction::AdminDonationRejected,
                subject: $donacionFresh,
                actor: $actor,
                metadata: [
                    'estado_anterior' => $anterior,
                    'monto' => (float) $donacionFresh->monto,
                    'motivo' => $motivo,
                ]
            );

            return $donacionFresh;
        });
    }
}
