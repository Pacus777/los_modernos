<?php

namespace App\Services;

use App\Models\Campana;
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
}
