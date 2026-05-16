<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Traduccion extends Model
{
    protected $table = 'traducciones';

    protected $fillable = [
        'entidad_tipo',
        'entidad_id',
        'campo',
        'idioma_origen',
        'idioma_destino',
        'texto_original_hash',
        'texto_original',
        'texto_traducido',
        'proveedor',
    ];

    public static function hashTexto(?string $texto): string
    {
        return hash('sha256', trim((string) $texto));
    }
}