<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsEmprendedor
{
    /**
     * Permite el acceso únicamente a usuarios con rol emprendedor.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'No tienes una sesión activa.');
        }

        if (! $user->tieneRol('emprendedor')) {
            abort(403, 'Solo los emprendedores pueden acceder a esta sección.');
        }

        return $next($request);
    }
}