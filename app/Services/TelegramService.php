<?php

namespace App\Services;

use App\Models\Donacion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /*
    |--------------------------------------------------------------------------
    | TelegramService
    |--------------------------------------------------------------------------
    |
    | Servicio encargado de enviar notificaciones de WAYNA a Telegram.
    |
    | En el MVP se usará principalmente para avisar al admin/cajero cuando
    | exista una donación en efectivo pendiente de confirmación física.
    |
    */

    /**
     * Envía una notificación cuando se registra una donación en efectivo.
     *
     * Esta notificación NO valida la donación.
     * Solo avisa que hay un pago físico pendiente por confirmar.
     */
    public function notificarDonacionEfectivoPendiente(Donacion $donacion): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Verificar si Telegram está habilitado
        |--------------------------------------------------------------------------
        |
        | En desarrollo puede estar desactivado para que el sistema funcione
        | aunque el equipo todavía no tenga bot o grupo configurado.
        |
        */

        if (!config('services.telegram.enabled')) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Verificar credenciales mínimas
        |--------------------------------------------------------------------------
        |
        | Si falta el token o el chat_id, no intentamos enviar nada.
        | Registramos el problema en logs para poder depurarlo.
        |
        */

        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (!$botToken || !$chatId) {
            Log::warning('Telegram no configurado: falta TELEGRAM_BOT_TOKEN o TELEGRAM_CHAT_ID.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Cargar relaciones necesarias
        |--------------------------------------------------------------------------
        |
        | La donación NO tiene relación directa con emprendedor.
        |
        | El camino correcto es:
        |
        | Donacion -> Campana -> Emprendedor
        |
        */

        $donacion->loadMissing([
            'campana.emprendedor',
            'tipoPago',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 4. Obtener datos para el mensaje
        |--------------------------------------------------------------------------
        |
        | Si por algún motivo no existe campaña o emprendedor, usamos un texto
        | por defecto para que el sistema no se rompa.
        |
        */

        $nombreEmprendedor = $donacion->campana?->emprendedor
            ? $donacion->campana->emprendedor->nombreCompleto()
            : 'Emprendedor no identificado';

        $monto = number_format((float) $donacion->monto, 2);

        $fechaHora = now()
            ->timezone('America/La_Paz')
            ->format('d/m/Y H:i');

        /*
        |--------------------------------------------------------------------------
        | 5. Construir mensaje
        |--------------------------------------------------------------------------
        |
        | Mensaje breve y útil para el admin/cajero.
        |
        */

        $mensaje = <<<TEXT
🔔 Nueva donación en efectivo pendiente

Emprendedor: {$nombreEmprendedor}
Monto: Bs. {$monto}
Fecha y hora: {$fechaHora}

Revisar y confirmar desde el panel de cajero.
TEXT;

        /*
        |--------------------------------------------------------------------------
        | 6. Enviar mensaje a Telegram
        |--------------------------------------------------------------------------
        |
        | Si Telegram falla, NO rompemos el flujo de donación.
        | La donación ya quedó registrada como pendiente.
        |
        */

        try {
            $response = Http::timeout(10)->post(
                "https://api.telegram.org/bot{$botToken}/sendMessage",
                [
                    'chat_id' => $chatId,
                    'text' => $mensaje,
                ]
            );

            if ($response->failed()) {
                Log::warning('Telegram respondió con error.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error al enviar notificación por Telegram.', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Alertas de respaldo automático (S3-08).
     */
    public function notificarRespaldoExitoso(string $nombreRespaldo, string $rutaDestino): void
    {
        if (! config('services.telegram.backup_alerts')) {
            return;
        }

        $fecha = now()->timezone('America/La_Paz')->format('d/m/Y H:i');

        $this->enviarMensaje(<<<TEXT
✅ Respaldo WAYNA completado

Nombre: {$nombreRespaldo}
Destino: {$rutaDestino}
Fecha: {$fecha}
TEXT);
    }

    public function notificarRespaldoFallido(string $nombreRespaldo, string $error): void
    {
        if (! config('services.telegram.backup_alerts')) {
            return;
        }

        $fecha = now()->timezone('America/La_Paz')->format('d/m/Y H:i');
        $error = mb_substr($error, 0, 500);

        $this->enviarMensaje(<<<TEXT
❌ Falló el respaldo WAYNA

Nombre: {$nombreRespaldo}
Fecha: {$fecha}
Error: {$error}
TEXT);
    }

    /**
     * Envía texto plano al chat configurado (donaciones, backups, etc.).
     */
    public function enviarMensaje(string $mensaje): void
    {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (! $botToken || ! $chatId) {
            Log::warning('Telegram no configurado: falta TELEGRAM_BOT_TOKEN o TELEGRAM_CHAT_ID.');

            return;
        }

        try {
            $response = Http::timeout(10)->post(
                "https://api.telegram.org/bot{$botToken}/sendMessage",
                [
                    'chat_id' => $chatId,
                    'text' => $mensaje,
                ],
            );

            if ($response->failed()) {
                Log::warning('Telegram respondió con error.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error al enviar mensaje por Telegram.', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}