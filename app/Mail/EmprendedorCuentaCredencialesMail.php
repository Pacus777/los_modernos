<?php

namespace App\Mail;

use App\Models\Emprendedor;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmprendedorCuentaCredencialesMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $usuario,
        public Emprendedor $emprendedor,
        public string $passwordPlano,
        public bool $esReenvio = false,
    ) {
    }

    public function envelope(): Envelope
    {
        $app = config('app.name', 'WAYNA');

        $asunto = $this->esReenvio
            ? "{$app} — Nueva contraseña de acceso"
            : "{$app} — Tu cuenta de emprendedor";

        return new Envelope(
            subject: $asunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.emprendedor-cuenta-credenciales',
            with: [
                'loginUrl' => route('login'),
                'esReenvio' => $this->esReenvio,
                'usuario' => $this->usuario,
                'emprendedor' => $this->emprendedor,
                'passwordPlano' => $this->passwordPlano,
            ],
        );
    }
}
