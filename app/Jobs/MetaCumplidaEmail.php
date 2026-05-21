<?php

namespace App\Jobs;

use App\Mail\MetaCumplidaMail;
use App\Models\Campana;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MetaCumplidaEmail implements ShouldQueue
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

        $email = DB::table('users')
            ->where('id', $emprendedor->user_id)
            ->value('email');

        if (! filled($email)) {
            return;
        }

        Mail::to($email)->send(
            new MetaCumplidaMail(
                emprendedor: $emprendedor,
                campana: $this->campana,
            ),
        );
    }
}