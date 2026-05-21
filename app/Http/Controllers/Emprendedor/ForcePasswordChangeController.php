<?php

namespace App\Http\Controllers\Emprendedor;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ForcePasswordChangeController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if (! $request->user()?->debeCambiarPassword()) {
            return redirect()->route('emprendedor.dashboard');
        }

        return Inertia::render('Emprendedor/CambiarPassword', [
            'usuario' => [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    }

    public function store(Request $request, AuditLogService $auditLogService): RedirectResponse
    {
        $user = $request->user();

        if (! $user?->debeCambiarPassword()) {
            return redirect()->route('emprendedor.dashboard');
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'password.required' => 'Escribí tu nueva contraseña.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ])->save();

        $auditLogService->registrar(
            AuditAction::EmprendedorPasswordChanged,
            actor: $user,
            metadata: ['emprendedor_id' => $user->emprendedor?->id],
            request: $request,
        );

        return redirect()
            ->route('emprendedor.dashboard')
            ->with('success', 'Contraseña actualizada. Ya podés usar tu panel con normalidad.');
    }
}
