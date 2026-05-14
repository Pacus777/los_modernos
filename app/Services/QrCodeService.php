<?php

namespace App\Services;

use App\Models\Donacion;
use App\Models\Emprendedor;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /*
    |--------------------------------------------------------------------------
    | QrCodeService
    |--------------------------------------------------------------------------
    |
    | Este servicio genera los distintos QR del sistema WAYNA:
    |
    | 1. QR de perfil:
    |    Abre el perfil público del emprendedor.
    |
    | 2. QR de pago digital:
    |    Identifica una donación pendiente para pago digital.
    |
    | 3. QR de confirmación en efectivo:
    |    Lo escanea el cajero para confirmar una donación en efectivo.
    |
    */

    /**
     * QR 1: Genera el QR público de un emprendedor.
     *
     * Este QR apunta a:
     * /emprendedor/{id}
     *
     * Sirve para que el turista vea el perfil, historia y campaña.
     */
    public function generarQrPerfil(Emprendedor $emprendedor): string
    {
        $urlPerfil = url("/emprendedor/{$emprendedor->id}");

        $rutaQr = "emprendedores/qrs/emprendedor-{$emprendedor->id}.png";

        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $urlPerfil,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 400,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        $resultado = $builder->build();

        Storage::disk('public')->put($rutaQr, $resultado->getString());

        /*
         * Este método devuelve la ruta relativa porque normalmente se guarda
         * en la tabla emprendedores como qr_url.
         */
        return $rutaQr;
    }

    /**
     * Decide qué QR generar según el método de pago.
     *
     * Si el método contiene "efectivo", genera QR para cajero.
     * Si no, genera QR de pago digital.
     */
    public function generarQrPago(Donacion $donacion): string
    {
        $metodo = strtolower($donacion->metodo);

        if (str_contains($metodo, 'efectivo')) {
            return $this->generarQrConfirmacionEfectivo($donacion);
        }

        return $this->generarQrPagoDigital($donacion);
    }

    /**
     * URL pública del PNG del QR si ya existe en storage (sin regenerar).
     */
    public function urlPublicaQrPagoExistente(Donacion $donacion): ?string
    {
        $metodo = strtolower($donacion->metodo);
        $rutaRelativa = str_contains($metodo, 'efectivo')
            ? "donaciones/qrs/efectivo/donacion-{$donacion->id}.png"
            : "donaciones/qrs/digital/donacion-{$donacion->id}.png";

        if (! Storage::disk('public')->exists($rutaRelativa)) {
            return null;
        }

        return Storage::url($rutaRelativa);
    }

    /**
     * QR 2: Genera QR de pago digital.
     *
     * En el MVP no estamos conectando aún con una pasarela bancaria real.
     * Por eso el QR contiene datos de la donación:
     * referencia, monto, moneda y estado.
     *
     * Más adelante este contenido puede reemplazarse por un QR bancario real.
     */
    public function generarQrPagoDigital(Donacion $donacion): string
    {
        $contenidoQr = json_encode([
            'sistema' => 'Wayna Conecta',
            'tipo' => 'pago_digital',
            'donacion_id' => $donacion->id,
            'referencia_pago' => $donacion->referencia_pago,
            'monto' => (float) $donacion->monto,
            'moneda' => 'BOB',
            'estado_pago' => $donacion->estado_pago,
        ]);

        $rutaQr = "donaciones/qrs/digital/donacion-{$donacion->id}.png";

        return $this->guardarQrEnStorage($contenidoQr, $rutaQr);
    }

    /**
     * QR 3: Genera QR de confirmación en efectivo.
     *
     * Este QR no es para que el turista pague digitalmente.
     * Es para que el cajero lo escanee y confirme que recibió efectivo.
     *
     * La ruta real de confirmación se protegerá después con middleware
     * de cajero/admin, para que un turista no pueda validar pagos.
     */
    public function generarQrConfirmacionEfectivo(Donacion $donacion): string
    {
        $urlConfirmacion = url("/cajero/efectivo/{$donacion->id}/confirmar");

        $rutaQr = "donaciones/qrs/efectivo/donacion-{$donacion->id}.png";

        return $this->guardarQrEnStorage($urlConfirmacion, $rutaQr);
    }

    /**
     * Método reutilizable para generar cualquier QR y guardarlo en storage.
     *
     * Devuelve una URL pública tipo:
     * /storage/donaciones/qrs/...
     */
    private function guardarQrEnStorage(string $contenido, string $rutaQr): string
    {
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $contenido,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 500,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        $resultado = $builder->build();

        Storage::disk('public')->put($rutaQr, $resultado->getString());

        return Storage::url($rutaQr);
    }
}