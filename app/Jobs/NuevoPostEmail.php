<?php

namespace App\Jobs;

use App\Enums\EmprendedorPostEstado;
use App\Mail\NuevoPostPublicadoMail;
use App\Models\EmprendedorPost;
use App\Models\EmprendedorSeguidor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use App\Services\TuristaNotificacionService;

class NuevoPostEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public EmprendedorPost $post,
    ) {}

    public function handle(): void
    {
        $this->post->loadMissing('emprendedor');

        if ($this->post->estado !== EmprendedorPostEstado::Publicado) {
            return;
        }

        if ($this->post->publicado_en === null) {
            return;
        }

        $emprendedor = $this->post->emprendedor;

        if (! $emprendedor) {
            return;
        }

        app(TuristaNotificacionService::class)->registrarNuevoPostParaSeguidores($this->post);

        EmprendedorSeguidor::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->whereNotNull('email')
            ->whereNotNull('confirmado_en')
            ->whereNotNull('token_unsub')
            ->orderBy('id')
            ->chunkById(50, function ($seguidores) use ($emprendedor): void {
                foreach ($seguidores as $seguimiento) {
                    Mail::to($seguimiento->email)->send(
                        new NuevoPostPublicadoMail(
                            emprendedor: $emprendedor,
                            post: $this->post,
                            seguimiento: $seguimiento,
                        ),
                    );
                }
            });
    }
}