<?php

namespace App\Http\Controllers\Turista;

use App\Http\Controllers\Controller;
use App\Services\RagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ChatController
    |--------------------------------------------------------------------------
    |
    | Recibe preguntas del turista desde el ChatWidget.
    | No retorna JSON.
    | Retorna redirect()->back() con flash para que Inertia lo comparta
    | con la página actual.
    |
    */

    public function store(Request $request, RagService $ragService): RedirectResponse
    {
        $data = $request->validate([
            'pregunta' => ['required', 'string', 'min:2', 'max:300'],
            'idioma' => ['nullable', 'string', 'in:es,en'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Idioma activo
        |--------------------------------------------------------------------------
        |
        | Priorizamos el idioma enviado por React.
        | Si no llega, usamos el idioma guardado en sesión.
        | Si tampoco existe, usamos español.
        |
        */

        $idioma = $data['idioma']
            ?? session('locale')
            ?? app()->getLocale()
            ?? 'es';

        $respuesta = $ragService->responder($data['pregunta'], $idioma);

        return redirect()
            ->back()
            ->with('rag', $respuesta);
    }
}