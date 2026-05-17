<?php

namespace App\Events;

use App\Models\Donacion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class DonacionCreada implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public Donacion $donacion;

    public function __construct(Donacion $donacion)
    {
        $this->donacion = $donacion;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('admin-notifications');
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->donacion->id,
            'monto' => $this->donacion->monto,
            'campana' => $this->donacion->campana->titulo ?? '',
            'usuario' => $this->donacion->visitante?->nombre ?? 'Anónimo',
        ];
    }
}