<?php

namespace App\Mail;

use App\Models\Emprendedor;
use App\Models\EmprendedorSeguidor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmarSeguimientoEmprendedorMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Emprendedor $emprendedor,
        public EmprendedorSeguidor $seguimiento,
    ) {}

    public function envelope(): Envelope
    {
        $app = config('app.name', 'WAYNA');

        return new Envelope(
            subject: "{$app} — Confirmá que querés seguir a {$this->emprendedor->nombreCompleto()}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.confirmar-seguimiento-emprendedor',
            with: [
                'emprendedor' => $this->emprendedor,
                'confirmarUrl' => route('turista.seguir.confirmar', [
                    'token' => $this->seguimiento->token_confirm,
                ]),
                'bajaUrl' => route('turista.seguir.baja', [
                    'token' => $this->seguimiento->token_unsub,
                ]),
            ],
        );
    }
}
