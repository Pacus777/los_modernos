<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Services\AdminTwoFactorService;
use App\Services\AuditLogService;
use App\Support\AuthRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): InertiaResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $user?->esAdmin() || ! $user->tieneDosFactoresActivo()) {
            return redirect()->to(AuthRedirect::homeRouteForRole($user?->nombreRol()));
        }

        if ($request->session()->get('two_factor_passed') === true) {
            return redirect()->to(AuthRedirect::homeRouteForRole('admin'));
        }

        return Inertia::render('Auth/TwoFactorChallenge', [
            'email' => $user->email,
        ]);
    }

    public function store(
        Request $request,
        AdminTwoFactorService $twoFactorService,
        AuditLogService $auditLogService,
    ): RedirectResponse
    {
        $user = $request->user();

        if (! $user?->esAdmin() || ! $user->tieneDosFactoresActivo()) {
            return redirect()->to(AuthRedirect::homeRouteForRole($user?->nombreRol()));
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:16'],
        ]);

        if (! $twoFactorService->codigoValidoParaUsuario($user, $validated['code'])) {
            return back()->withErrors([
                'code' => __('auth.two_factor_invalid'),
            ]);
        }

        $request->session()->put('two_factor_passed', true);

        $auditLogService->registrar(
            AuditAction::AuthTwoFactorPassed,
            actor: $user,
            request: $request,
        );

        return AuthRedirect::redirectAfterLogin($request);
    }
}
