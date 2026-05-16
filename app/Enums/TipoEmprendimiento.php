<?php

namespace App\Enums;

/**
 * Clasificación del emprendimiento (T-A13).
 * Valores almacenados en BD; etiquetas para UI y reportes.
 */
enum TipoEmprendimiento: string
{
    case Gastronomia = 'gastronomia';
    case Artesania = 'artesania';
    case Turismo = 'turismo';
    case Textil = 'textil';
    case Agricultura = 'agricultura';
    case Servicios = 'servicios';
    case Otro = 'otro';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Gastronomia => 'Gastronomía',
            self::Artesania => 'Artesanía',
            self::Turismo => 'Turismo',
            self::Textil => 'Textil',
            self::Agricultura => 'Agricultura',
            self::Servicios => 'Servicios',
            self::Otro => 'Otro',
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
