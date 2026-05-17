<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}