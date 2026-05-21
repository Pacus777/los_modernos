<?php

namespace App\Console\Commands;

use App\Support\CorreoGmailEmprendedor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ProbarCorreoWaynaCommand extends Command
{
    protected $signature = 'wayna:probar-correo {destino : Correo de prueba}';

    protected $description = 'Envía un correo de prueba (verifica Mailpit o SMTP en .env)';

    public function handle(): int
    {
        $destino = strtolower($this->argument('destino'));

        if (CorreoGmailEmprendedor::exigido() && ! CorreoGmailEmprendedor::esGmail($destino)) {
            $this->error('WAYNA exige destino @gmail.com para credenciales de emprendedor.');

            return self::FAILURE;
        }

        if (! filled(config('mail.mailers.smtp.username')) || ! filled(config('mail.mailers.smtp.password'))) {
            $this->error('Completá MAIL_USERNAME y MAIL_PASSWORD (contraseña de aplicación Gmail) en .env');

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
            $this->line('Asegurate de que Mailpit esté iniciado en Laragon (Menu → Mailpit).');

            return self::FAILURE;
        }
    }
}
