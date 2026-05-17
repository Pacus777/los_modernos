<?php

namespace App\Http\Controllers\Turista;

use App\Http\Controllers\Controller;
use App\Models\Punto;
use App\Services\LibreTranslationService;
use Illuminate\Http\Request;
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
    public function show(string $slug, Request $request, LibreTranslationService $translator): Response
    {
        $locale = $request->session()->get('locale', 'es');
        $locale = in_array($locale, ['es', 'en'], true) ? $locale : 'es';

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

        $emprendedores = $punto->emprendedores->map(function ($emprendedor) use ($translator, $locale) {
            return [
                'id' => $emprendedor->id,
                'nombre' => $emprendedor->nombre,
                'apellidos' => $emprendedor->apellidos,
                'fotografia' => $emprendedor->fotografia,
                'descripcion' => $translator->traducirCampo(
                    entidadTipo: 'emprendedor',
                    entidadId: $emprendedor->id,
                    campo: 'descripcion',
                    texto: $emprendedor->descripcion,
                    idiomaDestino: $locale,
                ),
                'tipo_emprendimiento' => $emprendedor->tipo_emprendimiento?->value,
                'departamento' => $emprendedor->departamento?->value,
            ];
        })->values();

        return Inertia::render('Turista/Punto', [
            'punto' => [
                'nombre' => $punto->nombre,
                'ubicacion' => $punto->ubicacion,
                'descripcion' => $translator->traducirCampo(
                    entidadTipo: 'punto',
                    entidadId: $punto->id,
                    campo: 'descripcion',
                    texto: $punto->descripcion,
                    idiomaDestino: $locale,
                ),
            ],
            'emprendedores' => $emprendedores,
        ]);
    }
}