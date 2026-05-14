<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Registro de trazabilidad de un aporte (PB-12 / T-34).
 *
 * La PK UUID la puede asignar quien cree el registro (p. ej. TraceabilityService, T-35)
 * o dejarse vacía para que Eloquent genere una con HasUuids.
 */
class Transaccion extends Model
{
    use HasUuids;

    protected $table = 'transacciones';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'origen',
        'destino',
        'estado',
        'metadatos',
    ];

    protected $casts = [
        'metadatos' => 'array',
    ];
}
