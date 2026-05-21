<?php

namespace App\Support;

use App\Models\Donacion;

/**
 * CSV de donaciones para liquidación admin (S2-11).
 */
class WaynaDonacionesCsvExport
{
    /**
     * @param  iterable<int, Donacion>  $donaciones
     */
    public function render(iterable $donaciones): string
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            return '';
        }

        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'ID',
            'Fecha registro',
            'Emprendedor',
            'Campaña',
            'Monto BOB',
            'Estado pago',
            'Tipo de pago',
            'Método',
            'Referencia',
            'Proveedor',
            'Transaction ID',
            'Visitante',
            'Pagado en',
        ], ';');

        foreach ($donaciones as $donacion) {
            fputcsv($handle, $this->fila($donacion), ';');
        }

        rewind($handle);
        $contenido = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $contenido;
    }

    /**
     * @return list<string|int|float|null>
     */
    private function fila(Donacion $donacion): array
    {
        $emprendedor = $donacion->campana?->emprendedor;
        $nombreEmprendedor = $emprendedor
            ? trim($emprendedor->nombre.' '.$emprendedor->apellidos)
            : '';

        return [
            $donacion->id,
            $donacion->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i:s') ?? '',
            $nombreEmprendedor,
            $donacion->campana?->titulo ?? '',
            number_format((float) $donacion->monto, 2, '.', ''),
            $donacion->estado_pago,
            $donacion->tipoPago?->nombre ?? '',
            $donacion->metodo ?? '',
            $donacion->referencia_pago ?? '',
            $donacion->proveedor_pago ?? '',
            $donacion->transaction_id ?? '',
            trim((string) ($donacion->visitante?->nombre ?? '')) ?: 'Sin nombre',
            $donacion->pagado_en?->timezone(config('app.timezone'))->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
