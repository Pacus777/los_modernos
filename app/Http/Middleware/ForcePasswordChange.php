<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * E-04: redirige al emprendedor a cambiar contraseña si tiene credencial temporal.
 */
class ForcePasswordChange
{
    /**
     * @var list<string>
     */
    private const RUTAS_PERMITIDAS = [
        'emprendedor.password.force',
        'emprendedor.password.force.store',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->debeCambiarPassword()) {
            return $next($request);
        }

        if ($request->routeIs(self::RUTAS_PERMITIDAS)) {
            return $next($request);
        }

        return redirect()->route('emprendedor.password.force');
    }
}
