<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ProbarCorreoWaynaCommand extends Command
{
    protected $signature = 'wayna:probar-correo {destino : Correo de prueba}';

    protected $description = 'Envía un correo de prueba (verifica SMTP en .env)';

    public function handle(): int
    {
        $destino = strtolower(trim((string) $this->argument('destino')));

        if (! filter_var($destino, FILTER_VALIDATE_EMAIL)) {
            $this->error('Indicá un correo válido (debe incluir @ y un dominio, ej. usuario@wayna.com).');

            return self::FAILURE;
        }

        if (! filled(config('mail.mailers.smtp.username')) || ! filled(config('mail.mailers.smtp.password'))) {
            $this->error('Completá MAIL_USERNAME y MAIL_PASSWORD en .env');

            return self::FAILURE;
        }

        $this->info('Mailer: '.config('mail.default'));
        $this->info('Host: '.config('mail.mailers.smtp.host').':'.config('mail.mailers.smtp.port'));

        try {
            Mail::raw(
                'Correo de prueba WAYNA. Si ves esto, el envío SMTP funciona.',
                function ($message) use ($destino): void {
                    $message->to($destino)->subject('Prueba correo WAYNA');
                },
            );

            $this->newLine();
            $this->info('Correo enviado correctamente.');
            $this->line('Revisá la bandeja de entrada de '.$destino.' (y spam).');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('No se pudo enviar: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
