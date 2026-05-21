<?php

namespace App\Services;

use App\Mail\EmprendedorDonacionRecibidaMail;
use App\Models\Donacion;
use App\Models\Emprendedor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * E-10 — Correo y avisos en panel al validar una donación.
 */
class EmprendedorDonacionNotificacionService
{
    public function __construct(
        private EmprendedorNotificacionService $notificacionService,
    ) {}

    public function debeNotificarPorEmail(Emprendedor $emprendedor): bool
    {
        return (bool) $emprendedor->notificar_donaciones_email;
    }

    public function debeNotificarEnPanel(Emprendedor $emprendedor): bool
    {
        return (bool) $emprendedor->notificar_donaciones_panel;
    }

    public function notificarDonacionValidada(Donacion $donacion): void
    {
        $donacion->loadMissing([
            'campana:id,titulo,emprendedor_id',
            'campana.emprendedor.user:id,email,name',
            'visitante:id,nombre',
            'tipoPago:id,nombre',
        ]);

        $emprendedor = $donacion->campana?->emprendedor;

        if (! $emprendedor) {
            return;
        }

        if ($this->debeNotificarEnPanel($emprendedor)) {
            $this->notificacionService->registrarDonacionValidada($donacion, $emprendedor);
        }

        if ($this->debeNotificarPorEmail($emprendedor)) {
            $this->enviarCorreo($donacion, $emprendedor);
        }
    }

    private function enviarCorreo(Donacion $donacion, Emprendedor $emprendedor): void
    {
        $usuario = $emprendedor->user;

        if (! $usuario?->email) {
            return;
        }

        $nombreApoyo = trim((string) ($donacion->visitante?->nombre ?? ''));

        try {
            Mail::to($usuario->email)->send(new EmprendedorDonacionRecibidaMail(
                emprendedor: $emprendedor,
                donacion: $donacion,
                campanaTitulo: $donacion->campana?->titulo ?? 'Tu campaña',
                nombreApoyo: $nombreApoyo !== '' ? $nombreApoyo : null,
            ));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar correo de donación al emprendedor.', [
                'emprendedor_id' => $emprendedor->id,
                'donacion_id' => $donacion->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
