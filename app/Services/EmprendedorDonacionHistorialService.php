<?php

namespace App\Services;

use App\Enums\RangoMonto;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Support\WaynaDonacionesExcelExport;
use App\Support\WaynaDonacionesPdfExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmprendedorDonacionHistorialService
{
    /**
     * @param  array{
     *     estado_pago?: string,
     *     fecha_desde?: string,
     *     fecha_hasta?: string,
     *     rango_monto?: string
     * }  $filtros
     */
    public function consulta(Emprendedor $emprendedor, array $filtros = []): Builder
    {
        return Donacion::query()
            ->whereHas('campana', fn ($q) => $q->where('emprendedor_id', $emprendedor->id))
            ->with(['campana:id,titulo,emprendedor_id', 'tipoPago:id,nombre,codigo', 'visitante:id,nombre'])
            ->when(
                filled($filtros['estado_pago'] ?? null),
                fn ($q) => $q->where('estado_pago', $filtros['estado_pago']),
            )
            ->when(
                filled($filtros['fecha_desde'] ?? null),
                fn ($q) => $q->whereDate('created_at', '>=', $filtros['fecha_desde']),
            )
            ->when(
                filled($filtros['fecha_hasta'] ?? null),
                fn ($q) => $q->whereDate('created_at', '<=', $filtros['fecha_hasta']),
            )
            ->when(
                $rango = RangoMonto::desdeFiltro($filtros['rango_monto'] ?? ''),
                fn ($q) => $rango->aplicarFiltro($q, 'monto'),
            )
            ->orderByDesc('created_at');
    }

    /**
     * @return array{
     *     total_registros: int,
     *     total_validado: float,
     *     cantidad_validadas: int,
     *     cantidad_pendientes: int
     * }
     */
    public function resumen(Emprendedor $emprendedor, array $filtros = []): array
    {
        $base = $this->consulta($emprendedor, $filtros);

        $validadas = (clone $base)->validadas();

        return [
            'total_registros' => (clone $base)->count(),
            'total_validado' => (float) ((clone $validadas)->sum('monto') ?? 0),
            'cantidad_validadas' => (clone $validadas)->count(),
            'cantidad_pendientes' => (clone $base)->pendientes()->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializarFila(Donacion $donacion): array
    {
        return [
            'id' => $donacion->id,
            'monto' => (float) $donacion->monto,
            'moneda' => $donacion->moneda,
            'estado_pago' => $donacion->estado_pago,
            'metodo' => $donacion->metodo,
            'referencia_pago' => $donacion->referencia_pago,
            'campana_titulo' => $donacion->campana?->titulo,
            'tipo_pago' => $donacion->tipoPago?->nombre,
            'visitante_nombre' => $this->nombreVisitante($donacion),
            'fecha' => $donacion->created_at?->toIso8601String(),
            'fecha_legible' => $donacion->created_at
                ? $donacion->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i')
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function respuestaExcelWayna(Emprendedor $emprendedor, array $filtros): StreamedResponse
    {
        $nombreArchivo = sprintf(
            'wayna-donaciones-%s-%s.xls',
            $emprendedor->id,
            now()->format('Y-m-d_His'),
        );

        $resumen = $this->resumen($emprendedor, $filtros);
        $donaciones = $this->donacionesParaExport($emprendedor, $filtros);
        $exportador = new WaynaDonacionesExcelExport;

        return response()->streamDownload(
            function () use ($exportador, $emprendedor, $filtros, $resumen, $donaciones): void {
                echo $exportador->render($emprendedor, $filtros, $resumen, $donaciones);
            },
            $nombreArchivo,
            [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$nombreArchivo.'"',
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function respuestaPdfWayna(Emprendedor $emprendedor, array $filtros): Response
    {
        $nombreArchivo = sprintf(
            'wayna-donaciones-%s-%s.pdf',
            $emprendedor->id,
            now()->format('Y-m-d_His'),
        );

        $resumen = $this->resumen($emprendedor, $filtros);
        $donaciones = $this->donacionesParaExport($emprendedor, $filtros);
        $pdf = (new WaynaDonacionesPdfExport)->generar($emprendedor, $filtros, $resumen, $donaciones);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$nombreArchivo.'"',
        ]);
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    private function donacionesParaExport(Emprendedor $emprendedor, array $filtros): Collection
    {
        $donaciones = collect();

        $this->consulta($emprendedor, $filtros)->chunk(200, function ($lote) use (&$donaciones): void {
            $donaciones = $donaciones->concat($lote);
        });

        return $donaciones;
    }

    private function nombreVisitante(Donacion $donacion): string
    {
        $nombre = trim((string) ($donacion->visitante?->nombre ?? ''));

        return $nombre !== '' ? $nombre : 'Sin nombre';
    }
}
