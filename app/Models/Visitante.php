<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Visitante extends Model
{
    protected $table = 'visitantes';

    protected $fillable = [
        'codigo',
        'nombre',
        'idioma',
        'session_id',
    ];

    public function donaciones(): HasMany
    {
        return $this->hasMany(Donacion::class, 'visitante_id');
    }

    /**
     * Emprendedores que este visitante sigue (S4-01).
     */
    public function emprendedoresSeguidos(): BelongsToMany
    {
        return $this->belongsToMany(
            Emprendedor::class,
            'emprendedor_seguidores',
            'visitante_id',
            'emprendedor_id',
        )->withTimestamps();
    }

    /**
     * Reacciones del visitante en publicaciones (S4-01).
     */
    public function reaccionesEnPosts(): MorphMany
    {
        return $this->morphMany(EmprendedorPostReaccion::class, 'actor');
    }
}