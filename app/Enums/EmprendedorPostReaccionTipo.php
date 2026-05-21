<?php

namespace App\Enums;

/**
 * Reacciones permitidas en publicaciones (S4-01).
 */
enum EmprendedorPostReaccionTipo: string
{
    case MeGusta = 'me_gusta';
    case Aplauso = 'aplauso';
    case Apoyo = 'apoyo';
    case Inspirado = 'inspirado';

    /** @return list<string> */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
