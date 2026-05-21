<?php

namespace App\Services;

use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;

class EmprendedorMetaService
{
    /**
     * @param  array{titulo: string, meta_apoyo: numeric, fecha_inicio: string, fecha_fin: string}  $datos
     */
    public function crear(Emprendedor $emprendedor, array $datos): Campana
    {
        return Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => $datos['titulo'],
            'meta_apoyo' => $datos['meta_apoyo'],
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin'],
            'estado' => Campana::ESTADO_ACTIVA,
            'monto_recaudado' => 0,
        ]);
    }

    /**
     * @param  array{titulo: string, meta_apoyo: numeric, fecha_inicio: string, fecha_fin: string}  $datos
     */
    public function actualizar(Campana $campana, array $datos): Campana
    {
        $campana->update([
            'titulo' => $datos['titulo'],
            'meta_apoyo' => $datos['meta_apoyo'],
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin'],
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        return $campana->fresh();
    }

    public function cerrar(Campana $campana): Campana
    {
        $campana->update([
            'estado' => Campana::ESTADO_FINALIZADA,
        ]);

        return $campana->fresh();
    }

    public function campanaActiva(Emprendedor $emprendedor): ?Campana
    {
        return $emprendedor->campanas()
            ->where('estado', Campana::ESTADO_ACTIVA)
            ->orderByDesc('fecha_inicio')
            ->first();
    }

    public function tieneCampanaActiva(Emprendedor $emprendedor): bool
    {
        return $this->campanaActiva($emprendedor) !== null;
    }

    /**
     * E-08 — Listado y resumen para /emprendedor/mis-metas.
     *
     * @return array{
     *     campanas: list<array<string, mixed>>,
     *     puede_crear: bool,
     *     campana_activa_id: int|null
     * }
     */
    public function datosGestionMetas(Emprendedor $emprendedor): array
    {
        $campanas = $emprendedor->campanas()
            ->withSum([
                'donaciones as monto_validado' => function ($query) {
                    $query->where('estado_pago', Donacion::ESTADO_VALIDADO);
                },
            ], 'monto')
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->get();

        $activaId = $this->campanaActiva($emprendedor)?->id;

        $filas = $campanas->map(function (Campana $campana) use ($activaId) {
            $meta = (float) $campana->meta_apoyo;
            $monto = (float) ($campana->monto_validado ?? 0);
            $porcentaje = $meta > 0
                ? min(round(($monto / $meta) * 100, 2), 100.0)
                : 0.0;

            return [
                'id' => $campana->id,
                'titulo' => $campana->titulo,
                'estado' => $campana->estado,
                'meta_apoyo' => $meta,
                'monto_recaudado' => $monto,
                'porcentaje' => $porcentaje,
                'fecha_inicio' => $campana->fecha_inicio?->toDateString(),
                'fecha_fin' => $campana->fecha_fin?->toDateString(),
                'es_activa' => $campana->id === $activaId,
                'visible_en_perfil' => $campana->estaVisibleEnPerfilTurista(),
            ];
        })->values()->all();

        return [
            'campanas' => $filas,
            'puede_crear' => $activaId === null,
            'campana_activa_id' => $activaId,
        ];
    }
}
