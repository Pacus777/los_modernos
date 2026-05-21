<?php

namespace App\Http\Controllers\Emprendedor;

use App\Enums\AuditAction;
use App\Enums\RangoMonto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Emprendedor\FiltrarDonacionesRequest;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Services\AuditLogService;
use App\Services\EmprendedorDonacionHistorialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DonacionHistorialController extends Controller
{
    public function index(
        FiltrarDonacionesRequest $request,
        EmprendedorDonacionHistorialService $historialService,
    ): Response|RedirectResponse {
        $emprendedor = $this->emprendedorVinculado($request);

        if (! $emprendedor) {
            return redirect()
                ->route('emprendedor.dashboard')
                ->with('error', 'Tu cuenta no está vinculada a un emprendedor.');
        }

        $filtros = $request->filtrosNormalizados();

        $donaciones = $historialService
            ->consulta($emprendedor, $filtros)
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($donacion) => $historialService->serializarFila($donacion));

        return Inertia::render('Emprendedor/Donaciones/Index', [
            'donaciones' => $donaciones,
            'filtros' => $filtros,
            'resumen' => $historialService->resumen($emprendedor, $filtros),
            'rangosMonto' => RangoMonto::opcionesFiltro(),
            'estadosPago' => [
                ['value' => '', 'label' => 'Todos'],
                ['value' => Donacion::ESTADO_VALIDADO, 'label' => 'Validados'],
                ['value' => Donacion::ESTADO_PENDIENTE, 'label' => 'Pendientes'],
                ['value' => Donacion::ESTADO_RECHAZADO, 'label' => 'Rechazados'],
            ],
        ]);
    }

    public function exportarExcel(
        FiltrarDonacionesRequest $request,
        EmprendedorDonacionHistorialService $historialService,
        AuditLogService $auditLogService,
    ): StreamedResponse|RedirectResponse {
        return $this->exportarReporte(
            $request,
            $historialService,
            $auditLogService,
            'excel',
            fn (Emprendedor $emprendedor, array $filtros) => $historialService->respuestaExcelWayna($emprendedor, $filtros),
        );
    }

    public function exportarPdf(
        FiltrarDonacionesRequest $request,
        EmprendedorDonacionHistorialService $historialService,
        AuditLogService $auditLogService,
    ): HttpResponse|RedirectResponse {
        return $this->exportarReporte(
            $request,
            $historialService,
            $auditLogService,
            'pdf',
            fn (Emprendedor $emprendedor, array $filtros) => $historialService->respuestaPdfWayna($emprendedor, $filtros),
        );
    }

    /**
     * @param  callable(Emprendedor, array<string, mixed>): StreamedResponse|HttpResponse  $generar
     */
    private function exportarReporte(
        FiltrarDonacionesRequest $request,
        EmprendedorDonacionHistorialService $historialService,
        AuditLogService $auditLogService,
        string $formato,
        callable $generar,
    ): StreamedResponse|HttpResponse|RedirectResponse {
        $emprendedor = $this->emprendedorVinculado($request);

        if (! $emprendedor) {
            return redirect()
                ->route('emprendedor.dashboard')
                ->with('error', 'Tu cuenta no está vinculada a un emprendedor.');
        }

        $filtros = $request->filtrosNormalizados();

        $auditLogService->registrar(
            AuditAction::EmprendedorDonationsExported,
            actor: $request->user(),
            metadata: [
                'emprendedor_id' => $emprendedor->id,
                'formato' => $formato,
                'filtros' => $filtros,
                'total' => $historialService->consulta($emprendedor, $filtros)->count(),
            ],
            request: $request,
        );

        return $generar($emprendedor, $filtros);
    }

    private function emprendedorVinculado(Request $request): ?Emprendedor
    {
        return $request->user()?->emprendedor;
    }
}
