<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TuristaNotificacion extends Model
{
    protected $table = 'turista_notificaciones';

    public const TIPO_NUEVO_POST = 'nuevo_post';
    public const TIPO_NUEVA_META = 'nueva_meta';

    protected $fillable = [
        'user_id',
        'emprendedor_id',
        'emprendedor_post_id',
        'campana_id',
        'tipo',
        'titulo',
        'mensaje',
        'url',
        'leida_at',
    ];

    protected function casts(): array
    {
        return [
            'leida_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function emprendedor(): BelongsTo
    {
        return $this->belongsTo(Emprendedor::class, 'emprendedor_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(EmprendedorPost::class, 'emprendedor_post_id');
    }

    public function campana(): BelongsTo
    {
        return $this->belongsTo(Campana::class, 'campana_id');
    }

    public function estaLeida(): bool
    {
        return $this->leida_at !== null;
    }
}
