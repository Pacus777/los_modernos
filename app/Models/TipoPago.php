<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoPago extends Model
{
    protected $table = 'tipos_pago';

    public const PROVEEDOR_MANUAL = 'manual';
    public const PROVEEDOR_BANCO = 'banco';
    public const PROVEEDOR_LIBELULA = 'libelula';

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
            'requiere_validacion_manual' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function donaciones(): HasMany
    {
        return $this->hasMany(Donacion::class, 'tipo_pago_id');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeProveedor(Builder $query, string $proveedor): Builder
    {
        return $query->where('proveedor', $proveedor);
    }

    public function scopeManual(Builder $query): Builder
    {
        return $query->where('requiere_validacion_manual', true);
    }

    public function scopeAutomaticos(Builder $query): Builder
    {
        return $query->where('requiere_validacion_manual', false);
    }

    public function esManual(): bool
    {
        return $this->proveedor === self::PROVEEDOR_MANUAL;
    }

    public function esBanco(): bool
    {
        return $this->proveedor === self::PROVEEDOR_BANCO;
    }

    public function esLibelula(): bool
    {
        return $this->proveedor === self::PROVEEDOR_LIBELULA;
    }

    public function requiereValidacionManual(): bool
    {
        return (bool) $this->requiere_validacion_manual;
    }
}