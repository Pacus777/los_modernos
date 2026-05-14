<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campana extends Model
{
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
