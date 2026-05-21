<?php

namespace App\Services;

use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * E-05 / S4-04 — Datos agregados para el panel del emprendedor autenticado.
 */
class EmprendedorDashboardService
{
    /**
     * @return array{
     *     perfil: array<string, mixed>,
     *     campana_activa: array<string, mixed>|null,
     *     progreso: array{meta: float, monto_recaudado: float, monto_faltante: float, porcentaje: float},
     *     estadisticas: array<string, int|float>,
     *     graficas: array<string, mixed>,
     *     ultimas_donaciones: list<array<string, mixed>>,
     *     acciones: array{perfil_publico_url: string, qr_url: string|null}
     * }
     */
    public function datosParaPanel(Emprendedor $emprendedor): array
    {
        $campanaActiva = $this->resolverCampanaActiva($emprendedor);

        return [
            'perfil' => $this->serializarPerfil($emprendedor),
            'campana_activa' => $campanaActiva ? $this->serializarCampana($campanaActiva) : null,
            'progreso' => $this->calcularProgreso($campanaActiva, $emprendedor),
            'estadisticas' => $this->estadisticas($emprendedor),
            'graficas' => $this->graficas($emprendedor),
            'ultimas_donaciones' => $this->ultimasDonaciones($emprendedor),
            'acciones' => [
                'perfil_publico_url' => route('turista.emprendedor.show', $emprendedor),
                'qr_url' => $emprendedor->qr_url
                    ? EmprendedorMediosService::urlAlmacenPublico($emprendedor->qr_url)
                    : null,
            ],
        ];
    }

    private function resolverCampanaActiva(Emprendedor $emprendedor): ?Campana
    {
        return $emprendedor->campanas()
            ->visibleEnPerfilTurista()
            ->withSum([
                'donaciones as monto_validado' => function ($query) {
                    $query->where('estado_pago', Donacion::ESTADO_VALIDADO);
                },
            ], 'monto')
            ->orderByDesc('fecha_inicio')
            ->first();
    }

    /**
     * @return array{meta: float, monto_recaudado: float, monto_faltante: float, porcentaje: float}
     */
    private function calcularProgreso(?Campana $campana, Emprendedor $emprendedor): array
    {
        $meta = $campana
            ? (float) $campana->meta_apoyo
            : (float) ($emprendedor->meta_monto ?? 0);

        $montoValidado = $campana
            ? (float) ($campana->monto_validado ?? 0)
            : 0.0;

        $porcentaje = $meta > 0
            ? min(round(($montoValidado / $meta) * 100, 2), 100.0)
            : 0.0;

        return [
            'meta' => $meta,
            'monto_recaudado' => $montoValidado,
            'monto_faltante' => max($meta - $montoValidado, 0.0),
            'porcentaje' => $porcentaje,
        ];
    }

    /**
     * @return array<string, int|float>
     */
    private function estadisticas(Emprendedor $emprendedor): array
    {
        $base = Donacion::query()
            ->whereHas('campana', fn ($q) => $q->where('emprendedor_id', $emprendedor->id));

        $validadas = (clone $base)->validadas();
        $pendientes = (clone $base)->pendientes();

        $reacciones = DB::table('emprendedor_post_reacciones')
            ->join('emprendedor_posts', 'emprendedor_post_reacciones.emprendedor_post_id', '=', 'emprendedor_posts.id')
            ->where('emprendedor_posts.emprendedor_id', $emprendedor->id)
            ->count();

        return [
            'donaciones_validadas_total' => (float) ((clone $validadas)->sum('monto') ?? 0),
            'donaciones_validadas_cantidad' => (clone $validadas)->count(),
            'donaciones_pendientes_cantidad' => (clone $pendientes)->count(),
            'seguidores' => $emprendedor->seguidoresVisitantes()->count(),
            'puntos' => $emprendedor->puntos()->count(),
            'publicaciones' => $emprendedor->posts()->count(),
            'reacciones' => $reacciones,
        ];
    }

    /**
     * S4-04 — Máximo 3 gráficas importantes para el dashboard del emprendedor.
     *
     * @return array<string, mixed>
     */
    private function graficas(Emprendedor $emprendedor): array
    {
        $dias = 7;

        return [
            'periodo' => [
                'dias' => $dias,
                'inicio' => now()->subDays($dias - 1)->toDateString(),
                'fin' => now()->toDateString(),
            ],
            'aportes_por_dia' => $this->aportesValidadosPorDia($emprendedor, $dias),
            'contenido_por_dia' => $this->contenidoPorDia($emprendedor, $dias),
            'seguidores_por_dia' => $this->seguidoresPorDia($emprendedor, $dias),
        ];
    }

    /**
     * @return list<array{fecha: string, etiqueta: string, monto: float, aportes: int}>
     */
    private function aportesValidadosPorDia(Emprendedor $emprendedor, int $dias): array
    {
        [$inicio, $fin] = $this->periodoUltimosDias($dias);

        $filas = Donacion::query()
            ->selectRaw('DATE(donaciones.created_at) as fecha')
            ->selectRaw('COUNT(*) as aportes')
            ->selectRaw('COALESCE(SUM(donaciones.monto), 0) as monto')
            ->where('donaciones.estado_pago', Donacion::ESTADO_VALIDADO)
            ->whereBetween('donaciones.created_at', [$inicio, $fin])
            ->whereHas('campana', fn ($q) => $q->where('emprendedor_id', $emprendedor->id))
            ->groupByRaw('DATE(donaciones.created_at)')
            ->orderByRaw('DATE(donaciones.created_at)')
            ->get()
            ->keyBy(fn ($fila) => (string) $fila->fecha);

        return $this->serieDiaria($dias, function (string $fecha) use ($filas): array {
            $fila = $filas->get($fecha);

            return [
                'monto' => (float) ($fila->monto ?? 0),
                'aportes' => (int) ($fila->aportes ?? 0),
            ];
        });
    }

