<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmprendedorSeguidor extends Model
{
    protected $table = 'emprendedor_seguidores';

    protected $fillable = [
        'emprendedor_id',
        'visitante_id',
        'email',
        'token_confirm',
        'token_unsub',
        'confirmado_en',
    ];

    protected function casts(): array
    {
        return [
            'confirmado_en' => 'datetime',
        ];
    }

    public function emprendedor(): BelongsTo
    {
        return $this->belongsTo(Emprendedor::class, 'emprendedor_id');
    }

    public function visitante(): BelongsTo
    {
        return $this->belongsTo(Visitante::class, 'visitante_id');
    }

    public function estaConfirmado(): bool
    {
        return $this->confirmado_en !== null;
    }

    public static function generarToken(): string
    {
        return Str::random(48);
    }
}
