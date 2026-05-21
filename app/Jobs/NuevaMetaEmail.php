<?php

namespace App\Jobs;

use App\Mail\NuevaMetaPublicadaMail;
use App\Models\Campana;
use App\Models\EmprendedorSeguidor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use App\Services\TuristaNotificacionService;

class NuevaMetaEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public Campana $campana,
    ) {}

    public function handle(): void
    {
        $this->campana->loadMissing('emprendedor');

        $emprendedor = $this->campana->emprendedor;

        if (! $emprendedor) {
            return;
        }

        if (! $this->campana->estaVisibleEnPerfilTurista()) {
            return;
        }

        app(TuristaNotificacionService::class)->registrarNuevaMetaParaSeguidores($this->campana);

        EmprendedorSeguidor::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->whereNotNull('email')
            ->whereNotNull('confirmado_en')
            ->whereNotNull('token_unsub')
            ->orderBy('id')
            ->chunkById(50, function ($seguidores) use ($emprendedor): void {
                foreach ($seguidores as $seguimiento) {
                    Mail::to($seguimiento->email)->send(
                        new NuevaMetaPublicadaMail(
                            emprendedor: $emprendedor,
                            campana: $this->campana,
                            seguimiento: $seguimiento,
                        ),
                    );
                }
            });
    }
}