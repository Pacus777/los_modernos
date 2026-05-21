<?php

namespace App\Observers;

use App\Models\Campana;
use App\Models\Donacion;


/**
 * Único responsable de actualizar campanas.monto_recaudado (T-A19).
 *
 * Los servicios y controladores solo cambian estado_pago; este observer
 * incrementa o decrementa el monto al entrar o salir de "validado".
 */
class DonacionObserver
{
    public function created(Donacion $donacion): void
    {
        if ($donacion->estado_pago === Donacion::ESTADO_VALIDADO) {
            $this->incrementarRecaudacionCampana($donacion);
            \App\Events\DonacionConfirmada::dispatch($donacion);
        }
    }

    public function updated(Donacion $donacion): void
    {
        if (! $donacion->wasChanged('estado_pago')) {
            return;
        }

        $anterior = $donacion->getOriginal('estado_pago');
        $actual = $donacion->estado_pago;

        if ($anterior === Donacion::ESTADO_VALIDADO && $actual !== Donacion::ESTADO_VALIDADO) {
            $this->decrementarRecaudacionCampana($donacion);
        } elseif ($anterior !== Donacion::ESTADO_VALIDADO && $actual === Donacion::ESTADO_VALIDADO) {
            $this->incrementarRecaudacionCampana($donacion);
            \App\Events\DonacionConfirmada::dispatch($donacion);
        }
    }

    private function incrementarRecaudacionCampana(Donacion $donacion): void
    {
        if (! $donacion->campana_id) {
            return;
        }

        Campana::query()
            ->whereKey($donacion->campana_id)
            ->increment('monto_recaudado', $donacion->monto);
    }

    private function decrementarRecaudacionCampana(Donacion $donacion): void
    {
        if (! $donacion->campana_id) {
            return;
        }

        $campana = Campana::query()->find($donacion->campana_id);
        if (! $campana) {
            return;
        }

        $monto = (float) $donacion->monto;
        $recaudado = (float) $campana->monto_recaudado;
        $delta = min($monto, max($recaudado, 0));

        if ($delta > 0) {
            $campana->decrement('monto_recaudado', $delta);
        }
    }
}
