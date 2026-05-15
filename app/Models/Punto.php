<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Punto extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Modelo Punto
    |--------------------------------------------------------------------------
    |
    | Representa una ubicación física que tiene su propio QR.
    |
    | Ejemplos:
    | - Mesa principal
    | - Mostrador de feria
    | - Punto turístico
    |
    */

    protected $table = 'puntos_fisicos';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'ubicacion',
        'qr_url',
        'estado',
    ];

    /**
     * Emprendedores asociados a este punto físico.
     */
    public function emprendedores(): BelongsToMany
    {
        return $this->belongsToMany(
            Emprendedor::class,
            'emprendedor_punto',
            'punto_id',
            'emprendedor_id'
        )->withTimestamps();
    }

    /**
     * Verifica si el punto está activo.
     */
    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }
}