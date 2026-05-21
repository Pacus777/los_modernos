<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\RangoMonto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RevisionMasivaDonacionesRequest;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Services\AuditLogService;
use App\Services\DonacionRevisionMasivaService;
use App\Services\TraceabilityService;
use App\Services\AdminDonacionRevisionService;
use App\Support\WaynaDonacionesCsvExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class DonacionController extends Controller
{
    public function __construct(
        protected TraceabilityService $traceabilityService,
        protected DonacionRevisionMasivaService $revisionMasivaService,
        protected AuditLogService $auditLogService,
        protected AdminDonacionRevisionService $revisionService,
    ) {
    }

    /**
     * Listado paginado de donaciones para revisión en panel admin (T-38, paso 1).
     *
     * Filtros vía query string: emprendedor_id, estado_pago, fechas, rango_monto.
     * La paginación usa withQueryString() para conservar filtros al cambiar de página.
     */
    public function index(Request $request): Response
    {
        $filtrosInternos = $this->filtrosDonacionesDesdeRequest($request);
        $filters = [
            'emprendedor_id' => $filtrosInternos['emprendedor_id'],
            'estado_pago' => $filtrosInternos['estado_pago'],
            'fecha_desde' => $filtrosInternos['fecha_desde'],
            'fecha_hasta' => $filtrosInternos['fecha_hasta'],
            'rango_monto' => $filtrosInternos['rango_monto'],
        ];

        $donaciones = $this->consultaDonacionesAdmin($filtrosInternos)
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $observaciones = $this->traceabilityService->observacionesValidacionPorDonaciones(
            collect($donaciones->items())->pluck('id')->map(fn ($id) => (int) $id)->all(),
        );

        $donaciones->through(function (Donacion $donacion) use ($observaciones) {
            $donacion->observacion_validacion = $observaciones[$donacion->id] ?? null;

            return $donacion;
        });

        return Inertia::render('Admin/Donaciones/Index', [
            'donaciones' => $donaciones,
            'filters' => $filters,
            'emprendedores' => $this->listaEmprendedoresParaFiltro(),
            'rangosMonto' => RangoMonto::opcionesFiltro(),
        ]);
    }

    /**
     * Exportación CSV para liquidación admin (S2-11), respeta filtros del listado.
     */
    public function exportarCsv(Request $request): HttpFoundationResponse
    {
        $filters = $this->filtrosDonacionesDesdeRequest($request);

        $donaciones = $this->consultaDonacionesAdmin($filters)
            ->orderByDesc('created_at')
            ->get();

        $csv = (new WaynaDonacionesCsvExport)->render($donaciones);
        $nombre = 'wayna-liquidacion-donaciones-'.now()->format('Y-m-d_His').'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$nombre.'"',
        ]);
    }

    /**
     * @return array{
     *     emprendedor_id: string,
     *     estado_pago: string,
     *     fecha_desde: string,
     *     fecha_hasta: string,
     *     rango_monto: string,
     *     _emprendedor_id: int|null
     * }
     */
    private function filtrosDonacionesDesdeRequest(Request $request): array
    {
        $validated = $request->validate([
            'emprendedor_id' => ['nullable', 'integer', 'exists:emprendedores,id'],
            'estado_pago' => ['nullable', 'string', 'max:20'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
            'rango_monto' => ['nullable', 'string', Rule::in(RangoMonto::valores())],
        ]);

        $estadosValidos = [
            Donacion::ESTADO_PENDIENTE,
            Donacion::ESTADO_VALIDADO,
            Donacion::ESTADO_RECHAZADO,
        ];

        $estadoPago = $validated['estado_pago'] ?? '';
        if ($estadoPago !== '' && ! in_array($estadoPago, $estadosValidos, true)) {
            $estadoPago = '';
        }

        $rangoMonto = RangoMonto::desdeFiltro($validated['rango_monto'] ?? '')?->value ?? '';

        $emprendedorId = isset($validated['emprendedor_id'])
            ? (int) $validated['emprendedor_id']
            : null;

        return [
            'emprendedor_id' => $emprendedorId ? (string) $emprendedorId : '',
            'estado_pago' => $estadoPago,
            'fecha_desde' => $validated['fecha_desde'] ?? '',
            'fecha_hasta' => $validated['fecha_hasta'] ?? '',
            'rango_monto' => $rangoMonto,
            '_emprendedor_id' => $emprendedorId,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function consultaDonacionesAdmin(array $filters): Builder
    {
        $emprendedorId = $filters['_emprendedor_id'] ?? null;
        $rangoMonto = $filters['rango_monto'] ?? '';

        return Donacion::query()
            ->with(['campana.emprendedor', 'tipoPago', 'visitante'])
            ->when(
                $emprendedorId,
                fn ($q) => $q->whereHas(
                    'campana',
                    fn ($c) => $c->where('emprendedor_id', $emprendedorId),
                ),
            )
            ->when(
                filled($filters['estado_pago'] ?? ''),
                fn ($q) => $q->where('estado_pago', $filters['estado_pago']),
            )
            ->when(
                filled($filters['fecha_desde'] ?? ''),
                fn ($q) => $q->whereDate('created_at', '>=', $filters['fecha_desde']),
            )
            ->when(
                filled($filters['fecha_hasta'] ?? ''),
                fn ($q) => $q->whereDate('created_at', '<=', $filters['fecha_hasta']),
            )
            ->when(
                $rango = RangoMonto::desdeFiltro($rangoMonto),
                fn ($q) => $rango->aplicarFiltro($q, 'monto'),
            );
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{id: int, nombre_completo: string}>
     */
    private function listaEmprendedoresParaFiltro()
    {
        return Emprendedor::query()
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get(['id', 'nombre', 'apellidos'])
            ->map(fn (Emprendedor $e) => [
                'id' => $e->id,
                'nombre_completo' => $e->nombreCompleto(),
            ]);
    }

    /**
     * Marca una donación pendiente como validada (T-38).
     *
     * monto_recaudado lo actualiza DonacionObserver al cambiar estado_pago (T-A19).
     */
    public function validar(Request $request, Donacion $donacion): RedirectResponse
    {
        try {
            $this->revisionService->confirmar($donacion, $request->user());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', 'Donación validada correctamente.');
    }

    /**
     * Marca una donación pendiente como rechazada (T-38).
     */
    public function rechazar(Request $request, Donacion $donacion): RedirectResponse
    {
        $validated = $request->validate([
            'motivo' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->revisionService->rechazar(
                $donacion,
                $request->user(),
                $validated['motivo'] ?? null
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', 'Donación rechazada.');
    }

    /**
     * Validación o rechazo masivo de donaciones pendientes (T-A18).
     */
    public function revisionMasiva(RevisionMasivaDonacionesRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $accion = $validated['accion'];

        $estadoNuevo = $accion === 'validar'
            ? Donacion::ESTADO_VALIDADO
            : Donacion::ESTADO_RECHAZADO;

        $resultado = $this->revisionMasivaService->aplicar(
            $validated['ids'],
            $estadoNuevo,
            $request->user()?->id,
        );

        if ($resultado['procesadas'] === 0) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Ninguna donación pendiente pudo procesarse. Es posible que ya hayan sido revisadas.',
                );
        }

        $this->auditLogService->registrar(
            AuditAction::AdminDonationMassReview,
            actor: $request->user(),
            metadata: [
                'accion' => $accion,
                'estado_nuevo' => $estadoNuevo,
                'procesadas' => $resultado['procesadas'],
                'omitidas' => $resultado['omitidas'],
                'monto_total' => $resultado['monto_total'],
                'ids_solicitados' => $validated['ids'],
            ],
            request: $request,
        );

        $etiquetaAccion = $accion === 'validar' ? 'validadas' : 'rechazadas';
        $monto = number_format($resultado['monto_total'], 2, '.', ',');

        $mensaje = sprintf(
            '%d donación(es) %s por un total de Bs %s.',
            $resultado['procesadas'],
            $etiquetaAccion,
            $monto,
        );

        if ($resultado['omitidas'] > 0) {
            $mensaje .= sprintf(
                ' %d ya no estaban pendientes y se omitieron para evitar doble revisión.',
                $resultado['omitidas'],
            );
        }

        return redirect()
            ->back()
            ->with('success', $mensaje);
    }
}
