<?php

namespace App\Http\Controllers\Turista;

use App\Http\Controllers\Controller;
use App\Models\Emprendedor;
use App\Services\SeguirEmprendedorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SeguirEmprendedorController extends Controller
{
    public function store(
        Request $request,
        int $emprendedor,
        SeguirEmprendedorService $seguirService,
    ): RedirectResponse {
        $emprendedorModel = Emprendedor::query()
            ->where('estado', 'activo')
            ->findOrFail($emprendedor);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:180'],
        ]);

        $seguirService->solicitar($request, $emprendedorModel, $validated['email']);

        return redirect()
            ->route('turista.emprendedor.show', $emprendedorModel->id)
            ->with('seguimiento_status', 'pendiente');
    }

    public function confirmar(
        string $token,
        SeguirEmprendedorService $seguirService,
    ): Response {
        try {
            $registro = $seguirService->confirmar($token);
        } catch (ValidationException) {
            return Inertia::render('Turista/SeguirEstado', [
                'tipo' => 'error',
                'emprendedorNombre' => null,
            ]);
        }

        return Inertia::render('Turista/SeguirEstado', [
            'tipo' => 'confirmado',
            'emprendedorNombre' => $registro->emprendedor->nombreCompleto(),
            'emprendedorId' => $registro->emprendedor_id,
        ]);
    }

    public function baja(
        string $token,
        SeguirEmprendedorService $seguirService,
    ): Response {
        try {
            $emprendedor = $seguirService->darDeBaja($token);
        } catch (ValidationException) {
            return Inertia::render('Turista/SeguirEstado', [
                'tipo' => 'error',
                'emprendedorNombre' => null,
            ]);
        }

        return Inertia::render('Turista/SeguirEstado', [
            'tipo' => 'baja',
            'emprendedorNombre' => $emprendedor->nombreCompleto(),
            'emprendedorId' => $emprendedor->id,
        ]);
    }
}
