<?php

namespace App\Services;

use App\Models\Donacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DonacionService
{
    public function __construct(
        protected QrCodeService $qrCodeService,
        protected TraceabilityService $traceabilityService,
        protected TelegramService $telegramService,
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
            $referenciaPago = $data['referencia_pago']
                ?? 'WAYNA-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5));

            $donacion = Donacion::create([
                'campana_id' => $data['campana_id'],
                'tipo_pago_id' => $data['tipo_pago_id'],
                'visitante_id' => $data['visitante_id'] ?? null,
                'monto' => $data['monto'],
                'metodo' => $data['metodo'],
                'estado_pago' => Donacion::ESTADO_PENDIENTE,
                'referencia_pago' => $referenciaPago,
            ]);

            $trazabilidad = $this->traceabilityService->registrarDonacionCreada($donacion);

            /*
             * Genera el QR correspondiente al método de pago.
             * Por ahora puede ser QR de pago o QR de confirmación manual.
             */
            $qrPagoUrl = $this->qrCodeService->generarQrPago($donacion);

            return [
                'donacion' => $donacion,
                'qr_pago_url' => $qrPagoUrl,
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

        return $resultado;
    }

    /**
     * Determina si una donación corresponde a pago en efectivo.
     *
     * Se revisa primero la relación tipoPago porque es más consistente.
     * También se revisa el campo metodo como respaldo.
     */
    private function esPagoEnEfectivo(Donacion $donacion): bool
    {
        return $donacion->tipoPago?->codigo === 'efectivo'
            || $donacion->metodo === 'efectivo';
    }
}