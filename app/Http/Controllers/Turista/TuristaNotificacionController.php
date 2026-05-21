<?php

namespace App\Http\Controllers\Turista;

use App\Http\Controllers\Controller;
use App\Services\TuristaNotificacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TuristaNotificacionController extends Controller
{
    public function index(Request $request, TuristaNotificacionService $service): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Si el usuario no es turista (tiene rol de admin, cajero o emprendedor) redirige
        if ($user->tieneAlgunoDeEstosRoles(['admin', 'cajero', 'emprendedor'])) {
            return redirect()
                ->route('turista.explorar')
                ->with('error', 'Esta sección solo está disponible para turistas.');
        }

        return Inertia::render('Turista/Notificaciones/Index', [
            'notificaciones' => $service->listar($user),
            'no_leidas' => $service->contarNoLeidas($user),
        ]);
    }

    public function marcarLeida(
        Request $request,
        int $notificacion,
        TuristaNotificacionService $service,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $service->marcarLeida($user, $notificacion);

        return back();
    }

    public function marcarTodasLeidas(
        Request $request,
        TuristaNotificacionService $service,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $service->marcarTodasLeidas($user);

        return back()->with('success', 'Todas las notificaciones se marcaron como leídas.');
    }
}
