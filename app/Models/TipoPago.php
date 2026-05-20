<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoPago extends Model
{
    protected $table = 'tipos_pago';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'proveedor',
        'requiere_validacion_manual',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'requiere_validacion_manual' => 'boolean',
        ];
    }
}
