<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exige verificación 2FA en la sesión actual para admins con 2FA activo (S3-04).
 */
class EnsureAdminTwoFactorPassed
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->esAdmin() || ! $user->tieneDosFactoresActivo()) {
            return $next($request);
        }

        if ($request->session()->get('two_factor_passed') === true) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'two_factor_required',
            ], 403);
        }

        return redirect()->route('two-factor.challenge');
    }
}
