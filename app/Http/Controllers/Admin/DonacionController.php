<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RangoMonto;
use App\Http\Controllers\Controller;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Services\TraceabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DonacionController extends Controller
{
    public function __construct(
        protected TraceabilityService $traceabilityService,
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

        $filters = [
            'emprendedor_id' => $emprendedorId ? (string) $emprendedorId : '',
            'estado_pago' => $estadoPago,
            'fecha_desde' => $validated['fecha_desde'] ?? '',
            'fecha_hasta' => $validated['fecha_hasta'] ?? '',
            'rango_monto' => $rangoMonto,
        ];

        $donaciones = Donacion::query()
            ->with(['campana.emprendedor', 'tipoPago', 'visitante'])
            ->when(
                $emprendedorId,
                fn ($q) => $q->whereHas(
                    'campana',
                    fn ($c) => $c->where('emprendedor_id', $emprendedorId),
                ),
            )
            ->when(
                filled($filters['estado_pago']),
                fn ($q) => $q->where('estado_pago', $filters['estado_pago'])
            )
            ->when(
                filled($filters['fecha_desde']),
                fn ($q) => $q->whereDate('created_at', '>=', $filters['fecha_desde'])
            )
            ->when(
                filled($filters['fecha_hasta']),
                fn ($q) => $q->whereDate('created_at', '<=', $filters['fecha_hasta'])
            )
            ->when(
                $rango = RangoMonto::desdeFiltro($rangoMonto),
                fn ($q) => $rango->aplicarFiltro($q, 'monto'),
            )
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
     */
    public function validar(Request $request, Donacion $donacion): RedirectResponse
    {
        if ($donacion->estado_pago !== Donacion::ESTADO_PENDIENTE) {
            return redirect()
                ->back()
                ->with('error', 'Solo se pueden validar donaciones en estado pendiente.');
        }

        DB::transaction(function () use ($request, $donacion): void {
            $anterior = $donacion->estado_pago;
            $donacion->update(['estado_pago' => Donacion::ESTADO_VALIDADO]);
            $this->traceabilityService->registrarRevisionDonacionPorAdmin(
                $donacion->fresh(),
                $anterior,
                Donacion::ESTADO_VALIDADO,
                $request->user()?->id,
            );
        });

        return redirect()
            ->back()
            ->with('success', 'Donación validada correctamente.');
    }

    /**
     * Marca una donación pendiente como rechazada (T-38).
     */
    public function rechazar(Request $request, Donacion $donacion): RedirectResponse
    {
        if ($donacion->estado_pago !== Donacion::ESTADO_PENDIENTE) {
            return redirect()
                ->back()
                ->with('error', 'Solo se pueden rechazar donaciones en estado pendiente.');
        }

        DB::transaction(function () use ($request, $donacion): void {
            $anterior = $donacion->estado_pago;
            $donacion->update(['estado_pago' => Donacion::ESTADO_RECHAZADO]);
            $this->traceabilityService->registrarRevisionDonacionPorAdmin(
                $donacion->fresh(),
                $anterior,
                Donacion::ESTADO_RECHAZADO,
                $request->user()?->id,
            );
        });

        return redirect()
            ->back()
            ->with('success', 'Donación rechazada.');
    }
}
