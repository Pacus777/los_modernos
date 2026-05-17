<?php

namespace App\Http\Controllers\Turista;

use App\Http\Controllers\Controller;
use App\Services\RagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function store(Request $request, RagService $ragService): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'pregunta' => ['required', 'string', 'min:2', 'max:300'],
            'idioma' => ['nullable', 'string', 'in:es,en'],
            'context_emprendedor_id' => ['nullable', 'integer', 'exists:emprendedores,id'],
        ]);

        $idioma = $data['idioma']
            ?? session('locale')
            ?? app()->getLocale()
            ?? 'es';

        $respuesta = $ragService->responder(
            $data['pregunta'],
            $idioma,
            isset($data['context_emprendedor_id']) ? (int) $data['context_emprendedor_id'] : null,
        );

        if ($request->expectsJson()) {
            return response()->json([
                'rag' => $respuesta,
            ]);
        }

        return redirect()
            ->back()
            ->with('rag', $respuesta);
    }
}
