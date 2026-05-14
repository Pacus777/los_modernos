<?php

namespace App\Http\Controllers\Cajero;

use App\Http\Controllers\Controller;
use App\Models\Donacion;
use App\Services\TraceabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PendienteController extends Controller
{
    public function __construct(
        protected TraceabilityService $traceabilityService,
    ) {
    }

    /**
     * Donaciones en efectivo pendientes de confirmación en caja (T-39, paso 1).
     *
     * Criterio: estado «pendiente» y (método contiene «efectivo» o tipo de pago código «efectivo»).
     */
    public function index(Request $request): Response
    {
        $pendientes = Donacion::query()
            ->with(['campana.emprendedor', 'tipoPago', 'visitante'])
            ->where('estado_pago', Donacion::ESTADO_PENDIENTE)
            ->where(function ($q) {
                $q->where('metodo', 'like', '%efectivo%')
                    ->orWhereHas(
                        'tipoPago',
                        fn ($tq) => $tq->where('codigo', 'efectivo')
                    );
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Cajero/Pendientes', [
            'pendientes' => $pendientes,
        ]);
    }

    /**
     * Formulario para confirmar en caja una donación en efectivo (URL del QR, T-39 paso 4).
     */
    public function confirmarForm(Donacion $donacion): RedirectResponse|Response
    {
        $donacion->loadMissing(['campana.emprendedor', 'tipoPago', 'visitante']);

        if (! $this->permiteConfirmacionCajero($donacion)) {
            return redirect()
                ->route('cajero.efectivo.pendientes')
                ->with(
                    'error',
                    'Esta donación no está pendiente en efectivo o ya fue procesada.',
                );
        }

        return Inertia::render('Cajero/ConfirmarEfectivo', [
            'donacion' => $donacion,
        ]);
    }

    /**
     * Confirma recepción del efectivo: pendiente → validado (T-39 paso 4).
     */
    public function confirmar(Request $request, Donacion $donacion): RedirectResponse
    {
        $donacion->loadMissing(['tipoPago']);

        if (! $this->permiteConfirmacionCajero($donacion)) {
            return redirect()
                ->route('cajero.efectivo.pendientes')
                ->with(
                    'error',
                    'Esta donación no está pendiente en efectivo o ya fue procesada.',
                );
        }

        DB::transaction(function () use ($request, $donacion): void {
            $anterior = $donacion->estado_pago;
            $donacion->update(['estado_pago' => Donacion::ESTADO_VALIDADO]);
            $this->traceabilityService->registrarRevisionDonacionPorCajero(
                $donacion->fresh(),
                $anterior,
                Donacion::ESTADO_VALIDADO,
                $request->user()?->id,
            );
        });

        return redirect()
            ->route('cajero.efectivo.pendientes')
            ->with('success', 'Pago en efectivo confirmado en caja.');
    }

    /**
     * Misma regla que el listado de pendientes: solo efectivo y estado pendiente.
     */
    private function permiteConfirmacionCajero(Donacion $donacion): bool
    {
        if ($donacion->estado_pago !== Donacion::ESTADO_PENDIENTE) {
            return false;
        }

        $metodoEfectivo = str_contains(
            strtolower((string) $donacion->metodo),
            'efectivo',
        );
        $tipoEfectivo = $donacion->tipoPago?->codigo === 'efectivo';

        return $metodoEfectivo || $tipoEfectivo;
    }
}
