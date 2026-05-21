<?php

namespace App\Http\Controllers\Turista;

use App\Http\Controllers\Controller;
use App\Http\Requests\Turista\StoreDonacionRequest;
use App\Services\DonacionService;
use App\Services\VisitanteService;
use Illuminate\Http\RedirectResponse;

class DonacionController extends Controller
{
    /**
     * El Controller solo valida la solicitud y llama al servicio.
     * La lógica principal queda en DonacionService.
     */
    public function store(
        StoreDonacionRequest $request,
        DonacionService $donacionService,
        VisitanteService $visitanteService,
    ): RedirectResponse {
        $validated = $request->validated();

        $validated['visitante_id'] = $visitanteService->resolverParaDonacion(
            $request,
            $validated['visitante_nombre'] ?? null,
        );

        unset($validated['visitante_nombre']);

        $resultado = $donacionService->registrar($validated);

        return redirect()
            ->route('turista.donaciones.exitosa', [
                'donacion' => $resultado['donacion']->id,
            ])
            ->with('success', __('donacion.registered_success'));
    }
}