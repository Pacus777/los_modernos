<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Verifica que el usuario autenticado tenga uno de los roles permitidos.
     *
     * Ejemplos de uso en rutas:
     *
     * check.role:admin
     * check.role:cajero
     * check.role:admin,cajero
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Obtener el usuario autenticado
        |--------------------------------------------------------------------------
        |
        | Si no existe usuario, se bloquea el acceso.
        | Normalmente el middleware auth ya hace esto antes, pero esta validación
        | evita errores si alguien usa check.role sin auth.
        |
        */

        $user = $request->user();

        if (!$user) {
            abort(403, 'No tienes una sesión activa.');
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Obtener el rol del usuario
        |--------------------------------------------------------------------------
        |
        | nombreRol() viene del modelo User.
        | Debe devolver:
        | - admin
        | - cajero
        | - null si no tiene rol asignado
        |
        */

        $rolUsuario = $user->nombreRol();

        if (!$rolUsuario) {
            abort(403, 'Tu usuario no tiene un rol asignado.');
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Verificar si el rol está permitido
        |--------------------------------------------------------------------------
        |
        | $roles viene desde la ruta.
        |
        | Ejemplo:
        | check.role:admin,cajero
        |
        | Laravel lo interpreta como:
        | ['admin', 'cajero']
        |
        */

        if (!in_array($rolUsuario, $roles, true)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}