    /**
     * @return list<array{fecha: string, etiqueta: string, publicaciones: int, reacciones: int}>
     */
    private function contenidoPorDia(Emprendedor $emprendedor, int $dias): array
    {
        [$inicio, $fin] = $this->periodoUltimosDias($dias);

        $publicaciones = $emprendedor->posts()
            ->selectRaw('DATE(created_at) as fecha')
            ->selectRaw('COUNT(*) as publicaciones')
            ->whereBetween('created_at', [$inicio, $fin])
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->keyBy(fn ($fila) => (string) $fila->fecha);

        $reacciones = DB::table('emprendedor_post_reacciones')
            ->join('emprendedor_posts', 'emprendedor_post_reacciones.emprendedor_post_id', '=', 'emprendedor_posts.id')
            ->selectRaw('DATE(emprendedor_post_reacciones.created_at) as fecha')
            ->selectRaw('COUNT(*) as reacciones')
            ->where('emprendedor_posts.emprendedor_id', $emprendedor->id)
            ->whereBetween('emprendedor_post_reacciones.created_at', [$inicio, $fin])
            ->groupByRaw('DATE(emprendedor_post_reacciones.created_at)')
            ->orderByRaw('DATE(emprendedor_post_reacciones.created_at)')
            ->get()
            ->keyBy(fn ($fila) => (string) $fila->fecha);

        return $this->serieDiaria($dias, function (string $fecha) use ($publicaciones, $reacciones): array {
            return [
                'publicaciones' => (int) ($publicaciones->get($fecha)->publicaciones ?? 0),
                'reacciones' => (int) ($reacciones->get($fecha)->reacciones ?? 0),
            ];
        });
    }

    /**
     * @return list<array{fecha: string, etiqueta: string, seguidores: int}>
     */
    private function seguidoresPorDia(Emprendedor $emprendedor, int $dias): array
    {
        [$inicio, $fin] = $this->periodoUltimosDias($dias);

        $filas = DB::table('emprendedor_seguidores')
            ->selectRaw('DATE(created_at) as fecha')
            ->selectRaw('COUNT(*) as seguidores')
            ->where('emprendedor_id', $emprendedor->id)
            ->whereBetween('created_at', [$inicio, $fin])
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->keyBy(fn ($fila) => (string) $fila->fecha);

        return $this->serieDiaria($dias, function (string $fecha) use ($filas): array {
            return [
                'seguidores' => (int) ($filas->get($fecha)->seguidores ?? 0),
            ];
        });
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function periodoUltimosDias(int $dias): array
    {
        return [
            now()->subDays($dias - 1)->startOfDay(),
            now()->endOfDay(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function serieDiaria(int $dias, callable $resolver): array
    {
        $inicio = now()->subDays($dias - 1)->startOfDay();

        return collect(range(0, $dias - 1))
            ->map(function (int $offset) use ($inicio, $resolver): array {
                $fecha = $inicio->copy()->addDays($offset);
                $fechaKey = $fecha->toDateString();

                return array_merge([
                    'fecha' => $fechaKey,
                    'etiqueta' => $fecha->format('d/m'),
                ], $resolver($fechaKey));
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function ultimasDonaciones(Emprendedor $emprendedor, int $limite = 8): array
    {
        return Donacion::query()
            ->whereHas('campana', fn ($q) => $q->where('emprendedor_id', $emprendedor->id))
            ->with('campana:id,titulo')
            ->orderByDesc('created_at')
            ->limit($limite)
            ->get()
            ->map(fn (Donacion $donacion) => [
                'id' => $donacion->id,
                'monto' => (float) $donacion->monto,
                'estado_pago' => $donacion->estado_pago,
                'campana_titulo' => $donacion->campana?->titulo,
                'fecha' => $donacion->created_at?->toIso8601String(),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializarPerfil(Emprendedor $emprendedor): array
    {
        return [
            'id' => $emprendedor->id,
            'nombre_completo' => $emprendedor->nombreCompleto(),
            'estado' => $emprendedor->estado,
            'tipo_emprendimiento' => $emprendedor->tipo_emprendimiento?->etiqueta(),
            'departamento' => $emprendedor->departamento?->etiqueta(),
            'descripcion_resumen' => $this->resumirTexto($emprendedor->descripcion),
            'foto_portada' => $emprendedor->urlFotoPerfil(),
            'meta_referencia' => (float) $emprendedor->meta_monto,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializarCampana(Campana $campana): array
    {
        return [
            'id' => $campana->id,
            'titulo' => $campana->titulo,
            'meta_apoyo' => (float) $campana->meta_apoyo,
            'monto_recaudado' => (float) ($campana->monto_validado ?? $campana->monto_recaudado ?? 0),
            'fecha_fin' => $campana->fecha_fin?->toDateString(),
        ];
    }

    private function resumirTexto(?string $texto, int $maximo = 160): ?string
    {
        if ($texto === null || trim($texto) === '') {
            return null;
        }

        $limpio = trim(preg_replace('/\s+/', ' ', $texto) ?? '');

        if (mb_strlen($limpio) <= $maximo) {
            return $limpio;
        }

        return mb_substr($limpio, 0, $maximo - 1).'…';
    }
}