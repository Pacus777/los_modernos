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
    ) {
    }

    /**
     * Registra una donación desde cualquier parte del sistema.
     *
     * Esta lógica sale del Controller para no duplicarla después.
     *
     * Flujo:
     * 1. Crear referencia de pago.
     * 2. Registrar donación como pendiente.
     * 3. Registrar trazabilidad inicial.
     * 4. Generar QR de pago.
     *
     * Todo queda dentro de DB::transaction().
     */
    public function registrar(array $data): array
    {
        return DB::transaction(function () use ($data) {
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

            /*
             * Por ahora registra una trazabilidad básica.
             * Cuando llegues a T-34/T-35, este servicio guardará en la tabla transacciones.
             */
            $trazabilidad = $this->traceabilityService->registrarDonacionCreada($donacion);

            /*
             * Genera QR digital o QR de confirmación en efectivo,
             * dependiendo del método de pago.
             */
            $qrPagoUrl = $this->qrCodeService->generarQrPago($donacion);

            return [
                'donacion' => $donacion,
                'qr_pago_url' => $qrPagoUrl,
                'trazabilidad' => $trazabilidad,
            ];
        });
    }
}