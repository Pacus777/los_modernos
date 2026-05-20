<?php

namespace App\Services;

use App\Models\WebhookLog;
use Illuminate\Http\Request;

/**
 * Registro de evidencia de webhooks entrantes (S2-01).
 */
class WebhookLogService
{
    public function registrarEntrada(
        string $proveedor,
        array $payload,
        ?Request $request = null,
        ?string $eventId = null,
        ?string $transactionId = null,
        ?int $donacionId = null,
    ): WebhookLog {
        return WebhookLog::query()->create([
            'proveedor' => strtolower(trim($proveedor)),
            'event_id' => $eventId,
            'transaction_id' => $transactionId,
            'donacion_id' => $donacionId,
            'evento' => $payload['evento'] ?? $payload['event'] ?? $payload['type'] ?? null,
            'estado' => WebhookLog::ESTADO_RECIBIDO,
            'headers' => $request?->headers->all(),
            'payload' => $payload,
            'recibido_en' => now(),
        ]);
    }
}
