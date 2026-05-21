<?php

namespace App\Events;

use App\Models\Campana;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * La campaña alcanzó su meta de apoyo (S4-02).
 */
class CampanaMetaAlcanzada
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Campana $campana,
    ) {}
}
