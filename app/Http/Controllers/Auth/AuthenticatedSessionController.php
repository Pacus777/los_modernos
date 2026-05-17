<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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
    public function create(): InertiaResponse
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        $rol = $user->rol?->nombre;

        return match ($rol) {
            'admin' => redirect()->intended(route('admin.dashboard')),
            'cajero' => redirect()->intended(route('cajero.efectivo')),
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
    public function destroy(Request $request): HttpFoundationResponse
    {
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
