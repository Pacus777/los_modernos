<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro pivote explícito de seguimiento turista → emprendedor (S4-01).
 */
class EmprendedorSeguidor extends Model
{
    protected $table = 'emprendedor_seguidores';

    protected $fillable = [
        'emprendedor_id',
        'visitante_id',
    ];

    public function emprendedor(): BelongsTo
    {
        return $this->belongsTo(Emprendedor::class, 'emprendedor_id');
    }

    public function visitante(): BelongsTo
    {
        return $this->belongsTo(Visitante::class, 'visitante_id');
    }
}
