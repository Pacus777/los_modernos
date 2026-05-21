<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sesión admin por inactividad (S3-05): 30 min por defecto.
 */
class TouchAdminSessionActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->esAdmin()) {
            return $next($request);
        }

        $limiteSegundos = max(60, (int) config('wayna.admin_session_lifetime_minutes', 30) * 60);
        $ultima = (int) $request->session()->get('admin_last_activity', 0);

        if ($ultima > 0 && (time() - $ultima) > $limiteSegundos) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('status', __('auth.admin_session_expired'));
        }

        $request->session()->put('admin_last_activity', time());

        return $next($request);
    }
}
