<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\WebhookPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Webhooks de Libélula / pasarela (S3-02).
 */
class LibelulaWebhookController extends Controller
{
    public function __invoke(Request $request, WebhookPaymentService $webhookPaymentService): JsonResponse
    {
        if (! $this->firmaValida($request)) {
            return response()->json([
                'message' => 'invalid_signature',
            ], 401);
        }

        $resultado = $webhookPaymentService->procesarLibelula($request);

        return response()->json($resultado['body'], $resultado['status']);
    }

    private function firmaValida(Request $request): bool
    {
        $secreto = config('wayna.webhook_libelula_secret');

        if (! filled($secreto)) {
            return true;
        }

        $firmaRecibida = $request->header('X-Webhook-Signature')
            ?? $request->header('X-Libélula-Signature')
            ?? $request->input('signature');

        if (! filled($firmaRecibida)) {
            return false;
        }

        $cuerpo = $request->getContent();
        $esperada = hash_hmac('sha256', $cuerpo, (string) $secreto);

        return hash_equals($esperada, (string) $firmaRecibida);
    }
}
