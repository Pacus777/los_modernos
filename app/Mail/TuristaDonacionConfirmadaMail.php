<?php

namespace App\Mail;

use App\Models\Donacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TuristaDonacionConfirmadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Donacion $donacion,
        public string $campanaTitulo,
        public string $emprendedorNombre,
        public ?string $nombreApoyo,
        public string $exitosaUrl,
    ) {}

    public function envelope(): Envelope
    {
        $app = config('app.name', 'WAYNA');

        return new Envelope(
            subject: "{$app} — Tu aporte fue confirmado",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.turista-donacion-confirmada',
            with: [
                'donacion' => $this->donacion,
                'campanaTitulo' => $this->campanaTitulo,
                'emprendedorNombre' => $this->emprendedorNombre,
                'nombreApoyo' => $this->nombreApoyo,
                'exitosaUrl' => $this->exitosaUrl,
            ],
        );
    }
}
