<?php

namespace App\Http\Middleware;

use App\Services\EmprendedorOnboardingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * S4-09/E-12: Rutas que siempre deben estar permitidas para evitar bloqueos
     * o bucles de redirección infinitos.
     *
     * @var list<string>
     */
    private const RUTAS_PERMITIDAS = [
        'emprendedor.perfil.edit',
        'emprendedor.perfil.update',
        'emprendedor.password.force',
        'emprendedor.password.force.store',
        'logout',
    ];

    /**
     * Maneja la validación de perfil completo para el emprendedor.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Si no hay sesión o no es de tipo emprendedor, continuar.
        if (! $user || ! $user->esEmprendedor()) {
            return $next($request);
        }

        $emprendedor = $user->emprendedor;

        // 2. Si no tiene perfil asociado, continuar (para evitar romper flujos sin relación).
        if (! $emprendedor) {
            return $next($request);
        }

        // 3. Si la ruta actual está explícitamente permitida, continuar.
        if ($request->routeIs(self::RUTAS_PERMITIDAS)) {
            return $next($request);
        }

        // 4. Si el perfil ya está completo, continuar.
        $onboardingService = app(EmprendedorOnboardingService::class);
        if ($onboardingService->estaCompleto($emprendedor)) {
            return $next($request);
        }

        // 5. De lo contrario, redirigir a editar el perfil con un mensaje explicativo.
        return redirect()
            ->route('emprendedor.perfil.edit')
            ->with('error', 'Por favor, completa los campos requeridos de tu perfil antes de continuar usando el panel.');
    }
}
