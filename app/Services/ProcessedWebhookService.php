<?php

namespace App\Services;

use App\Models\ProcessedWebhook;
use App\Support\WebhookIdempotencyResult;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

/**
 * Idempotencia de webhooks de pasarela (S3-02).
 */
class ProcessedWebhookService
{
    /**
     * Construye una clave estable para deduplicar reenvíos.
     */
    public function resolverClaveIdempotencia(
        string $proveedor,
        ?string $eventId,
        ?string $transactionId,
    ): string {
        $proveedor = strtolower(trim($proveedor));

        if ($eventId !== null && trim($eventId) !== '') {
            return $proveedor.':event:'.trim($eventId);
        }

        if ($transactionId !== null && trim($transactionId) !== '') {
            return $proveedor.':tx:'.trim($transactionId);
        }

        throw new \InvalidArgumentException(
            'El webhook debe incluir event_id o transaction_id para idempotencia.',
        );
    }

    /**
     * Intenta reclamar el webhook. Si ya fue procesado, devuelve duplicado.
     */
    public function reclamar(
        string $proveedor,
        string $idempotencyKey,
        ?string $eventId = null,
        ?string $transactionId = null,
        ?int $webhookLogId = null,
    ): WebhookIdempotencyResult {
        try {
            $record = ProcessedWebhook::query()->create([
                'proveedor' => strtolower(trim($proveedor)),
                'idempotency_key' => $idempotencyKey,
                'event_id' => $eventId,
                'transaction_id' => $transactionId,
                'webhook_log_id' => $webhookLogId,
                'status' => ProcessedWebhook::STATUS_PROCESSING,
            ]);

            return WebhookIdempotencyResult::claimed($record);
        } catch (QueryException $exception) {
            if (! $this->esViolacionUnica($exception)) {
                throw $exception;
            }

            $existente = ProcessedWebhook::query()
                ->where('proveedor', strtolower(trim($proveedor)))
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if (! $existente) {
                throw $exception;
            }

            if ($existente->estaCompletado()) {
                return WebhookIdempotencyResult::duplicateCompleted($existente);
            }

            if ($existente->estaProcesando()) {
                return WebhookIdempotencyResult::duplicateInFlight($existente);
            }

            $existente->update([
                'status' => ProcessedWebhook::STATUS_PROCESSING,
                'webhook_log_id' => $webhookLogId ?? $existente->webhook_log_id,
                'event_id' => $eventId ?? $existente->event_id,
                'transaction_id' => $transactionId ?? $existente->transaction_id,
                'http_status' => null,
                'completed_at' => null,
            ]);

            return WebhookIdempotencyResult::claimed($existente->fresh());
        }
    }

    public function marcarCompletado(
        ProcessedWebhook $record,
        int $httpStatus = 200,
        ?int $donacionId = null,
    ): void {
        $record->forceFill([
            'status' => ProcessedWebhook::STATUS_COMPLETED,
            'http_status' => $httpStatus,
            'donacion_id' => $donacionId ?? $record->donacion_id,
            'completed_at' => now(),
        ])->save();
    }

    public function marcarFallido(
        ProcessedWebhook $record,
        int $httpStatus = 422,
        ?int $donacionId = null,
    ): void {
        $record->forceFill([
            'status' => ProcessedWebhook::STATUS_FAILED,
            'http_status' => $httpStatus,
            'donacion_id' => $donacionId ?? $record->donacion_id,
            'completed_at' => now(),
        ])->save();
    }

    private function esViolacionUnica(QueryException $exception): bool
    {
        $codigo = (string) $exception->getCode();

        if (Str::contains($codigo, '23000') || Str::contains($codigo, '23505')) {
            return true;
        }

        $mensaje = strtolower($exception->getMessage());

        return str_contains($mensaje, 'unique')
            || str_contains($mensaje, 'duplicate');
    }
}
