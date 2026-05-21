<?php

namespace App\Models;

use App\Enums\EmprendedorPostEstado;
use App\Enums\EmprendedorPostTipo;
use App\Services\EmprendedorMediosService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmprendedorPost extends Model
{
    protected $table = 'emprendedor_posts';

    protected $fillable = [
        'emprendedor_id',
        'tipo',
        'contenido',
        'media_path',
        'enlace_externo',
        'estado',
        'publicado_en',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => EmprendedorPostTipo::class,
            'estado' => EmprendedorPostEstado::class,
            'publicado_en' => 'datetime',
            'orden' => 'integer',
        ];
    }

    public function emprendedor(): BelongsTo
    {
        return $this->belongsTo(Emprendedor::class, 'emprendedor_id');
    }

    public function reacciones(): HasMany
    {
        return $this->hasMany(EmprendedorPostReaccion::class, 'emprendedor_post_id');
    }

    public function estaPublicado(): bool
    {
        return $this->estado === EmprendedorPostEstado::Publicado;
    }

    /**
     * Posts visibles en el feed turista (S4-02).
     */
    public function scopePublicados(Builder $query): Builder
    {
        return $query
            ->where('estado', EmprendedorPostEstado::Publicado)
            ->whereNotNull('publicado_en')
            ->orderByDesc('publicado_en')
            ->orderByDesc('id');
    }

    public function urlMediaPublica(): ?string
    {
        return EmprendedorMediosService::urlAlmacenPublico($this->media_path);
    }
}
