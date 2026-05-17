<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Si ya hay sesión, no mostrar login ni registro de invitado.
 */
class RedirectAuthenticatedFromGuest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $rol = $request->user()?->rol?->nombre;

        return match ($rol) {
            'admin' => redirect()->route('admin.dashboard'),
            'cajero' => redirect()->route('cajero.efectivo'),
            default => redirect()->route('turista.explorar'),
        };
    }
}
