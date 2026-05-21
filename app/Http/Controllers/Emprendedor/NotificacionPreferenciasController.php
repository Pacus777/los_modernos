<?php

namespace App\Http\Controllers\Emprendedor;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Emprendedor\UpdatePreferenciasNotificacionesRequest;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificacionPreferenciasController extends Controller
{
    public function edit(Request $request): Response|RedirectResponse
    {
        $emprendedor = $request->user()?->emprendedor;

        if (! $emprendedor) {
            return redirect()
                ->route('emprendedor.dashboard')
                ->with('error', 'Tu cuenta no está vinculada a un perfil de emprendedor.');
        }

        return Inertia::render('Emprendedor/Preferencias/Index', [
            'notificar_donaciones_email' => (bool) $emprendedor->notificar_donaciones_email,
            'notificar_donaciones_panel' => (bool) $emprendedor->notificar_donaciones_panel,
            'email_cuenta' => $request->user()->email,
        ]);
    }

    public function update(
        UpdatePreferenciasNotificacionesRequest $request,
        AuditLogService $auditLogService,
    ): RedirectResponse {
        $emprendedor = $request->user()->emprendedor;

        $emailAnterior = (bool) $emprendedor->notificar_donaciones_email;
        $panelAnterior = (bool) $emprendedor->notificar_donaciones_panel;
        $emailNuevo = $request->boolean('notificar_donaciones_email');
        $panelNuevo = $request->boolean('notificar_donaciones_panel');

        $emprendedor->update([
            'notificar_donaciones_email' => $emailNuevo,
            'notificar_donaciones_panel' => $panelNuevo,
        ]);

        if ($emailAnterior !== $emailNuevo || $panelAnterior !== $panelNuevo) {
            $auditLogService->registrar(
                AuditAction::EmprendedorNotificationPreferencesUpdated,
                subject: $emprendedor,
                actor: $request->user(),
                metadata: [
                    'notificar_donaciones_email' => $emailNuevo,
                    'notificar_donaciones_panel' => $panelNuevo,
                ],
                request: $request,
            );
        }

        return redirect()
            ->route('emprendedor.preferencias.edit')
            ->with('success', 'Tus preferencias de notificación se guardaron.');
    }
}
