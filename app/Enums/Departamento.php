<?php

namespace App\Enums;

/**
 * Departamento de Bolivia (T-A14).
 */
enum Departamento: string
{
    case LaPaz = 'la_paz';
    case SantaCruz = 'santa_cruz';
    case Cochabamba = 'cochabamba';
    case Oruro = 'oruro';
    case Potosi = 'potosi';
    case Chuquisaca = 'chuquisaca';
    case Tarija = 'tarija';
    case Beni = 'beni';
    case Pando = 'pando';

    public function etiqueta(): string
    {
        return match ($this) {
            self::LaPaz => 'La Paz',
            self::SantaCruz => 'Santa Cruz',
            self::Cochabamba => 'Cochabamba',
            self::Oruro => 'Oruro',
            self::Potosi => 'Potosí',
            self::Chuquisaca => 'Chuquisaca',
            self::Tarija => 'Tarija',
            self::Beni => 'Beni',
            self::Pando => 'Pando',
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
    public static function opcionesParaFormulario(): array
    {
        return array_map(
            fn (self $caso) => [
                'value' => $caso->value,
                'label' => $caso->etiqueta(),
            ],
            self::cases(),
        );
    }

    public static function etiquetaDe(?string $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return self::tryFrom($valor)?->etiqueta();
    }
}
