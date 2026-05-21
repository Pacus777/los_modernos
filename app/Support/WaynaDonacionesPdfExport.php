<?php

namespace App\Support;

use App\Models\Donacion;
use App\Models\Emprendedor;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;

class WaynaDonacionesPdfExport
{
    public function __construct(
        private readonly EmprendedorDonacionesReportPresenter $presenter = new EmprendedorDonacionesReportPresenter,
    ) {}

    /**
     * @param  array{
     *     estado_pago?: string,
     *     fecha_desde?: string,
     *     fecha_hasta?: string,
     *     rango_monto?: string
     * }  $filtros
     * @param  array{
     *     total_registros: int,
     *     total_validado: float,
     *     cantidad_validadas: int,
     *     cantidad_pendientes: int
     * }  $resumen
     */
    public function generar(
        Emprendedor $emprendedor,
        array $filtros,
        array $resumen,
        iterable $donaciones,
    ): string {
        $filas = [];
        foreach ($donaciones as $donacion) {
            $filas[] = $this->presenter->filaTabla($donacion);
        }

        $html = View::make('pdf.emprendedor-donaciones', [
            'logoBase64' => $this->logoBase64(),
            'emprendedorNombre' => $this->presenter->nombreEmprendedor($emprendedor),
            'emprendedorId' => $emprendedor->id,
            'generado' => $this->presenter->fechaGeneracion(),
            'filtrosTexto' => $this->presenter->textoFiltros($filtros),
            'resumen' => $resumen,
            'filas' => $filas,
            'estadosAyuda' => [
                $this->presenter->estiloEstado(Donacion::ESTADO_VALIDADO),
                $this->presenter->estiloEstado(Donacion::ESTADO_PENDIENTE),
                $this->presenter->estiloEstado(Donacion::ESTADO_RECHAZADO),
            ],
        ])->render();

        $opciones = new Options;
        $opciones->set('isHtml5ParserEnabled', true);
        $opciones->set('isRemoteEnabled', false);
        $opciones->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($opciones);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }

    private function logoBase64(): ?string
    {
        $ruta = public_path('images/logo-naranja.png');

        if (! is_readable($ruta)) {
            return null;
        }

        $contenido = file_get_contents($ruta);

        if ($contenido === false) {
            return null;
        }

        return base64_encode($contenido);
    }
}
