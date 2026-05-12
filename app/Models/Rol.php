<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Modelo Rol
    |--------------------------------------------------------------------------
    |
    | Representa los roles generales del sistema WAYNA.
    | Por ahora los roles base serán:
    | - admin
    | - cajero
    |
    | No se guarda el rol directamente en users porque usamos una tabla
    | intermedia user_roles. Esto permite extender la estructura después
    | sin modificar la tabla users.
    |
    */

    protected $table = 'roles';

    protected $fillable = [
        'nombre',
    ];

    /**
     * Un rol puede estar asignado a varios usuarios.
     *
     * Ejemplo:
     * - Rol admin puede estar asignado a varios usuarios administradores.
     * - Rol cajero puede estar asignado a varios usuarios cajeros.
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRol::class, 'role_id');
    }
}