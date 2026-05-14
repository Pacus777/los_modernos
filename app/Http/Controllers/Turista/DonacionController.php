<?php

namespace App\Http\Controllers\Turista;

use App\Http\Controllers\Controller;
use App\Services\DonacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DonacionController extends Controller
{
    /**
     * El Controller solo valida la solicitud y llama al servicio.
     * La lógica principal queda en DonacionService.
     */
    public function store(Request $request, DonacionService $donacionService): RedirectResponse
    {
        $validated = $request->validate([
            'campana_id' => ['required', 'exists:campanas,id'],
            'tipo_pago_id' => ['required', 'exists:tipos_pago,id'],
            'visitante_id' => ['nullable', 'exists:visitantes,id'],
            'monto' => ['required', 'numeric', 'min:1', 'max:999999.99'],
            'metodo' => ['required', 'string', 'max:50'],
            'referencia_pago' => ['nullable', 'string', 'max:150'],
        ]);

        $resultado = $donacionService->registrar($validated);

        return redirect()
            ->route('turista.donaciones.confirmacion', [
                'donacion' => $resultado['donacion']->id,
            ])
            ->with('success', 'Donación registrada correctamente. Escanea el QR para completar el pago.');
    }
}