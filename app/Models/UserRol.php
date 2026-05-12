<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRol extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Modelo UserRol
    |--------------------------------------------------------------------------
    |
    | Representa la asignación de un rol a un usuario.
    |
    | La tabla user_roles funciona como tabla intermedia entre:
    | - users
    | - roles
    |
    | En este proyecto cada usuario tendrá como máximo un rol.
    | Esa regla está reforzada en la migración con unique(user_id).
    |
    */

    protected $table = 'user_roles';

    protected $fillable = [
        'user_id',
        'role_id',
    ];

    /**
     * La asignación pertenece a un usuario.
     *
     * Ejemplo:
     * user_roles.user_id = 1 apunta a users.id = 1
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * La asignación pertenece a un rol.
     *
     * Ejemplo:
     * user_roles.role_id = 1 apunta a roles.id = 1
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'role_id');
    }
}