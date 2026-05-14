<?php

namespace App\Http\Controllers\Turista;

use App\Http\Controllers\Controller;
use App\Models\Donacion;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DonacionController extends Controller
{
    /**
     * Registra una donación del turista.
     *
     * La donación nace como "pendiente" porque todavía debe ser
     * confirmada por el admin o cajero.
     *
     * Todo se ejecuta dentro de una transacción:
     * - crear donación,
     * - generar referencia,
     * - generar QR de pago.
     *
     * Si algo falla, Laravel revierte la operación.
     */
    public function store(Request $request, QrCodeService $qrCodeService): RedirectResponse
    {
        $validated = $request->validate([
            'campana_id' => ['required', 'exists:campanas,id'],
            'tipo_pago_id' => ['required', 'exists:tipos_pago,id'],
            'visitante_id' => ['nullable', 'exists:visitantes,id'],
            'monto' => ['required', 'numeric', 'min:1', 'max:999999.99'],
            'metodo' => ['required', 'string', 'max:50'],
            'referencia_pago' => ['nullable', 'string', 'max:150'],
        ]);

        $resultado = DB::transaction(function () use ($validated, $qrCodeService) {
            $referenciaPago = $validated['referencia_pago']
                ?? 'WAYNA-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5));

            $donacion = Donacion::create([
                'campana_id' => $validated['campana_id'],
                'tipo_pago_id' => $validated['tipo_pago_id'],
                'visitante_id' => $validated['visitante_id'] ?? null,
                'monto' => $validated['monto'],
                'metodo' => $validated['metodo'],
                'estado_pago' => Donacion::ESTADO_PENDIENTE,
                'referencia_pago' => $referenciaPago,
            ]);

            $qrPagoUrl = $qrCodeService->generarQrPago($donacion);

            return [
                'donacion' => $donacion,
                'qr_pago_url' => $qrPagoUrl,
            ];
        });

        /*
         * T-23 pide redirigir con flash.
         * La página Turista/Confirmacion se hará en T-26.
         */
        return redirect()
            ->route('turista.donaciones.confirmacion')
            ->with('success', 'Donación registrada correctamente. Escanea el QR para completar el pago.')
            ->with('donacion_id', $resultado['donacion']->id)
            ->with('referencia_pago', $resultado['donacion']->referencia_pago)
            ->with('qr_pago_url', $resultado['qr_pago_url']);
    }
}