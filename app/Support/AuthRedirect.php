<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Redirecciones post-login según rol WAYNA (evita el /dashboard genérico de Breeze).
 */
class AuthRedirect
{
    public static function homeRouteForRole(?string $rol): string
    {
        return match ($rol) {
            'admin' => route('admin.dashboard'),
            'cajero' => route('cajero.efectivo'),
            'emprendedor' => route('emprendedor.dashboard'),
            default => route('turista.explorar'),
        };
    }

    public static function redirectAfterLogin(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $rol = $user?->rol?->nombre;

        if ($rol === 'emprendedor' && $user?->debeCambiarPassword()) {
            return redirect()->route('emprendedor.password.force');
        }

        $default = self::homeRouteForRole($rol);

        $intended = $request->session()->pull('url.intended');

        if (is_string($intended) && $intended !== '' && ! self::isGenericDashboardUrl($intended)) {
            return redirect()->to($intended);
        }

        return redirect()->to($default);
    }

    public static function isGenericDashboardUrl(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        return (bool) preg_match('#^/dashboard/?$#', $path);
    }

    /**
     * Solo rutas internas del mismo host (evita open redirect).
     */
    public static function isSafeLocalRedirect(string $url): bool
    {
        if (! str_starts_with($url, '/')) {
            return false;
        }

        if (str_starts_with($url, '//')) {
            return false;
        }

        return (bool) preg_match('#^/[a-zA-Z0-9_\-./?=&%]*$#', $url);
    }

    public static function rememberIntendedFromQuery(Request $request): void
    {
        if (! $request->filled('redirect')) {
            return;
        }

        $target = $request->string('redirect')->toString();

        if (self::isSafeLocalRedirect($target) && ! self::isGenericDashboardUrl($target)) {
            $request->session()->put('url.intended', $target);
        }
    }
}
