<?php

namespace App\Mail;

use App\Models\Donacion;
use App\Models\Emprendedor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmprendedorDonacionRecibidaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Emprendedor $emprendedor,
        public Donacion $donacion,
        public string $campanaTitulo,
        public ?string $nombreApoyo,
    ) {}

    public function envelope(): Envelope
    {
        $app = config('app.name', 'WAYNA');
        $monto = number_format((float) $this->donacion->monto, 2, ',', '.');

        return new Envelope(
            subject: "{$app} — Nuevo aporte de Bs {$monto} en tu campaña",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.emprendedor-donacion-recibida',
            with: [
                'emprendedor' => $this->emprendedor,
                'donacion' => $this->donacion,
                'campanaTitulo' => $this->campanaTitulo,
                'nombreApoyo' => $this->nombreApoyo,
                'panelUrl' => route('emprendedor.donaciones.index'),
                'preferenciasUrl' => route('emprendedor.preferencias.edit'),
            ],
        );
    }
}
