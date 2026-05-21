<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\Donacion;
use App\Models\User;
use App\Support\ReferenciaPagoWayna;
use Illuminate\Support\Facades\DB;
use App\Models\Campana;
use Illuminate\Validation\ValidationException;
use App\Events\DonacionCreada;


class DonacionService
{
    public function __construct(
        protected QrCodeService $qrCodeService,
        protected TraceabilityService $traceabilityService,
        protected TelegramService $telegramService,
        protected AuditLogService $auditLogService,
        protected LibelulaService $libelulaService,
    ) {
    }

    /**
     * Registra una donación desde cualquier parte del sistema.
     *
     * Flujo:
     * 1. Crear referencia de pago.
     * 2. Registrar donación como pendiente.
     * 3. Registrar trazabilidad.
     * 4. Generar QR de pago o confirmación.
     * 5. Si es efectivo, notificar por Telegram.
     *
     * La notificación de Telegram se ejecuta después de la transacción
     * para evitar avisar sobre donaciones que podrían revertirse.
     */
    public function registrar(array $data): array
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Registrar la donación dentro de una transacción
        |--------------------------------------------------------------------------
        |
        | Si falla la donación, la trazabilidad o el QR, todo se revierte.
        |
        */

        $resultado = DB::transaction(function () use ($data) {
            $referenciaPago = filled($data['referencia_pago'] ?? null)
                ? trim((string) $data['referencia_pago'])
                : ReferenciaPagoWayna::generar();

            $donacion = Donacion::create([
                'campana_id' => $data['campana_id'],
                'tipo_pago_id' => $data['tipo_pago_id'],
                'visitante_id' => $data['visitante_id'] ?? null,
                'monto' => $data['monto'],
                'metodo' => $data['metodo'],
                'estado_pago' => Donacion::ESTADO_PENDIENTE,
                'referencia_pago' => $referenciaPago,
                'payment_uuid' => strtolower((string) $data['payment_uuid']),
            ]);

            $trazabilidad = $this->traceabilityService->registrarDonacionCreada($donacion);

            $donacion->loadMissing('tipoPago');

            $qrPagoUrl = null;
            $checkoutUrl = null;

            if ($donacion->tipoPago?->esLibelula()) {
                $deuda = $this->libelulaService->crearDeuda(
                    $donacion->loadMissing('visitante'),
                    ['email' => $data['email_cliente'] ?? null],
                );

                $donacion->refresh();
                $checkoutUrl = $deuda->urlPasarela;
                $qrPagoUrl = $deuda->qrSimpleUrl;
            }

            if (! $qrPagoUrl) {
                $qrPagoUrl = $this->qrCodeService->generarQrPago($donacion->fresh());
            }

            return [
                'donacion' => $donacion->fresh(),
                'qr_pago_url' => $qrPagoUrl,
                'checkout_url' => $checkoutUrl ?? $donacion->checkout_url,
                'trazabilidad' => $trazabilidad,
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | 2. Notificar por Telegram después de confirmar la transacción
        |--------------------------------------------------------------------------
        |
        | Solo se notifica cuando la donación es en efectivo.
        | Telegram no valida la donación, solo avisa que hay efectivo pendiente.
        |
        */

        $donacion = $resultado['donacion'];

        $donacion->loadMissing('tipoPago');

        if ($this->esPagoEnEfectivo($donacion)) {
            $this->telegramService->notificarDonacionEfectivoPendiente($donacion);
        }

        // Notificación interna web (toast)
        event(new DonacionCreada($donacion));

        return $resultado;
    }

    /**
     * Confirma una donación en efectivo pendiente.
     *
     * Centraliza la validación de efectivo para admin y cajero. El monto
     * recaudado de la campaña lo actualiza DonacionObserver al cambiar estado_pago.
     */
    public function confirmarPagoEfectivo(Donacion $donacion, ?int $usuarioId = null): Donacion
    {
        $donacionConfirmada = DB::transaction(function () use ($donacion, $usuarioId) {
            /*
            |--------------------------------------------------------------------------
            | 1. Bloquear la donación
            |--------------------------------------------------------------------------
            |
            | lockForUpdate evita que dos usuarios confirmen la misma donación
            | al mismo tiempo.
            |
            */

            $donacion = Donacion::query()
                ->with(['tipoPago', 'campana'])
                ->lockForUpdate()
                ->findOrFail($donacion->id);

            /*
            |--------------------------------------------------------------------------
            | 2. Verificar que sea efectivo
            |--------------------------------------------------------------------------
            */

            if (! $this->esPagoEnEfectivo($donacion)) {
                throw ValidationException::withMessages([
                    'donacion' => 'Solo se pueden confirmar donaciones en efectivo desde esta pantalla.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Verificar que esté pendiente
            |--------------------------------------------------------------------------
            */

            if ($donacion->estado_pago !== Donacion::ESTADO_PENDIENTE) {
                throw ValidationException::withMessages([
                    'donacion' => 'Esta donación ya fue procesada anteriormente.',
                ]);
            }

            $estadoAnterior = $donacion->estado_pago;

            /*
            |--------------------------------------------------------------------------
            | 4. Validar donación
            |--------------------------------------------------------------------------
            */

            $donacion->update([
                'estado_pago' => Donacion::ESTADO_VALIDADO,
            ]);

            $this->traceabilityService->registrarRevisionDonacionPorCajero(
                $donacion->fresh(),
                $estadoAnterior,
                Donacion::ESTADO_VALIDADO,
                $usuarioId,
            );

            return $donacion->refresh();
        });

        $actor = $usuarioId !== null ? User::query()->find($usuarioId) : null;

        $this->auditLogService->registrar(
            AuditAction::CajeroDonationCashConfirmed,
            subject: $donacionConfirmada,
            actor: $actor,
            metadata: ['monto' => (float) $donacionConfirmada->monto],
        );

        return $donacionConfirmada;
    }

    /**
     * Confirma una donación tras webhook de pasarela (S3-02).
     *
     * Solo aplica si la donación sigue pendiente; si ya está validada es idempotente.
     */
    public function confirmarPagoDesdeWebhook(
        Donacion $donacion,
        string $proveedor,
        ?string $transactionId = null,
        ?string $estadoProveedor = null,
        ?array $metadataPago = null,
    ): Donacion {
        return DB::transaction(function () use (
            $donacion,
            $proveedor,
            $transactionId,
            $estadoProveedor,
            $metadataPago,
        ) {
            $donacion = Donacion::query()
                ->lockForUpdate()
                ->findOrFail($donacion->id);

            if ($donacion->estado_pago === Donacion::ESTADO_VALIDADO) {
                return $donacion;
            }

            if ($donacion->estado_pago !== Donacion::ESTADO_PENDIENTE) {
                throw ValidationException::withMessages([
                    'donacion' => 'La donación no puede confirmarse desde webhook en su estado actual.',
                ]);
            }

            $estadoAnterior = $donacion->estado_pago;

            $donacion->update([
                'estado_pago' => Donacion::ESTADO_VALIDADO,
                'proveedor_pago' => $proveedor,
                'estado_proveedor' => $estadoProveedor,
                'transaction_id' => $transactionId ?? $donacion->transaction_id,
                'pagado_en' => now(),
                'metadata_pago' => $metadataPago ?? $donacion->metadata_pago,
            ]);

            $this->traceabilityService->registrarConfirmacionWebhook(
                $donacion->fresh(),
                $estadoAnterior,
                $proveedor,
            );

            return $donacion->refresh();
        });
    }

    /**
     * Determina si una donación corresponde a pago en efectivo.
     *
     * Se revisa el tipo de pago y también el campo metodo como respaldo.
     */
    private function esPagoEnEfectivo(Donacion $donacion): bool
    {
        $metodoEfectivo = str_contains(
            strtolower((string) $donacion->metodo),
            'efectivo',
        );

        $tipoEfectivo = $donacion->tipoPago?->codigo === 'efectivo';

        return $metodoEfectivo || $tipoEfectivo;
    }

    /**
     * Determina si una donación corresponde a pago en efectivo.
     *
     * Se revisa primero la relación tipoPago porque es más consistente.
     * También se revisa el campo metodo como respaldo.
     */
    /*
    private function esPagoEnEfectivo(Donacion $donacion): bool
    {
        return $donacion->tipoPago?->codigo === 'efectivo'
            || $donacion->metodo === 'efectivo';
    }*/
}