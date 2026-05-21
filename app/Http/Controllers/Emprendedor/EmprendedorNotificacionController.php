<?php

namespace App\Http\Controllers\Emprendedor;

use App\Http\Controllers\Controller;
use App\Services\EmprendedorNotificacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmprendedorNotificacionController extends Controller
{
    public function index(Request $request, EmprendedorNotificacionService $service): Response|RedirectResponse
    {
        $emprendedor = $request->user()?->emprendedor;

        if (! $emprendedor) {
            return redirect()
                ->route('emprendedor.dashboard')
                ->with('error', 'Tu cuenta no está vinculada a un perfil de emprendedor.');
        }

        return Inertia::render('Emprendedor/Notificaciones/Index', [
            'notificaciones' => $service->listar($emprendedor),
            'no_leidas' => $service->contarNoLeidas($emprendedor),
        ]);
    }

    public function marcarLeida(
        Request $request,
        int $notificacion,
        EmprendedorNotificacionService $service,
    ): RedirectResponse {
        $emprendedor = $request->user()->emprendedor;

        $service->marcarLeida($emprendedor, $notificacion);

        return back();
    }

    public function marcarTodasLeidas(
        Request $request,
        EmprendedorNotificacionService $service,
    ): RedirectResponse {
        $emprendedor = $request->user()->emprendedor;

        $service->marcarTodasLeidas($emprendedor);

        return back()->with('success', 'Todas las notificaciones se marcaron como leídas.');
    }
}
