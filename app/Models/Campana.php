<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Campana extends Model
{
    public const ESTADO_ACTIVA = 'activa';

    public const ESTADO_INACTIVA = 'inactiva';

    public const ESTADO_FINALIZADA = 'finalizada';

    /*
    |--------------------------------------------------------------------------
    | Regla de negocio (PB-09)
    |--------------------------------------------------------------------------
    |
    | Un emprendedor puede tener varias campañas en el tiempo, pero solo una
    | debería estar en estado "activa" a la vez. Eso se valida al crear/editar
    | campañas (admin); la base no impone un índice único parcial por motor.
    |
    | monto_recaudado se mantiene alineado con donaciones validadas vía
    | DonacionObserver al cambiar estado_pago.
    |
    */

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

    /**
     * Campaña con estado activa y dentro del rango de fechas (T-A12).
     */
    public function scopeVisibleEnPerfilTurista(Builder $query, ?Carbon $fecha = null): Builder
    {
        $fecha ??= now()->startOfDay();
        $dia = $fecha->toDateString();

        return $query
            ->where('estado', self::ESTADO_ACTIVA)
            ->where(function (Builder $q) use ($dia): void {
                $q->whereNull('fecha_inicio')
                    ->orWhereDate('fecha_inicio', '<=', $dia);
            })
            ->where(function (Builder $q) use ($dia): void {
                $q->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $dia);
            });
    }

    public function estaVisibleEnPerfilTurista(?Carbon $fecha = null): bool
    {
        if ($this->estado !== self::ESTADO_ACTIVA) {
            return false;
        }

        return self::fechasPermitenVisibilidadPublica(
            $this->fecha_inicio?->toDateString(),
            $this->fecha_fin?->toDateString(),
            $fecha,
        );
    }

    /**
     * @param  string|null  $fechaInicio  Formato Y-m-d
     * @param  string|null  $fechaFin  Formato Y-m-d
     */
    public static function fechasPermitenVisibilidadPublica(
        ?string $fechaInicio,
        ?string $fechaFin,
        ?Carbon $fecha = null,
    ): bool {
        $hoy = ($fecha ?? now())->copy()->startOfDay();

        if ($fechaInicio) {
            $inicio = Carbon::parse($fechaInicio)->startOfDay();
            if ($inicio->gt($hoy)) {
                return false;
            }
        }

        if ($fechaFin) {
            $fin = Carbon::parse($fechaFin)->startOfDay();
            if ($fin->lt($hoy)) {
                return false;
            }
        }

        return true;
    }
}

