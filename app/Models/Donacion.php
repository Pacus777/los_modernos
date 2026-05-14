<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donacion extends Model
{
    protected $table = 'donaciones';

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_VALIDADO = 'validado';
    public const ESTADO_RECHAZADO = 'rechazado';

    protected $fillable = [
        'campana_id',
        'tipo_pago_id',
        'visitante_id',
        'monto',
        'metodo',
        'estado_pago',
        'referencia_pago',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
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
}