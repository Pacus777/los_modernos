<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditLogService;
use App\Support\AuthRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): InertiaResponse
    {
        AuthRedirect::rememberIntendedFromQuery($request);

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, AuditLogService $auditLogService): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        $auditLogService->registrar(
            AuditAction::AuthLoginSuccess,
            actor: $user,
            metadata: ['rol' => $user->rol?->nombre],
            request: $request,
        );

        $request->session()->forget('two_factor_passed');

        $rol = $user->rol?->nombre;

        if ($rol === 'admin' && $user->tieneDosFactoresActivo()) {
            return redirect()->route('two-factor.challenge');
        }

        return match ($rol) {
            'admin', 'cajero' => AuthRedirect::redirectAfterLogin($request),
            default => $this->logoutUserWithoutRole($request),
        };
    }

    private function logoutUserWithoutRole(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors([
                'email' => __('auth.no_role'),
            ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request, AuditLogService $auditLogService): HttpFoundationResponse
    {
        $auditLogService->registrar(
            AuditAction::AuthLogout,
            actor: $request->user(),
            request: $request,
        );

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $loginUrl = route('login');

        if ($request->inertia()) {
            return Inertia::location($loginUrl);
        }

        return redirect($loginUrl)->with('status', __('auth.logged_out'));
    }
}
