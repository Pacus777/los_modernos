<?php

namespace App\Mail;

use App\Models\Campana;
use App\Models\Emprendedor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MetaCumplidaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Emprendedor $emprendedor,
        public Campana $campana,
    ) {}

    public function envelope(): Envelope
    {
        $app = config('app.name', 'WAYNA');

        return new Envelope(
            subject: "{$app} — Tu meta fue cumplida",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.meta-cumplida',
            with: [
                'emprendedor' => $this->emprendedor,
                'campana' => $this->campana,
                'panelUrl' => route('emprendedor.mis-metas.index'),
                'donacionesUrl' => route('emprendedor.donaciones.index'),
            ],
        );
    }
}
