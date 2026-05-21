<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessedWebhook extends Model
{
    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $table = 'processed_webhooks';

    protected $fillable = [
        'proveedor',
        'idempotency_key',
        'event_id',
        'transaction_id',
        'donacion_id',
        'webhook_log_id',
        'status',
        'http_status',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function donacion(): BelongsTo
    {
        return $this->belongsTo(Donacion::class, 'donacion_id');
    }

    public function webhookLog(): BelongsTo
    {
        return $this->belongsTo(WebhookLog::class, 'webhook_log_id');
    }

    public function estaCompletado(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function estaProcesando(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }
}
