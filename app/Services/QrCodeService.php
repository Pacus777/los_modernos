<?php

namespace App\Services;

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
    | Servicio encargado de generar códigos QR para el sistema WAYNA.
    | En esta tarea generamos el QR del perfil público del emprendedor.
    |
    */

    /**
     * Genera el QR público de un emprendedor y guarda la imagen PNG en storage.
     *
     * El QR apunta a una ruta pública del sistema:
     * /emprendedor/{id}
     *
     * En la base de datos se guarda solo la ruta relativa del archivo PNG:
     * emprendedores/qrs/emprendedor-1.png
     */
    public function generarQrPerfil(Emprendedor $emprendedor): string
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Construir URL pública del perfil
        |--------------------------------------------------------------------------
        |
        | Esta URL será el contenido real del QR.
        | Cuando el turista escanee el QR, abrirá esta ruta.
        |
        */

        $urlPerfil = url("/emprendedor/{$emprendedor->id}");

        /*
        |--------------------------------------------------------------------------
        | 2. Definir ruta donde se guardará el QR
        |--------------------------------------------------------------------------
        |
        | Esta ruta es relativa al disco public de Laravel.
        | No se guarda /storage/ en la base de datos.
        |
        */

        $rutaQr = "emprendedores/qrs/emprendedor-{$emprendedor->id}.png";

        /*
        |--------------------------------------------------------------------------
        | 3. Generar QR usando Endroid QR Code
        |--------------------------------------------------------------------------
        |
        | Esta versión de la librería usa new Builder(...), no Builder::create().
        |
        */

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

        /*
        |--------------------------------------------------------------------------
        | 4. Guardar archivo PNG en storage público
        |--------------------------------------------------------------------------
        |
        | getString() devuelve el contenido binario de la imagen PNG.
        |
        */

        Storage::disk('public')->put($rutaQr, $resultado->getString());

        return $rutaQr;
    }
}