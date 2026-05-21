<?php

namespace App\Enums;

/**
 * Tipo de publicación en el muro del emprendedor (S4-01).
 */
enum EmprendedorPostTipo: string
{
    case Texto = 'texto';
    case Imagen = 'imagen';
    case Video = 'video';

    /** @return list<string> */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
