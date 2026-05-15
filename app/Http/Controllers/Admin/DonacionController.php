<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RangoMonto;
use App\Http\Controllers\Controller;
use App\Models\Donacion;
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
     * Filtros vía query string: estado_pago, fecha_desde, fecha_hasta.
     */
    public function index(Request $request): Response
    {
        $validated = $request->validate([
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

        $filters = [
            'estado_pago' => $estadoPago,
            'fecha_desde' => $validated['fecha_desde'] ?? '',
            'fecha_hasta' => $validated['fecha_hasta'] ?? '',
            'rango_monto' => $rangoMonto,
        ];

        $donaciones = Donacion::query()
            ->with(['campana.emprendedor', 'tipoPago', 'visitante'])
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
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Donaciones/Index', [
            'donaciones' => $donaciones,
            'filters' => [
                'estado_pago' => $filters['estado_pago'],
                'fecha_desde' => $filters['fecha_desde'],
                'fecha_hasta' => $filters['fecha_hasta'],
                'rango_monto' => $filters['rango_monto'],
            ],
            'rangosMonto' => RangoMonto::opcionesFiltro(),
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
