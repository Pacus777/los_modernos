<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmprendedorNotificacion extends Model
{
    protected $table = 'emprendedor_notificaciones';

    public const TIPO_DONACION_VALIDADA = 'donacion_validada';

    protected $fillable = [
        'emprendedor_id',
        'donacion_id',
        'tipo',
        'titulo',
        'mensaje',
        'monto',
        'leida_at',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'leida_at' => 'datetime',
        ];
    }

    public function emprendedor(): BelongsTo
    {
        return $this->belongsTo(Emprendedor::class);
    }

    public function donacion(): BelongsTo
    {
        return $this->belongsTo(Donacion::class);
    }

    public function estaLeida(): bool
    {
        return $this->leida_at !== null;
    }
}
