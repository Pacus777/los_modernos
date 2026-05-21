<?php

namespace App\Enums;

/**
 * Visibilidad de una publicación (S4-01).
 */
enum EmprendedorPostEstado: string
{
    case Borrador = 'borrador';
    case Publicado = 'publicado';
    case Oculto = 'oculto';

    /** @return list<string> */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
