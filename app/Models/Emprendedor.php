<?php

namespace App\Models;

use App\Enums\Departamento;
use App\Enums\TipoEmprendimiento;
use App\Services\EmprendedorMediosService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


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
        'tipo_emprendimiento',
        'departamento',
        'fotografia',
        'foto_empresa',
        'galeria',
        'video_url',
        'whatsapp',
        'instagram',
        'facebook',
        'tiktok',
        'sitio_web',
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
        'tipo_emprendimiento' => TipoEmprendimiento::class,
        'departamento' => Departamento::class,
        'galeria' => 'array',
    ];

    /**
     * Foto de perfil del emprendedor (cabecera turista). No usar foto_empresa aquí.
     */
    public function urlFotoPerfil(): ?string
    {
        return EmprendedorMediosService::urlAlmacenPublico($this->fotografia);
    }

    /** @deprecated Use urlFotoPerfil() — mantiene compatibilidad con props Inertia. */
    public function urlFotoPortada(): ?string
    {
        return $this->urlFotoPerfil();
    }

    public function urlFotoEmpresa(): ?string
    {
        return EmprendedorMediosService::urlAlmacenPublico($this->foto_empresa);
    }

    /**
     * @return list<string>
     */
    public function urlsGaleriaPublica(): array
    {
        $rutas = is_array($this->galeria) ? $this->galeria : [];

        return array_values(array_filter(array_map(
            fn (string $ruta) => EmprendedorMediosService::urlAlmacenPublico($ruta),
            $rutas,
        )));
    }

    /**
     * @return array{titulo: string, tipo: 'none'|'archivo'|'embed', src: string|null, embed_url: string|null}
     */
    public function presentacionVideoPublico(): array
    {
        return EmprendedorMediosService::presentacionVideo($this->video_url);
    }

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

    /**
 * Puntos físicos donde aparece este emprendedor.
 */
    public function puntos(): BelongsToMany
    {
        return $this->belongsToMany(
            Punto::class,
            'emprendedor_punto',
            'emprendedor_id',
            'punto_id'
        )->withTimestamps();
    }
}