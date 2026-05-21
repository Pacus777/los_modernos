<?php

namespace App\Support;

use App\Models\Donacion;
use App\Models\Emprendedor;

/**
 * Reporte de donaciones con colores WAYNA (Excel abre .xls como HTML estilizado).
 */
class WaynaDonacionesExcelExport
{
    private const COLOR_NARANJA = '#f07e26';

    private const COLOR_NARANJA_OSCURO = '#d96d1c';

    private const COLOR_CREMA = '#fde9d6';

    private const COLOR_FONDO = '#f7f6f4';

    private const COLOR_BLANCO = '#ffffff';

    private const COLOR_TEXTO = '#1c1917';

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
    public function render(
        Emprendedor $emprendedor,
        array $filtros,
        array $resumen,
        iterable $donaciones,
    ): string {
        $generado = now()->timezone(config('app.timezone'))->format('d/m/Y H:i');
        $filtrosTexto = $this->textoFiltros($filtros);

        $filasDatos = '';
        $indice = 0;

        foreach ($donaciones as $donacion) {
            $filasDatos .= $this->filaDonacion($donacion, $indice % 2 === 0);
            $indice++;
        }

        if ($filasDatos === '') {
            $filasDatos = $this->filaVacia();
        }

        return <<<HTML
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
<x:Name>Donaciones</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
<style>
  table { border-collapse: collapse; font-family: Calibri, Arial, sans-serif; font-size: 11pt; }
  td, th { padding: 8px 10px; vertical-align: middle; }
  .num { mso-number-format: "0.00"; text-align: right; }
  .entero { mso-number-format: "0"; text-align: center; }
</style>
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  {$this->filaTitulo($emprendedor, $generado)}
  {$this->filaResumen($resumen)}
  {$this->filaFiltros($filtrosTexto)}
  <tr><td colspan="8" style="height:10px;border:none;"></td></tr>
  {$this->filaCabeceraColumnas()}
  {$filasDatos}
  {$this->filaPie($emprendedor)}
</table>
</body>
</html>
HTML;
    }

    private function filaTitulo(Emprendedor $emprendedor, string $generado): string
    {
        $nombre = $this->e($emprendedor->nombreCompleto());

        return <<<HTML
<tr>
  <td colspan="8" style="background:{$this->const(self::COLOR_NARANJA)};color:#fff;font-size:18pt;font-weight:bold;border:1px solid {$this->const(self::COLOR_NARANJA_OSCURO)};">
    WAYNA — Historial de donaciones
  </td>
</tr>
<tr>
  <td colspan="5" style="background:{$this->const(self::COLOR_NARANJA_OSCURO)};color:#fff;font-weight:bold;border:1px solid {$this->const(self::COLOR_NARANJA_OSCURO)};">
    Emprendedor: {$nombre}
  </td>
  <td colspan="3" style="background:{$this->const(self::COLOR_NARANJA_OSCURO)};color:#fff;text-align:right;border:1px solid {$this->const(self::COLOR_NARANJA_OSCURO)};">
    Generado: {$this->e($generado)}
  </td>
</tr>
HTML;
    }

    /**
     * @param  array{total_registros: int, total_validado: float, cantidad_validadas: int, cantidad_pendientes: int}  $resumen
     */
    private function filaResumen(array $resumen): string
    {
        $total = (int) ($resumen['total_registros'] ?? 0);
        $validado = number_format((float) ($resumen['total_validado'] ?? 0), 2, ',', '.');
        $cantVal = (int) ($resumen['cantidad_validadas'] ?? 0);
        $cantPen = (int) ($resumen['cantidad_pendientes'] ?? 0);

        $celda = fn (string $titulo, string $valor) => <<<HTML
<td colspan="2" style="background:{$this->const(self::COLOR_CREMA)};color:{$this->const(self::COLOR_TEXTO)};font-weight:bold;border:1px solid #f5d5b8;text-align:center;">
  <span style="font-size:9pt;color:{$this->const(self::COLOR_NARANJA_OSCURO)};text-transform:uppercase;">{$this->e($titulo)}</span><br/>
  <span style="font-size:13pt;">{$this->e($valor)}</span>
</td>
HTML;

        return '<tr>'.implode('', [
            $celda('Registros', (string) $total),
            $celda('Validado (Bs)', $validado),
            $celda('Validadas', (string) $cantVal),
            $celda('Pendientes', (string) $cantPen),
        ]).'</tr>';
    }

    private function filaFiltros(string $texto): string
    {
        return <<<HTML
<tr>
  <td colspan="8" style="background:{$this->const(self::COLOR_FONDO)};color:#57534e;font-size:10pt;border:1px solid #e7e5e4;">
    <strong>Filtros:</strong> {$this->e($texto)}
  </td>
</tr>
HTML;
    }

