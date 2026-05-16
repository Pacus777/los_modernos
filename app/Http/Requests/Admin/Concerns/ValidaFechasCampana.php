<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\Campana;
use Illuminate\Support\Carbon;

trait ValidaFechasCampana
{
    protected function validarRangoFechasCampana($validator): void
    {
        $inicio = $this->input('fecha_inicio');
        $fin = $this->input('fecha_fin');

        if ($inicio && $fin && Carbon::parse((string) $fin)->lt(Carbon::parse((string) $inicio))) {
            $validator->errors()->add(
                'fecha_fin',
                'La fecha de fin debe ser igual o posterior a la de inicio.'
            );
        }
    }

    protected function validarCampanaActivaVigente($validator): void
    {
        if ($this->input('estado') !== Campana::ESTADO_ACTIVA) {
            return;
        }

        if (! Campana::fechasPermitenVisibilidadPublica(
            $this->input('fecha_inicio'),
            $this->input('fecha_fin'),
        )) {
            $validator->errors()->add(
                'estado',
                'No podés dejar la campaña activa si aún no comenzó o ya venció según las fechas indicadas.'
            );
        }
    }

    protected function validarFechaInicioNoRetroactivaEnEdicion($validator, Campana $campana): void
    {
        $inicio = $this->input('fecha_inicio');

        if (! $inicio) {
            return;
        }

        $fechaEnviada = Carbon::parse((string) $inicio)->startOfDay();
        $hoy = now()->startOfDay();
        $fechaOriginal = $campana->fecha_inicio?->startOfDay();

        if ($fechaEnviada->lt($hoy) && $fechaOriginal?->toDateString() !== $fechaEnviada->toDateString()) {
            $validator->errors()->add(
                'fecha_inicio',
                'La fecha de inicio no puede ser anterior a hoy.'
            );
        }
    }
}
