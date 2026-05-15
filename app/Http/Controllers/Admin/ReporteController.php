<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campana;
use App\Models\Donacion;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReporteController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ReporteController
    |--------------------------------------------------------------------------
    |
    | Controller encargado de preparar métricas y reportes del panel admin.
    |
    | En T-45 solo implementamos el método impacto(), que alimenta
    | el dashboard principal del administrador.
    |
    */

    /**
     * Muestra el dashboard de impacto del sistema.
     *
     * Métricas calculadas:
     * - total recaudado
     * - número de aportes
     * - emprendedores apoyados
     * - progreso por campaña
     *
     * Todas las métricas consideran únicamente donaciones validadas.
     */
    public function impacto(Request $request): Response
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Validar filtros de fecha
        |--------------------------------------------------------------------------
        |
        | Los filtros llegan por query params:
        |
        | /admin/dashboard?fecha_inicio=2026-05-01&fecha_fin=2026-05-19
        |
        | Si no llegan, se calculan métricas generales.
        |
        */

        $filtros = $request->validate([
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        $fechaInicio = $filtros['fecha_inicio'] ?? null;
        $fechaFin = $filtros['fecha_fin'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | 2. Consulta base de donaciones validadas
        |--------------------------------------------------------------------------
        |
        | Solo las donaciones validadas cuentan como impacto real.
        |
        | Las donaciones pendientes no deben sumar al dashboard porque todavía
        | no fueron confirmadas por admin/cajero.
        |
        */

        $donacionesValidadas = Donacion::query()
            ->join('campanas', 'donaciones.campana_id', '=', 'campanas.id')
            ->where('donaciones.estado_pago', Donacion::ESTADO_VALIDADO)
            ->when($fechaInicio, function ($query) use ($fechaInicio) {
                $query->whereDate('donaciones.created_at', '>=', $fechaInicio);
            })
            ->when($fechaFin, function ($query) use ($fechaFin) {
                $query->whereDate('donaciones.created_at', '<=', $fechaFin);
            });

        /*
        |--------------------------------------------------------------------------
        | 3. Métricas principales en una sola consulta
        |--------------------------------------------------------------------------
        |
        | Evitamos hacer una consulta separada para cada tarjeta del dashboard.
        |
        | Calculamos:
        | - suma total
        | - cantidad de aportes
        | - emprendedores distintos apoyados
        |
        */

        $resumen = (clone $donacionesValidadas)
            ->selectRaw('COALESCE(SUM(donaciones.monto), 0) as total_recaudado')
            ->selectRaw('COUNT(donaciones.id) as numero_aportes')
            ->selectRaw('COUNT(DISTINCT campanas.emprendedor_id) as emprendedores_apoyados')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | 4. Progreso por campaña
        |--------------------------------------------------------------------------
        |
        | Se calcula en servidor para que React solo muestre los datos.
        |
        | Si hay filtro de fecha, el progreso se calcula con donaciones
        | validadas dentro de ese período.
        |
        */

        $progresoCampanas = Campana::query()
            ->with('emprendedor:id,nombre,apellidos')
            ->leftJoin('donaciones', function (JoinClause $join) use ($fechaInicio, $fechaFin) {
                $join->on('donaciones.campana_id', '=', 'campanas.id')
                    ->where('donaciones.estado_pago', Donacion::ESTADO_VALIDADO);

                if ($fechaInicio) {
                    $join->whereDate('donaciones.created_at', '>=', $fechaInicio);
                }

                if ($fechaFin) {
                    $join->whereDate('donaciones.created_at', '<=', $fechaFin);
                }
            })
            ->select([
                'campanas.id',
                'campanas.emprendedor_id',
                'campanas.titulo',
                'campanas.meta_apoyo',
                'campanas.monto_recaudado',
                'campanas.estado',
                'campanas.created_at',
            ])
            ->selectRaw('COALESCE(SUM(donaciones.monto), 0) as monto_recaudado_filtrado')
            ->groupBy([
                'campanas.id',
                'campanas.emprendedor_id',
                'campanas.titulo',
                'campanas.meta_apoyo',
                'campanas.monto_recaudado',
                'campanas.estado',
                'campanas.created_at',
            ])
            ->orderByDesc('campanas.created_at')
            ->get()
            ->map(function ($campana) {
                $meta = (float) $campana->meta_apoyo;
                $recaudado = (float) $campana->monto_recaudado_filtrado;

                return [
                    'id' => $campana->id,
                    'titulo' => $campana->titulo,
                    'estado' => $campana->estado,
                    'meta_apoyo' => $meta,
                    'monto_recaudado' => $recaudado,
                    'porcentaje' => $meta > 0
                        ? min(100, round(($recaudado / $meta) * 100, 2))
                        : 0,
                    'emprendedor' => $campana->emprendedor
                        ? [
                            'id' => $campana->emprendedor->id,
                            'nombre' => $campana->emprendedor->nombre,
                            'apellidos' => $campana->emprendedor->apellidos,
                        ]
                        : null,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | 5. Retornar dashboard con props de Inertia
        |--------------------------------------------------------------------------
        |
        | La página React no hará fetch.
        | Solo recibirá metricas, filtros y progresoCampanas.
        |
        */

        return Inertia::render('Admin/Dashboard', [
            'metricas' => [
                'total_recaudado' => (float) $resumen->total_recaudado,
                'numero_aportes' => (int) $resumen->numero_aportes,
                'emprendedores_apoyados' => (int) $resumen->emprendedores_apoyados,
            ],
            'progresoCampanas' => $progresoCampanas,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }
}