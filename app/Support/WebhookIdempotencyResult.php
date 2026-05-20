<?php

namespace App\Support;

use App\Models\ProcessedWebhook;

/**
 * Resultado de intentar reclamar un webhook para procesamiento (S3-02).
 */
final class WebhookIdempotencyResult
{
    private function __construct(
        public readonly bool $alreadyProcessed,
        public readonly bool $concurrentProcessing,
        public readonly ?ProcessedWebhook $record,
    ) {}

    public static function claimed(ProcessedWebhook $record): self
    {
        return new self(
            alreadyProcessed: false,
            concurrentProcessing: false,
            record: $record,
        );
    }

    public static function duplicateCompleted(ProcessedWebhook $record): self
    {
        return new self(
            alreadyProcessed: true,
            concurrentProcessing: false,
            record: $record,
        );
    }

    public static function duplicateInFlight(ProcessedWebhook $record): self
    {
        return new self(
            alreadyProcessed: false,
            concurrentProcessing: true,
            record: $record,
        );
    }

    public function puedeProcesar(): bool
    {
        return ! $this->alreadyProcessed && ! $this->concurrentProcessing && $this->record !== null;
    }
}
