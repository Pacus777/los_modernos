<?php

namespace App\Http\Controllers\Emprendedor;

use App\Enums\AuditAction;
use App\Enums\Departamento;
use App\Enums\TipoEmprendimiento;
use App\Http\Controllers\Controller;
use App\Http\Requests\Emprendedor\UpdatePerfilPublicoRequest;
use App\Services\AuditLogService;
use App\Services\EmprendedorPerfilService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PerfilController extends Controller
{
    public function edit(Request $request): Response|RedirectResponse
    {
        $emprendedor = $request->user()?->emprendedor;

        if (! $emprendedor) {
            return redirect()
                ->route('emprendedor.dashboard')
                ->with('error', 'Tu cuenta no está vinculada a un perfil de emprendedor.');
        }

        return Inertia::render('Emprendedor/PerfilEdit', [
            'emprendedor' => $emprendedor,
            'tiposEmprendimiento' => TipoEmprendimiento::opcionesParaFormulario(),
            'departamentos' => Departamento::opcionesParaFormulario(),
            'perfil_publico_url' => route('turista.emprendedor.show', $emprendedor),
        ]);
    }

    public function update(
        UpdatePerfilPublicoRequest $request,
        EmprendedorPerfilService $perfilService,
        AuditLogService $auditLogService,
    ): RedirectResponse {
        $emprendedor = $request->user()->emprendedor;

        $actualizado = $perfilService->actualizar($emprendedor, $request);

        $auditLogService->registrar(
            AuditAction::EmprendedorProfileUpdated,
            subject: $actualizado,
            actor: $request->user(),
            metadata: ['nombre' => $actualizado->nombreCompleto()],
            request: $request,
        );

        return redirect()
            ->route('emprendedor.dashboard')
            ->with('success', 'Tu perfil público se actualizó correctamente.');
    }
}
