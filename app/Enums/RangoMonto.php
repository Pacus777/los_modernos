<?php

namespace App\Enums;

use Illuminate\Database\Eloquent\Builder;

/**
 * Rangos de monto para filtros visuales (T-A15).
 * Bajo: hasta Bs. 500 | Medio: Bs. 501–2.000 | Alto: más de Bs. 2.000
 */
enum RangoMonto: string
{
    case Bajo = 'bajo';
    case Medio = 'medio';
    case Alto = 'alto';

    public const BAJO_MAXIMO = 500;

    public const MEDIO_MINIMO = 501;

    public const MEDIO_MAXIMO = 2000;

    public function etiqueta(): string
    {
        return match ($this) {
            self::Bajo => 'Bajo (hasta Bs. 500)',
            self::Medio => 'Medio (Bs. 501 – 2.000)',
            self::Alto => 'Alto (más de Bs. 2.000)',
        };
    }

    public function etiquetaCorta(): string
    {
        return match ($this) {
            self::Bajo => 'Bajo',
            self::Medio => 'Medio',
            self::Alto => 'Alto',
        };
    }

    /**
     * @return list<string>
     */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function opcionesFiltro(): array
    {
        return array_map(
            fn (self $caso) => [
                'value' => $caso->value,
                'label' => $caso->etiqueta(),
            ],
            self::cases(),
        );
    }

    public static function desdeFiltro(?string $valor): ?self
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return self::tryFrom($valor);
    }

    public static function clasificar(float|int|string|null $monto): ?self
    {
        if ($monto === null || $monto === '') {
            return null;
        }

        $n = (float) $monto;

        if ($n <= self::BAJO_MAXIMO) {
            return self::Bajo;
        }

        if ($n <= self::MEDIO_MAXIMO) {
            return self::Medio;
        }

        return self::Alto;
    }

    public function aplicarFiltro(Builder $query, string $column): void
    {
        match ($this) {
            self::Bajo => $query->where($column, '<=', self::BAJO_MAXIMO),
            self::Medio => $query->where($column, '>=', self::MEDIO_MINIMO)
                ->where($column, '<=', self::MEDIO_MAXIMO),
            self::Alto => $query->where($column, '>', self::MEDIO_MAXIMO),
        };
    }
}
