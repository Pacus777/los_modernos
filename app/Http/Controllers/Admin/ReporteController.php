<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campana;
use App\Models\Donacion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Collection;


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

        $campanasActivas = Campana::query()
            ->where('estado', Campana::ESTADO_ACTIVA)
            ->count();

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

        $graficas = [
            'evolucion' => $this->construirEvolucionTemporal(
                clone $donacionesValidadas,
                $fechaInicio,
                $fechaFin,
            ),
            'campanas_por_estado' => $this->construirCampanasPorEstado(),
            'por_metodo' => $this->construirRecaudacionPorMetodo(clone $donacionesValidadas),
            'top_campanas' => $this->construirTopCampanas($progresoCampanas),
        ];

        return Inertia::render('Admin/Dashboard', [
            'metricas' => [
                'total_recaudado' => (float) $resumen->total_recaudado,
                'numero_aportes' => (int) $resumen->numero_aportes,
                'emprendedores_apoyados' => (int) $resumen->emprendedores_apoyados,
                'campanas_activas' => $campanasActivas,
            ],
            'detalleRecaudacion' => $this->construirDetalleRecaudacion($fechaInicio, $fechaFin),
            'progresoCampanas' => $progresoCampanas,
            'graficas' => $graficas,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }

    /**
     * Serie temporal de recaudación y aportes (últimos 6 meses o rango filtrado).
     *
     * @return array<int, array{etiqueta: string, monto: float, aportes: int}>
     */
    private function construirEvolucionTemporal(
        Builder $donacionesValidadas,
        ?string $fechaInicio,
        ?string $fechaFin,
    ): array {
        if ($fechaInicio && $fechaFin) {
            $inicio = Carbon::parse($fechaInicio)->startOfDay();
            $fin = Carbon::parse($fechaFin)->endOfDay();
            $dias = (int) $inicio->diffInDays($fin);

            if ($dias <= 31) {
                $puntos = [];
                for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
                    $puntos[] = $this->puntoEvolucion(
                        $donacionesValidadas,
                        $fecha->copy()->startOfDay(),
                        $fecha->copy()->endOfDay(),
                        $fecha->format('d/m'),
                    );
                }

                return $puntos;
            }
        }

        $puntos = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $puntos[] = $this->puntoEvolucion(
                $donacionesValidadas,
                $mes->copy()->startOfMonth(),
                $mes->copy()->endOfMonth(),
                $this->etiquetaMes($mes),
            );
        }

        return $puntos;
    }

    /**
     * @return array{etiqueta: string, monto: float, aportes: int}
     */
    private function puntoEvolucion(
        Builder $donacionesValidadas,
        Carbon $desde,
        Carbon $hasta,
        string $etiqueta,
    ): array {
        $fila = (clone $donacionesValidadas)
            ->whereBetween('donaciones.created_at', [$desde, $hasta])
            ->selectRaw('COALESCE(SUM(donaciones.monto), 0) as monto')
            ->selectRaw('COUNT(donaciones.id) as aportes')
            ->first();

        return [
            'etiqueta' => $etiqueta,
            'monto' => (float) ($fila->monto ?? 0),
            'aportes' => (int) ($fila->aportes ?? 0),
        ];
    }

    private function etiquetaMes(Carbon $fecha): string
    {
        $abreviaturas = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        return $abreviaturas[$fecha->month - 1].' '.$fecha->format('y');
    }

    /**
     * @return array<int, array{estado: string, etiqueta: string, total: int}>
     */
    private function construirCampanasPorEstado(): array
    {
        $etiquetas = [
            Campana::ESTADO_ACTIVA => 'Activas',
            Campana::ESTADO_INACTIVA => 'Inactivas',
            Campana::ESTADO_FINALIZADA => 'Finalizadas',
        ];

        return Campana::query()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($fila) => [
                'estado' => $fila->estado,
                'etiqueta' => $etiquetas[$fila->estado] ?? ucfirst((string) $fila->estado),
                'total' => (int) $fila->total,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{metodo: string, etiqueta: string, monto: float, aportes: int}>
     */
    private function construirRecaudacionPorMetodo(Builder $donacionesValidadas): array
    {
        return $donacionesValidadas
            ->selectRaw('donaciones.metodo')
            ->selectRaw('COALESCE(SUM(donaciones.monto), 0) as monto')
            ->selectRaw('COUNT(donaciones.id) as aportes')
            ->groupBy('donaciones.metodo')
            ->orderByDesc('monto')
            ->get()
            ->map(fn ($fila) => [
                'metodo' => $fila->metodo,
                'etiqueta' => $this->etiquetaMetodoPago((string) $fila->metodo),
                'monto' => (float) $fila->monto,
                'aportes' => (int) $fila->aportes,
            ])
            ->values()
            ->all();
    }

    private function etiquetaMetodoPago(string $metodo): string
    {
        $normalizado = str_replace(['_', '-'], ' ', strtolower(trim($metodo)));

        return $normalizado !== ''
            ? ucwords($normalizado)
            : 'Sin método';
    }

    private function consultaDonacionesImpacto(?string $fechaInicio, ?string $fechaFin): Builder
    {
        return Donacion::query()
            ->join('campanas', 'donaciones.campana_id', '=', 'campanas.id')
            ->leftJoin('tipos_pago', 'donaciones.tipo_pago_id', '=', 'tipos_pago.id')
            ->whereIn('donaciones.estado_pago', [
                Donacion::ESTADO_VALIDADO,
                Donacion::ESTADO_PENDIENTE,
            ])
            ->when($fechaInicio, function ($query) use ($fechaInicio) {
                $query->whereDate('donaciones.created_at', '>=', $fechaInicio);
            })
            ->when($fechaFin, function ($query) use ($fechaFin) {
                $query->whereDate('donaciones.created_at', '<=', $fechaFin);
            });
    }

    /**
     * @return array{efectivo: string, qr: string}
     */
    private function condicionesCanalPago(): array
    {
        return [
            'efectivo' => "(LOWER(donaciones.metodo) LIKE '%efectivo%' OR tipos_pago.codigo = 'efectivo')",
            'qr' => "(LOWER(donaciones.metodo) LIKE '%qr%' OR tipos_pago.codigo = 'qr')",
        ];
    }

    private function aplicarAgregadosRecaudacion(Builder $query): Builder
    {
        $canales = $this->condicionesCanalPago();

        return $query
            ->selectRaw('COALESCE(SUM(donaciones.monto), 0) as total_general')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN donaciones.estado_pago = ? THEN donaciones.monto ELSE 0 END), 0) as total_validado',
                [Donacion::ESTADO_VALIDADO],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN donaciones.estado_pago = ? THEN donaciones.monto ELSE 0 END), 0) as total_pendiente',
                [Donacion::ESTADO_PENDIENTE],
            )
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN donaciones.estado_pago = ? AND {$canales['efectivo']} THEN donaciones.monto ELSE 0 END), 0) as total_efectivo",
                [Donacion::ESTADO_VALIDADO],
            )
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN donaciones.estado_pago = ? AND {$canales['qr']} THEN donaciones.monto ELSE 0 END), 0) as total_qr",
                [Donacion::ESTADO_VALIDADO],
            )
            ->selectRaw(
                'COUNT(CASE WHEN donaciones.estado_pago = ? THEN 1 END) as aportes_validados',
                [Donacion::ESTADO_VALIDADO],
            )
            ->selectRaw(
                'COUNT(CASE WHEN donaciones.estado_pago = ? THEN 1 END) as aportes_pendientes',
                [Donacion::ESTADO_PENDIENTE],
            );
    }

    /**
     * @return array<string, mixed>
     */
    private function filaRecaudacionToArray(object $fila, array $extra = []): array
    {
        return array_merge($extra, [
            'total_general' => (float) ($fila->total_general ?? 0),
            'total_efectivo' => (float) ($fila->total_efectivo ?? 0),
            'total_qr' => (float) ($fila->total_qr ?? 0),
            'total_validado' => (float) ($fila->total_validado ?? 0),
            'total_pendiente' => (float) ($fila->total_pendiente ?? 0),
            'aportes_validados' => (int) ($fila->aportes_validados ?? 0),
            'aportes_pendientes' => (int) ($fila->aportes_pendientes ?? 0),
        ]);
    }

    /**
     * Desglose de recaudación para el modal del dashboard (T-A4).
     */
    private function construirDetalleRecaudacion(?string $fechaInicio, ?string $fechaFin): array
    {
        $fila = $this->aplicarAgregadosRecaudacion(
            $this->consultaDonacionesImpacto($fechaInicio, $fechaFin),
        )->first();

        return array_merge(
            $this->filaRecaudacionToArray($fila ?? new \stdClass),
            [
                'por_emprendedor' => $this->construirDesglosePorEmprendedor($fechaInicio, $fechaFin),
                'por_campana' => $this->construirDesglosePorCampana($fechaInicio, $fechaFin),
            ],
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function construirDesglosePorEmprendedor(?string $fechaInicio, ?string $fechaFin): array
    {
        return $this->aplicarAgregadosRecaudacion(
            $this->consultaDonacionesImpacto($fechaInicio, $fechaFin)
                ->join('emprendedores', 'campanas.emprendedor_id', '=', 'emprendedores.id')
                ->select([
                    'emprendedores.id',
                    'emprendedores.nombre',
                    'emprendedores.apellidos',
                    'emprendedores.estado',
                ])
                ->groupBy(
                    'emprendedores.id',
                    'emprendedores.nombre',
                    'emprendedores.apellidos',
                    'emprendedores.estado',
                )
                ->havingRaw('COALESCE(SUM(donaciones.monto), 0) > 0')
                ->orderByDesc('total_validado'),
        )
            ->get()
            ->map(fn ($fila) => $this->filaRecaudacionToArray($fila, [
                'id' => (int) $fila->id,
                'nombre' => trim($fila->nombre.' '.$fila->apellidos),
                'estado' => $fila->estado,
            ]))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function construirDesglosePorCampana(?string $fechaInicio, ?string $fechaFin): array
    {
        return $this->aplicarAgregadosRecaudacion(
            $this->consultaDonacionesImpacto($fechaInicio, $fechaFin)
                ->join('emprendedores', 'campanas.emprendedor_id', '=', 'emprendedores.id')
                ->select([
                    'campanas.id',
                    'campanas.titulo',
                    'campanas.estado',
                    'emprendedores.nombre as emprendedor_nombre',
                    'emprendedores.apellidos as emprendedor_apellidos',
                ])
                ->groupBy(
                    'campanas.id',
                    'campanas.titulo',
                    'campanas.estado',
                    'emprendedores.nombre',
                    'emprendedores.apellidos',
                )
                ->havingRaw('COALESCE(SUM(donaciones.monto), 0) > 0')
                ->orderByDesc('total_validado'),
        )
            ->get()
            ->map(fn ($fila) => $this->filaRecaudacionToArray($fila, [
                'id' => (int) $fila->id,
                'titulo' => $fila->titulo,
                'estado' => $fila->estado,
                'emprendedor' => trim($fila->emprendedor_nombre.' '.$fila->emprendedor_apellidos),
            ]))
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $progresoCampanas
     * @return array<int, array{id: int, titulo: string, monto: float, porcentaje: float}>
     */
    private function construirTopCampanas(Collection $progresoCampanas): array
    {
        return $progresoCampanas
            ->sortByDesc('monto_recaudado')
            ->take(5)
            ->values()
            ->map(fn ($campana) => [
                'id' => $campana['id'],
                'titulo' => $campana['titulo'],
                'monto' => (float) $campana['monto_recaudado'],
                'porcentaje' => (float) $campana['porcentaje'],
            ])
            ->all();
    }

    /**
 * Muestra el reporte paginado de donaciones.
 *
 * Filtros disponibles:
 * - emprendedor_id
 * - estado_pago
 * - fecha_inicio
 * - fecha_fin
 *
 * La información llega a React como props de Inertia.
 * No se retorna JSON ni se usa fetch.
 */
    public function donaciones(Request $request): Response
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Validar filtros recibidos por URL
        |--------------------------------------------------------------------------
        |
        | Ejemplo de URL:
        |
        | /admin/reportes?emprendedor_id=2&estado_pago=validado
        | /admin/reportes?fecha_inicio=2026-05-01&fecha_fin=2026-05-19
        |
        */

        $filtros = $request->validate([
            'emprendedor_id' => ['nullable', 'integer', 'exists:emprendedores,id'],
            'estado_pago' => ['nullable', 'string', 'in:pendiente,validado,rechazado'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2. Consultar donaciones con relaciones necesarias
        |--------------------------------------------------------------------------
        |
        | Donacion no tiene emprendedor_id directo.
        | La relación correcta es:
        |
        | Donacion -> Campana -> Emprendedor
        |
        | Por eso filtramos emprendedor usando whereHas('campana').
        |
        */

        $donaciones = Donacion::query()
            ->with([
                'campana.emprendedor',
                'tipoPago',
                'visitante',
            ])
            ->when($filtros['emprendedor_id'] ?? null, function ($query, $emprendedorId) {
                $query->whereHas('campana', function ($campanaQuery) use ($emprendedorId) {
                    $campanaQuery->where('emprendedor_id', $emprendedorId);
                });
            })
            ->when($filtros['estado_pago'] ?? null, function ($query, $estadoPago) {
                $query->where('estado_pago', $estadoPago);
            })
            ->when($filtros['fecha_inicio'] ?? null, function ($query, $fechaInicio) {
                $query->whereDate('created_at', '>=', $fechaInicio);
            })
            ->when($filtros['fecha_fin'] ?? null, function ($query, $fechaFin) {
                $query->whereDate('created_at', '<=', $fechaFin);
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | 3. Datos para los filtros del frontend
        |--------------------------------------------------------------------------
        |
        | La página React necesita la lista de emprendedores para el selector.
        | No debe hacer fetch adicional.
        |
        */

        $emprendedores = Emprendedor::query()
            ->select('id', 'nombre', 'apellidos')
            ->orderBy('nombre')
            ->get()
            ->map(function ($emprendedor) {
                return [
                    'id' => $emprendedor->id,
                    'nombre_completo' => $emprendedor->nombreCompleto(),
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | 4. Retornar página Inertia
        |--------------------------------------------------------------------------
        |
        | T-48 creará:
        | resources/js/Pages/Admin/Reportes/Index.jsx
        |
        */

        return Inertia::render('Admin/Reportes/Index', [
            'donaciones' => $donaciones,
            'emprendedores' => $emprendedores,
            'estados' => [
                Donacion::ESTADO_PENDIENTE,
                Donacion::ESTADO_VALIDADO,
                Donacion::ESTADO_RECHAZADO,
            ],
            'filtros' => [
                'emprendedor_id' => $filtros['emprendedor_id'] ?? '',
                'estado_pago' => $filtros['estado_pago'] ?? '',
                'fecha_inicio' => $filtros['fecha_inicio'] ?? '',
                'fecha_fin' => $filtros['fecha_fin'] ?? '',
            ],
        ]);
    }


}