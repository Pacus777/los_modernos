<?php

namespace App\Http\Controllers\Emprendedor;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Emprendedor\StoreEmprendedorMetaRequest;
use App\Http\Requests\Emprendedor\UpdateEmprendedorMetaRequest;
use App\Models\Campana;
use App\Models\Emprendedor;
use App\Services\AuditLogService;
use App\Services\EmprendedorMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use App\Jobs\NuevaMetaEmail;

class EmprendedorMetaController extends Controller
{
    public function index(Request $request, EmprendedorMetaService $metaService): Response|RedirectResponse
    {
        $emprendedor = $this->emprendedorVinculado($request);

        if (! $emprendedor) {
            return $this->sinVinculo();
        }

        return Inertia::render('Emprendedor/MisMetas/Index', [
            'gestion' => $metaService->datosGestionMetas($emprendedor),
        ]);
    }

    public function create(Request $request, EmprendedorMetaService $metaService): Response|RedirectResponse
    {
        $emprendedor = $this->emprendedorVinculado($request);

        if (! $emprendedor) {
            return $this->sinVinculo();
        }

        Gate::authorize('create', Campana::class);

        if ($metaService->tieneCampanaActiva($emprendedor)) {
            return redirect()
                ->route('emprendedor.mis-metas.index')
                ->with('error', 'Ya tenés una meta activa. Podés editarla o cerrarla.');
        }

        return Inertia::render('Emprendedor/Meta/Form', [
            'modo' => 'crear',
            'campana' => null,
            'fechaHoy' => now()->toDateString(),
        ]);
    }

    public function edit(Request $request, EmprendedorMetaService $metaService): Response|RedirectResponse
    {
        $emprendedor = $this->emprendedorVinculado($request);

        if (! $emprendedor) {
            return $this->sinVinculo();
        }

        $campana = $metaService->campanaActiva($emprendedor);

        if (! $campana) {
            return redirect()
                ->route('emprendedor.mis-metas.index')
                ->with('error', 'No tenés una meta activa. Creá una para mostrar tu progreso a los turistas.');
        }

        Gate::authorize('update', $campana);

        return Inertia::render('Emprendedor/Meta/Form', [
            'modo' => 'editar',
            'campana' => $campana->only([
                'id',
                'titulo',
                'meta_apoyo',
                'fecha_inicio',
                'fecha_fin',
                'estado',
            ]),
            'fechaHoy' => now()->toDateString(),
        ]);
    }

    public function store(
        StoreEmprendedorMetaRequest $request,
        EmprendedorMetaService $metaService,
        AuditLogService $auditLogService,
    ): RedirectResponse {
        $emprendedor = $request->user()->emprendedor;

        $campana = $metaService->crear($emprendedor, $request->validated());

        $auditLogService->registrar(
            AuditAction::EmprendedorMetaCreated,
            subject: $campana,
            actor: $request->user(),
            metadata: [
                'emprendedor_id' => $emprendedor->id,
                'meta_apoyo' => (float) $campana->meta_apoyo,
                'titulo' => $campana->titulo,
            ],
            request: $request,
        );

        NuevaMetaEmail::dispatch($campana);

        return redirect()
            ->route('emprendedor.mis-metas.index')
            ->with('success', 'Tu meta de apoyo quedó activa. Los turistas ya pueden ver tu progreso.');
    }

    public function update(
        UpdateEmprendedorMetaRequest $request,
        Campana $campana,
        EmprendedorMetaService $metaService,
        AuditLogService $auditLogService,
    ): RedirectResponse {
        $actualizada = $metaService->actualizar($campana, $request->validated());

        $auditLogService->registrar(
            AuditAction::EmprendedorMetaUpdated,
            subject: $actualizada,
            actor: $request->user(),
            metadata: [
                'emprendedor_id' => $actualizada->emprendedor_id,
                'meta_apoyo' => (float) $actualizada->meta_apoyo,
            ],
            request: $request,
        );

        return redirect()
            ->route('emprendedor.mis-metas.index')
            ->with('success', 'Tu meta de apoyo se actualizó correctamente.');
    }

    public function close(
        Request $request,
        Campana $campana,
        EmprendedorMetaService $metaService,
        AuditLogService $auditLogService,
    ): RedirectResponse {
        Gate::authorize('close', $campana);

        $cerrada = $metaService->cerrar($campana);

        $auditLogService->registrar(
            AuditAction::EmprendedorMetaClosed,
            subject: $cerrada,
            actor: $request->user(),
            metadata: [
                'emprendedor_id' => $cerrada->emprendedor_id,
                'con_donaciones' => $cerrada->donaciones()->exists(),
            ],
            request: $request,
        );

        $mensaje = $cerrada->donaciones()->exists()
            ? 'Tu meta se cerró. Las donaciones registradas se conservan en el historial.'
            : 'Tu meta de apoyo se cerró correctamente.';

        return redirect()
            ->route('emprendedor.mis-metas.index')
            ->with('success', $mensaje);
    }

    private function emprendedorVinculado(Request $request): ?Emprendedor
    {
        return $request->user()?->emprendedor;
    }

    private function sinVinculo(): RedirectResponse
    {
        return redirect()
            ->route('emprendedor.dashboard')
            ->with('error', 'Tu cuenta no está vinculada a un emprendedor.');
    }
}
