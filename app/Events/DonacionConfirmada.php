<?php

namespace App\Events;

use App\Models\Donacion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DonacionConfirmada
{
    use Dispatchable, SerializesModels;

    public Donacion $donacion;

    public function __construct(Donacion $donacion)
    {
        $this->donacion = $donacion;
    }
}
