<?php

namespace App\Services;

use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
/**
 * E-05 — Datos agregados para el panel del emprendedor autenticado.
 */
class EmprendedorDashboardService
{
    /**
     * @return array{
     *     perfil: array<string, mixed>,
     *     campana_activa: array<string, mixed>|null,
     *     progreso: array{meta: float, monto_recaudado: float, monto_faltante: float, porcentaje: float},
     *     estadisticas: array<string, int|float>,
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

        return [
            'donaciones_validadas_total' => (float) ($validadas->sum('monto') ?? 0),
            'donaciones_validadas_cantidad' => $validadas->count(),
            'donaciones_pendientes_cantidad' => $pendientes->count(),
            'seguidores' => $emprendedor->seguidoresVisitantes()->count(),
            'puntos' => $emprendedor->puntos()->count(),
            'publicaciones' => $emprendedor->posts()->count(),
        ];
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
