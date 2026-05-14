<?php

namespace App\Http\Controllers\Turista;

use App\Http\Controllers\Controller;
use App\Models\Donacion;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DonacionConfirmacionController extends Controller
{
    /**
     * Pantalla de confirmación tras registrar una donación.
     *
     * Usa el id en la URL para que un cambio de idioma (nuevo GET) siga mostrando
     * referencia y QR sin depender del flash de Laravel, que solo dura un request.
     */
    public function show(Request $request, Donacion $donacion, QrCodeService $qrCodeService): Response
    {
        if ($donacion->estado_pago !== Donacion::ESTADO_PENDIENTE) {
            abort(404);
        }

        $qrPagoUrl = $qrCodeService->urlPublicaQrPagoExistente($donacion)
            ?? $qrCodeService->generarQrPago($donacion);

        $success = $request->session()->pull('success');

        return Inertia::render('Turista/Confirmacion', [
            'confirmacion' => [
                'donacion_id' => $donacion->id,
                'referencia_pago' => $donacion->referencia_pago,
                'monto' => (string) $donacion->monto,
                'metodo' => $donacion->metodo,
            ],
            'qr_pago_url' => $qrPagoUrl,
            'success' => $success,
        ]);
    }
}
