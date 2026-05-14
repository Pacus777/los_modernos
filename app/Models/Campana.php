<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campana extends Model
{
    public const ESTADO_ACTIVA = 'activa';

    public const ESTADO_INACTIVA = 'inactiva';

    public const ESTADO_FINALIZADA = 'finalizada';

    /*
    |--------------------------------------------------------------------------
    | Regla de negocio (PB-09)
    |--------------------------------------------------------------------------
    |
    | Un emprendedor puede tener varias campañas en el tiempo, pero solo una
    | debería estar en estado "activa" a la vez. Eso se valida al crear/editar
    | campañas (admin); la base no impone un índice único parcial por motor.
    |
    | monto_recaudado se mantiene alineado con donaciones validadas vía
    | DonacionObserver al cambiar estado_pago.
    |
    */

    protected $table = 'campanas';

    protected $fillable = [
        'emprendedor_id',
        'titulo',
        'meta_apoyo',
        'monto_recaudado',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'meta_apoyo' => 'decimal:2',
        'monto_recaudado' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function emprendedor(): BelongsTo
    {
        return $this->belongsTo(Emprendedor::class, 'emprendedor_id');
    }

    public function donaciones(): HasMany
    {
        return $this->hasMany(Donacion::class, 'campana_id');
    }
}
