<?php

namespace App\Mail;

use App\Models\Emprendedor;
use App\Models\EmprendedorPost;
use App\Models\EmprendedorSeguidor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevoPostPublicadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Emprendedor $emprendedor,
        public EmprendedorPost $post,
        public EmprendedorSeguidor $seguimiento,
    ) {}

    public function envelope(): Envelope
    {
        $app = config('app.name', 'WAYNA');

        return new Envelope(
            subject: "{$app} — Nueva publicación de {$this->emprendedor->nombreCompleto()}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.nuevo-post-publicado',
            with: [
                'emprendedor' => $this->emprendedor,
                'post' => $this->post,
                'perfilUrl' => route('turista.emprendedor.show', $this->emprendedor->id),
                'bajaUrl' => route('turista.seguir.baja', [
                    'token' => $this->seguimiento->token_unsub,
                ]),
            ],
        );
    }
}
