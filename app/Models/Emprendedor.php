<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Emprendedor extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Modelo Emprendedor
    |--------------------------------------------------------------------------
    |
    | Representa a un emprendedor registrado en el sistema WAYNA.
    |
    | Este modelo será usado después por:
    | - Panel administrativo.
    | - Perfil público del turista.
    | - Generación de códigos QR.
    | - Campañas.
    | - Donaciones.
    |
    */

    protected $table = 'emprendedores';

    /*
    |--------------------------------------------------------------------------
    | Campos permitidos para asignación masiva
    |--------------------------------------------------------------------------
    |
    | Estos campos podrán llenarse desde formularios controlados.
    | No incluimos id, created_at ni updated_at porque Laravel los maneja.
    |
    */

    protected $fillable = [
        'nombre',
        'apellidos',
        'descripcion',
        'fotografia',
        'qr_url',
        'estado',
        'meta_monto',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de tipos
    |--------------------------------------------------------------------------
    |
    | meta_monto se trata como decimal con dos posiciones porque representa
    | un monto económico.
    |
    */

    protected $casts = [
        'meta_monto' => 'decimal:2',
    ];

    /**
     * Verifica si el emprendedor está activo.
     *
     * Ayuda a escribir código más legible:
     *
     * $emprendedor->estaActivo()
     *
     * en vez de repetir:
     *
     * $emprendedor->estado === 'activo'
     */
    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    /**
     * Devuelve el nombre completo del emprendedor.
     *
     * Será útil en tablas, reportes, perfiles públicos y mensajes.
     */
    public function nombreCompleto(): string
    {
        return trim($this->nombre . ' ' . $this->apellidos);
    }

    public function campanas(): HasMany
    {
        return $this->hasMany(Campana::class, 'emprendedor_id');
    }
}