<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Registro de trazabilidad de un aporte (PB-12 / T-34).
 *
 * La PK UUID la puede asignar quien cree el registro (p. ej. TraceabilityService, T-35)
 * o dejarse vacía para que Eloquent genere una con HasUuids.
 *
 * Se usa UUID v4 (Str::uuid) en lugar del v7 ordenado por defecto de HasUuids,
 * para un identificador con aspecto aleatorio estándar.
 */
class Transaccion extends Model
{
    use HasUuids;

    protected $table = 'transacciones';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * UUID aleatorio (v4). HasUuids por defecto usa UUID v7 (ordenado por tiempo),
     * que muchas personas reconocen menos en pantalla.
     */
    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }

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
