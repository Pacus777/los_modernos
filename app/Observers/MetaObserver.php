<?php

namespace App\Observers;

use App\Events\CampanaMetaAlcanzada;
use App\Models\Campana;

/**
 * Detecta cuando una campaña alcanza su meta_apoyo (S4-02).
 */
class MetaObserver
{
    public function updated(Campana $campana): void
    {
        if (! $campana->wasChanged('monto_recaudado')) {
            return;
        }

        $meta = (float) $campana->meta_apoyo;

        if ($meta <= 0) {
            return;
        }

        $recaudadoAntes = (float) $campana->getOriginal('monto_recaudado');
        $recaudadoAhora = (float) $campana->monto_recaudado;

        if ($recaudadoAntes < $meta && $recaudadoAhora >= $meta) {
            CampanaMetaAlcanzada::dispatch($campana);
        }
    }
}
