<?php

namespace App\Models;

use App\Enums\EmprendedorPostReaccionTipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmprendedorPostReaccion extends Model
{
    protected $table = 'emprendedor_post_reacciones';

    protected $fillable = [
        'emprendedor_post_id',
        'actor_type',
        'actor_id',
        'tipo',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => EmprendedorPostReaccionTipo::class,
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(EmprendedorPost::class, 'emprendedor_post_id');
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}
