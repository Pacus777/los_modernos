<?php

namespace App\Http\Controllers\Turista;

use App\Http\Controllers\Controller;
use App\Models\Punto;
use Inertia\Inertia;
use Inertia\Response;

class PuntoController extends Controller
{
    /**
     * Muestra la lista de emprendedores activos de un punto físico.
     *
     * Ruta pública:
     * /punto/{slug}
     */
    public function show(string $slug): Response
    {
        $punto = Punto::query()
            ->where('slug', $slug)
            ->where('estado', 'activo')
            ->with([
                'emprendedores' => function ($query) {
                    $query
                        ->where('estado', 'activo')
                        ->orderBy('nombre');
                },
            ])
            ->firstOrFail();

        return Inertia::render('Turista/Punto', [
            'punto' => $punto,
            'emprendedores' => $punto->emprendedores,
        ]);
    }
}