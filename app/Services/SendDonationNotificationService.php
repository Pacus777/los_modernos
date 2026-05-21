<?php

namespace App\Services;

use App\Mail\EmprendedorDonacionRecibidaMail;
use App\Mail\TuristaDonacionConfirmadaMail;
use App\Models\Donacion;
use App\Models\Emprendedor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDonationNotificationService
{
    public function __construct(
        protected EmprendedorNotificacionService $notificacionService,
        protected TelegramService $telegramService,
    ) {
    }

    /**
     * Procesa todas las notificaciones por canal para una donación validada.
     */
    public function notificar(Donacion $donacion): void
    {
        $donacion->loadMissing([
            'campana.emprendedor.user',
            'visitante',
            'tipoPago',
        ]);

        $emprendedor = $donacion->campana?->emprendedor;

        // 1. Email al Emprendedor
        if ($emprendedor && $this->debeNotificarEmailEmprendedor($donacion, $emprendedor)) {
            $enviado = $this->enviarEmailEmprendedor($donacion, $emprendedor);
            if ($enviado) {
                $this->marcarCanal($donacion, 'emprendedor_email_enviado_en', $enviado);
            }
        }

        // 2. In-App al Emprendedor (Panel)
        if ($emprendedor && $this->debeNotificarPanelEmprendedor($donacion, $emprendedor)) {
            $registrado = $this->registrarPanelEmprendedor($donacion, $emprendedor);
            if ($registrado) {
                $this->marcarCanal($donacion, 'emprendedor_panel_registrado_en', $registrado);
            }
        }

        // 3. Email al Turista (Donante)
        if ($this->debeNotificarEmailTurista($donacion)) {
            $enviado = $this->enviarEmailTurista($donacion);
            if ($enviado) {
                $this->marcarCanal($donacion, 'turista_email_enviado_en', $enviado);
            }
        }

        // 4. Alerta Telegram Interna
        if ($this->debeNotificarTelegram($donacion)) {
            $enviado = $this->enviarTelegram($donacion);
            if ($enviado) {
                $this->marcarCanal($donacion, 'telegram_enviado_en', $enviado);
            }
        }
    }

    /**
     * Resuelve el correo electrónico del turista revisando metadata_pago.
     */
    public function resolverEmailTurista(Donacion $donacion): ?string
    {
        $metadata = $donacion->metadata_pago ?? [];

        $emailKeys = [
            'email',
            'email_cliente',
            'turista_email',
        ];

        foreach ($emailKeys as $key) {
            if (!empty($metadata[$key]) && filter_var($metadata[$key], FILTER_VALIDATE_EMAIL)) {
                return trim((string) $metadata[$key]);
            }
        }

        if (!empty($metadata['donante']['email']) && filter_var($metadata['donante']['email'], FILTER_VALIDATE_EMAIL)) {
            return trim((string) $metadata['donante']['email']);
        }

        if (!empty($metadata['customer']['email']) && filter_var($metadata['customer']['email'], FILTER_VALIDATE_EMAIL)) {
            return trim((string) $metadata['customer']['email']);
        }

        if (!empty($metadata['libelula_webhook_payload']['email_cliente']) && filter_var($metadata['libelula_webhook_payload']['email_cliente'], FILTER_VALIDATE_EMAIL)) {
            return trim((string) $metadata['libelula_webhook_payload']['email_cliente']);
        }

        return null;
    }

    private function debeNotificarEmailEmprendedor(Donacion $donacion, Emprendedor $emprendedor): bool
    {
        if (!$emprendedor->notificar_donaciones_email) {
            return false;
        }

        if ($this->canalYaProcesado($donacion, 'emprendedor_email_enviado_en')) {
            return false;
        }

        return !empty($emprendedor->user?->email);
    }

    private function debeNotificarPanelEmprendedor(Donacion $donacion, Emprendedor $emprendedor): bool
    {
        if (!$emprendedor->notificar_donaciones_panel) {
            return false;
        }

        return !$this->canalYaProcesado($donacion, 'emprendedor_panel_registrado_en');
    }

    private function debeNotificarEmailTurista(Donacion $donacion): bool
    {
        if ($this->canalYaProcesado($donacion, 'turista_email_enviado_en')) {
            return false;
        }

        return !empty($this->resolverEmailTurista($donacion));
    }

    private function debeNotificarTelegram(Donacion $donacion): bool
    {
        if (!config('services.telegram.enabled')) {
            return false;
        }

        return !$this->canalYaProcesado($donacion, 'telegram_enviado_en');
    }

    private function canalYaProcesado(Donacion $donacion, string $canalKey): bool
    {
        $metadata = $donacion->metadata_pago ?? [];
        $notificaciones = $metadata['notificaciones'] ?? [];

        return !empty($notificaciones[$canalKey]);
    }

    private function marcarCanal(Donacion $donacion, string $canalKey, string $timestamp): void
    {
        $donacion->refresh();
        $metadata = $donacion->metadata_pago ?? [];
        $notificaciones = $metadata['notificaciones'] ?? [];
        
        $notificaciones[$canalKey] = $timestamp;
        $metadata['notificaciones'] = $notificaciones;

        $donacion->updateQuietly([
            'metadata_pago' => $metadata,
        ]);
    }

    private function enviarEmailEmprendedor(Donacion $donacion, Emprendedor $emprendedor): ?string
    {
        $usuario = $emprendedor->user;
        $nombreApoyo = trim((string) ($donacion->visitante?->nombre ?? ''));

        try {
            Mail::to($usuario->email)->send(new EmprendedorDonacionRecibidaMail(
                emprendedor: $emprendedor,
                donacion: $donacion,
                campanaTitulo: $donacion->campana?->titulo ?? 'Tu campaña',
                nombreApoyo: $nombreApoyo !== '' ? $nombreApoyo : null,
            ));

            return now()->toIso8601String();
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar correo de donación al emprendedor.', [
                'emprendedor_id' => $emprendedor->id,
                'donacion_id' => $donacion->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function registrarPanelEmprendedor(Donacion $donacion, Emprendedor $emprendedor): ?string
    {
        try {
            $this->notificacionService->registrarDonacionValidada($donacion, $emprendedor);

            return now()->toIso8601String();
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar notificación in-app para el emprendedor.', [
                'emprendedor_id' => $emprendedor->id,
                'donacion_id' => $donacion->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function enviarEmailTurista(Donacion $donacion): ?string
    {
        $email = $this->resolverEmailTurista($donacion);
        if (!$email) {
            return null;
        }

        $campanaTitulo = $donacion->campana?->titulo ?? 'Campaña';
        $emprendedorNombre = $donacion->campana?->emprendedor
            ? $donacion->campana->emprendedor->nombreCompleto()
            : 'Emprendedor';
        
        $nombreApoyo = trim((string) ($donacion->visitante?->nombre ?? ''));
        $exitosaUrl = route('turista.donaciones.exitosa', $donacion->id);

        try {
            Mail::to($email)->send(new TuristaDonacionConfirmadaMail(
                donacion: $donacion,
                campanaTitulo: $campanaTitulo,
                emprendedorNombre: $emprendedorNombre,
                nombreApoyo: $nombreApoyo !== '' ? $nombreApoyo : null,
                exitosaUrl: $exitosaUrl,
            ));

            return now()->toIso8601String();
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar correo de donación al turista.', [
                'email_turista' => $email,
                'donacion_id' => $donacion->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function enviarTelegram(Donacion $donacion): ?string
    {
        $emprendedorNombre = $donacion->campana?->emprendedor
            ? $donacion->campana->emprendedor->nombreCompleto()
            : 'Emprendedor no identificado';

        $monto = number_format((float) $donacion->monto, 2);
        $campana = $donacion->campana?->titulo ?? 'Campaña';
        $metodo = strtoupper((string) $donacion->metodo);
        $referencia = $donacion->referencia_pago ?? 'Ninguna';

        $mensaje = "✅ Donación validada en WAYNA\n" .
                   "Monto: Bs {$monto}\n" .
                   "Campaña: {$campana}\n" .
                   "Emprendedor: {$emprendedorNombre}\n" .
                   "Método: {$metodo}\n" .
                   "Referencia: {$referencia}\n" .
                   "ID: #{$donacion->id}";

        try {
            $this->telegramService->enviarMensaje($mensaje);

            return now()->toIso8601String();
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar alerta de Telegram.', [
                'donacion_id' => $donacion->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
