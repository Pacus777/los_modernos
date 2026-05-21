<?php

namespace App\Support;

use App\Models\Donacion;
use App\Models\Emprendedor;

/**
 * Textos y filas compartidos entre export Excel y PDF.
 */
class EmprendedorDonacionesReportPresenter
{
    /**
     * @param  array{
     *     estado_pago?: string,
     *     fecha_desde?: string,
     *     fecha_hasta?: string,
     *     rango_monto?: string
     * }  $filtros
     */
    public function textoFiltros(array $filtros): string
    {
        $partes = ['Todos los aportes de tus campañas'];

        if (filled($filtros['estado_pago'] ?? '')) {
            $partes[] = 'Estado: '.$this->etiquetaEstado($filtros['estado_pago']);
        }
        if (filled($filtros['fecha_desde'] ?? '')) {
            $partes[] = 'Desde: '.$filtros['fecha_desde'];
        }
        if (filled($filtros['fecha_hasta'] ?? '')) {
            $partes[] = 'Hasta: '.$filtros['fecha_hasta'];
        }
        if (filled($filtros['rango_monto'] ?? '')) {
            $partes[] = 'Monto: '.$filtros['rango_monto'];
        }

        return implode(' · ', $partes);
    }

    public function etiquetaEstado(string $estado): string
    {
        return match ($estado) {
            Donacion::ESTADO_VALIDADO => 'Validado',
            Donacion::ESTADO_PENDIENTE => 'Pendiente',
            Donacion::ESTADO_RECHAZADO => 'Rechazado',
            default => $estado,
        };
    }

    /**
     * @return array{etiqueta: string, fondo: string, texto: string, ayuda: string}
     */
    public function estiloEstado(string $estado): array
    {
        return match ($estado) {
            Donacion::ESTADO_VALIDADO => [
                'etiqueta' => 'Validado',
                'fondo' => '#d1fae5',
                'texto' => '#047857',
                'ayuda' => 'El aporte ya fue confirmado y suma a tu recaudación.',
            ],
            Donacion::ESTADO_PENDIENTE => [
                'etiqueta' => 'Pendiente',
                'fondo' => '#fef3c7',
                'texto' => '#b45309',
                'ayuda' => 'Está en revisión; aún no suma al total validado.',
            ],
            Donacion::ESTADO_RECHAZADO => [
                'etiqueta' => 'Rechazado',
                'fondo' => '#fee2e2',
                'texto' => '#b91c1c',
                'ayuda' => 'No se aplicó este aporte a tu campaña.',
            ],
            default => [
                'etiqueta' => $estado,
                'fondo' => '#f5f5f4',
                'texto' => '#44403c',
                'ayuda' => '',
            ],
        };
    }

    public function nombreVisitante(Donacion $donacion): string
    {
        $nombre = trim((string) ($donacion->visitante?->nombre ?? ''));

        return $nombre !== '' ? $nombre : 'Sin nombre';
    }

    /**
     * @return array<string, mixed>
     */
    public function filaTabla(Donacion $donacion): array
    {
        $estilo = $this->estiloEstado($donacion->estado_pago);

        return [
            'id' => $donacion->id,
            'fecha' => $donacion->created_at
                ? $donacion->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i')
                : '—',
            'monto' => number_format((float) $donacion->monto, 2, ',', '.'),
            'estado_etiqueta' => $estilo['etiqueta'],
            'estado_fondo' => $estilo['fondo'],
            'estado_texto' => $estilo['texto'],
            'campana' => $donacion->campana?->titulo ?? '—',
            'tipo_pago' => $donacion->tipoPago?->nombre ?? '—',
            'metodo' => $donacion->metodo ?? '—',
            'visitante' => $this->nombreVisitante($donacion),
        ];
    }

    public function nombreEmprendedor(Emprendedor $emprendedor): string
    {
        return $emprendedor->nombreCompleto();
    }

    public function fechaGeneracion(): string
    {
        return now()->timezone(config('app.timezone'))->format('d/m/Y H:i');
    }
}
