<?php

namespace App\Http\Controllers\Turista;

use App\Http\Controllers\Controller;
use App\Http\Requests\Turista\StoreChatRequest;
use App\Services\RagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ChatController extends Controller
{
    public function store(StoreChatRequest $request, RagService $ragService): JsonResponse|RedirectResponse
    {
        $data = $request->validated();

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
