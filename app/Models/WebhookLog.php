<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    protected $table = 'webhook_logs';

    public const ESTADO_RECIBIDO = 'recibido';
    public const ESTADO_PROCESADO = 'procesado';
    public const ESTADO_ERROR = 'error';
    public const ESTADO_IGNORADO = 'ignorado';

    public const PROVEEDOR_LIBELULA = 'libelula';

    protected $fillable = [
        'proveedor',
        'event_id',
        'transaction_id',
        'donacion_id',
        'evento',
        'estado',
        'headers',
        'payload',
        'recibido_en',
        'procesado_en',
        'error',
    ];

    protected $casts = [
        'headers' => 'array',
        'payload' => 'array',
        'recibido_en' => 'datetime',
        'procesado_en' => 'datetime',
    ];

    public function donacion(): BelongsTo
    {
        return $this->belongsTo(Donacion::class, 'donacion_id');
    }

    public function scopeProveedor(Builder $query, string $proveedor): Builder
    {
        return $query->where('proveedor', $proveedor);
    }

    public function scopeLibelula(Builder $query): Builder
    {
        return $query->where('proveedor', self::PROVEEDOR_LIBELULA);
    }

    public function scopeRecibidos(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_RECIBIDO);
    }

    public function scopeProcesados(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_PROCESADO);
    }

    public function scopeConError(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_ERROR);
    }

    public function scopePorTransactionId(Builder $query, string $transactionId): Builder
    {
        return $query->where('transaction_id', $transactionId);
    }

    public function scopePorEventId(Builder $query, string $eventId): Builder
    {
        return $query->where('event_id', $eventId);
    }

    public function marcarComoProcesado(): void
    {
        $this->forceFill([
            'estado' => self::ESTADO_PROCESADO,
            'procesado_en' => now(),
            'error' => null,
        ])->save();
    }

    public function marcarComoError(string $mensaje): void
    {
        $this->forceFill([
            'estado' => self::ESTADO_ERROR,
            'procesado_en' => now(),
            'error' => $mensaje,
        ])->save();
    }

    public function marcarComoIgnorado(?string $mensaje = null): void
    {
        $this->forceFill([
            'estado' => self::ESTADO_IGNORADO,
            'procesado_en' => now(),
            'error' => $mensaje,
        ])->save();
    }
}