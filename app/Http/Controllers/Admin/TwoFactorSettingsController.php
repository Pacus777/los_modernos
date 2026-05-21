<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Services\AdminTwoFactorService;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TwoFactorSettingsController extends Controller
{
    public function edit(Request $request): InertiaResponse
    {
        $user = $request->user();

        return Inertia::render('Admin/Seguridad/TwoFactor', [
            'enabled' => $user->tieneDosFactoresActivo(),
            'confirmedAt' => $user->google2fa_confirmed_at?->toIso8601String(),
            'setup' => [
                'qrDataUri' => $request->session()->get('two_factor_setup_qr'),
                'secret' => $request->session()->get('two_factor_setup_secret_display'),
            ],
        ]);
    }

    public function preparar(Request $request, AdminTwoFactorService $twoFactorService): RedirectResponse
    {
        $user = $request->user();

        if ($user->tieneDosFactoresActivo()) {
            return redirect()
                ->route('admin.two-factor.edit')
                ->with('error', __('auth.two_factor_already_enabled'));
        }

        $secreto = $twoFactorService->generarSecreto();
        $otpauthUrl = $twoFactorService->urlOtpAuth($user, $secreto);

        $request->session()->put('two_factor_setup_secret', $secreto);
        $request->session()->put('two_factor_setup_secret_display', $secreto);
        $request->session()->put('two_factor_setup_qr', $twoFactorService->qrComoDataUri($otpauthUrl));

        return redirect()->route('admin.two-factor.edit');
    }

    public function activar(
        Request $request,
        AdminTwoFactorService $twoFactorService,
        AuditLogService $auditLogService,
    ): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:16'],
        ]);

        $secreto = $request->session()->get('two_factor_setup_secret');

        if (! is_string($secreto) || $secreto === '') {
            return redirect()
                ->route('admin.two-factor.edit')
                ->withErrors(['code' => __('auth.two_factor_setup_expired')]);
        }

        if (! $twoFactorService->codigoValido($secreto, $validated['code'])) {
            return back()->withErrors([
                'code' => __('auth.two_factor_invalid'),
            ]);
        }

        $twoFactorService->activar($request->user(), $secreto);

        $auditLogService->registrar(
            AuditAction::AdminTwoFactorEnabled,
            actor: $request->user(),
            request: $request,
        );

        $request->session()->forget([
            'two_factor_setup_secret',
            'two_factor_setup_secret_display',
            'two_factor_setup_qr',
        ]);
        $request->session()->put('two_factor_passed', true);

        return redirect()
            ->route('admin.two-factor.edit')
            ->with('success', __('auth.two_factor_enabled_success'));
    }

    public function desactivar(
        Request $request,
        AdminTwoFactorService $twoFactorService,
        AuditLogService $auditLogService,
    ): RedirectResponse {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:16'],
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if (! $twoFactorService->codigoValidoParaUsuario($user, $validated['code'])) {
            return back()->withErrors([
                'code' => __('auth.two_factor_invalid'),
            ]);
        }

        $twoFactorService->desactivar($user);

        $auditLogService->registrar(
            AuditAction::AdminTwoFactorDisabled,
            actor: $user,
            request: $request,
        );

        $request->session()->forget('two_factor_passed');

        return redirect()
            ->route('admin.two-factor.edit')
            ->with('success', __('auth.two_factor_disabled_success'));
    }
}
