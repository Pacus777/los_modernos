<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Donacion extends Model
{
    protected $table = 'donaciones';

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_VALIDADO = 'validado';
    public const ESTADO_RECHAZADO = 'rechazado';

    public const MONEDA_BOB = 'BOB';

    public const PROVEEDOR_MANUAL = 'manual';
    public const PROVEEDOR_BANCO = 'banco';
    public const PROVEEDOR_LIBELULA = 'libelula';

    protected $fillable = [
        'campana_id',
        'tipo_pago_id',
        'visitante_id',
        'monto',
        'moneda',
        'metodo',
        'estado_pago',
        'referencia_pago',
        'payment_uuid',
        'transaction_id',
        'proveedor_pago',
        'estado_proveedor',
        'checkout_url',
        'pagado_en',
        'metadata_pago',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'pagado_en' => 'datetime',
        'metadata_pago' => 'array',
    ];

    public function campana(): BelongsTo
    {
        return $this->belongsTo(Campana::class, 'campana_id');
    }

    public function tipoPago(): BelongsTo
    {
        return $this->belongsTo(TipoPago::class, 'tipo_pago_id');
    }

    public function visitante(): BelongsTo
    {
        return $this->belongsTo(Visitante::class, 'visitante_id');
    }

    public function webhookLogs(): HasMany
    {
        return $this->hasMany(WebhookLog::class, 'donacion_id');
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('estado_pago', self::ESTADO_PENDIENTE);
    }

    public function scopeValidadas(Builder $query): Builder
    {
        return $query->where('estado_pago', self::ESTADO_VALIDADO);
    }

    public function scopeRechazadas(Builder $query): Builder
    {
        return $query->where('estado_pago', self::ESTADO_RECHAZADO);
    }

    public function scopeProveedor(Builder $query, string $proveedor): Builder
    {
        return $query->where('proveedor_pago', $proveedor);
    }

    public function scopeConTransactionId(Builder $query, string $transactionId): Builder
    {
        return $query->where('transaction_id', $transactionId);
    }

    public function scopeConPaymentUuid(Builder $query, string $paymentUuid): Builder
    {
        return $query->where('payment_uuid', $paymentUuid);
    }

    public function scopePagadas(Builder $query): Builder
    {
        return $query->whereNotNull('pagado_en');
    }

    public function estaPendiente(): bool
    {
        return $this->estado_pago === self::ESTADO_PENDIENTE;
    }

    public function estaValidada(): bool
    {
        return $this->estado_pago === self::ESTADO_VALIDADO;
    }

    public function estaRechazada(): bool
    {
        return $this->estado_pago === self::ESTADO_RECHAZADO;
    }

    public function fueProcesadaPorLibelula(): bool
    {
        return $this->proveedor_pago === self::PROVEEDOR_LIBELULA;
    }

    public function requiereValidacionManual(): bool
    {
        if ($this->relationLoaded('tipoPago') && $this->tipoPago) {
            return $this->tipoPago->requiereValidacionManual();
        }

        return $this->tipoPago()
            ->where('requiere_validacion_manual', true)
            ->exists();
    }
}