    private function filaCabeceraColumnas(): string
    {
        $th = fn (string $label) => <<<HTML
<th style="background:{$this->const(self::COLOR_NARANJA)};color:#fff;font-weight:bold;text-align:center;border:1px solid {$this->const(self::COLOR_NARANJA_OSCURO)};white-space:nowrap;">
  {$this->e($label)}
</th>
HTML;

        return '<tr>'.implode('', array_map($th, [
            'ID', 'Fecha', 'Monto (BOB)', 'Estado', 'Campaña', 'Tipo de pago', 'Método', 'Quien apoyó',
        ])).'</tr>';
    }

    private function filaDonacion(Donacion $donacion, bool $filaClara): string
    {
        $fondo = $filaClara ? self::COLOR_BLANCO : self::COLOR_FONDO;
        $estado = $donacion->estado_pago;
        [$fondoEstado, $textoEstado] = $this->coloresEstado($estado);
        $etiqueta = $this->etiquetaEstado($estado);

        $fecha = $donacion->created_at
            ? $donacion->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i')
            : '—';

        return <<<HTML
<tr>
  <td class="entero" style="background:{$this->const($fondo)};border:1px solid #e7e5e4;font-weight:bold;color:{$this->const(self::COLOR_NARANJA_OSCURO)};">{$donacion->id}</td>
  <td style="background:{$this->const($fondo)};border:1px solid #e7e5e4;white-space:nowrap;">{$this->e($fecha)}</td>
  <td class="num" style="background:{$this->const($fondo)};border:1px solid #e7e5e4;font-weight:bold;color:{$this->const(self::COLOR_TEXTO)};">{$this->e(number_format((float) $donacion->monto, 2, ',', '.'))}</td>
  <td style="background:{$this->const($fondoEstado)};color:{$this->const($textoEstado)};font-weight:bold;text-align:center;border:1px solid #e7e5e4;">{$this->e($etiqueta)}</td>
  <td style="background:{$this->const($fondo)};border:1px solid #e7e5e4;">{$this->e($donacion->campana?->titulo ?? '—')}</td>
  <td style="background:{$this->const($fondo)};border:1px solid #e7e5e4;">{$this->e($donacion->tipoPago?->nombre ?? '—')}</td>
  <td style="background:{$this->const($fondo)};border:1px solid #e7e5e4;">{$this->e($donacion->metodo ?? '—')}</td>
  <td style="background:{$this->const($fondo)};border:1px solid #e7e5e4;">{$this->e($this->nombreVisitante($donacion))}</td>
</tr>
HTML;
    }

    private function filaVacia(): string
    {
        return <<<HTML
<tr>
  <td colspan="8" style="background:{$this->const(self::COLOR_FONDO)};color:#78716c;text-align:center;font-style:italic;border:1px solid #e7e5e4;padding:16px;">
    No hay donaciones con los filtros seleccionados.
  </td>
</tr>
HTML;
    }

    private function filaPie(Emprendedor $emprendedor): string
    {
        return <<<HTML
<tr><td colspan="8" style="height:8px;border:none;"></td></tr>
<tr>
  <td colspan="8" style="background:{$this->const(self::COLOR_NARANJA)};color:#fff;font-size:9pt;text-align:center;border:1px solid {$this->const(self::COLOR_NARANJA_OSCURO)};">
    Documento generado por WAYNA · Emprendedor #{$emprendedor->id} · Los montos en Bs. corresponden a aportes registrados en el sistema.
  </td>
</tr>
HTML;
    }

    /**
     * @return array{0: string, 1: string} fondo y texto
     */
    private function coloresEstado(string $estado): array
    {
        return match ($estado) {
            Donacion::ESTADO_VALIDADO => ['#d1fae5', '#047857'],
            Donacion::ESTADO_PENDIENTE => ['#fef3c7', '#b45309'],
            Donacion::ESTADO_RECHAZADO => ['#fee2e2', '#b91c1c'],
            default => [self::COLOR_FONDO, self::COLOR_TEXTO],
        };
    }

    /**
     * @param  array<string, string>  $filtros
     */
    private function textoFiltros(array $filtros): string
    {
        $partes = ['Todos los registros de tus campañas'];

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

    private function nombreVisitante(Donacion $donacion): string
    {
        $nombre = trim((string) ($donacion->visitante?->nombre ?? ''));

        return $nombre !== '' ? $nombre : 'Sin nombre';
    }

    private function etiquetaEstado(string $estado): string
    {
        return match ($estado) {
            Donacion::ESTADO_VALIDADO => 'Validado',
            Donacion::ESTADO_PENDIENTE => 'Pendiente',
            Donacion::ESTADO_RECHAZADO => 'Rechazado',
            default => $estado,
        };
    }

    private function e(?string $valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function const(string $hex): string
    {
        return $hex;
    }
}
