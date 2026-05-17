<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCambio extends Model
{
    protected $table = 'tipos_cambio';

    protected $fillable = [
        'base',
        'quote',
        'compra',
        'venta',
        'promedio',
        'fuente',
        'tipo',
        'consultado_en',
        'metadata',
    ];

    protected $casts = [
        'compra' => 'decimal:4',
        'venta' => 'decimal:4',
        'promedio' => 'decimal:4',
        'consultado_en' => 'datetime',
        'metadata' => 'array',
    ];
}