<?php

namespace App\Http\Controllers\Emprendedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $emprendedor = $user->emprendedor;

        return Inertia::render('Emprendedor/Dashboard', [
            'emprendedor' => $emprendedor ? [
                'id' => $emprendedor->id,
                'nombre_completo' => $emprendedor->nombreCompleto(),
                'estado' => $emprendedor->estado,
                'meta_monto' => (float) $emprendedor->meta_monto,
                'foto_portada' => $emprendedor->fotografia
                    ? '/storage/'.$emprendedor->fotografia
                    : null,
                'perfil_publico_url' => route('turista.emprendedor.show', $emprendedor),
            ] : null,
            'usuario' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
