<?php

namespace App\Http\Controllers\Cajero;

use App\Http\Controllers\Controller;
use App\Models\Donacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\DonacionService;
use Illuminate\Validation\ValidationException;

class PendienteController extends Controller
{
    public function __construct(
        protected DonacionService $donacionService,
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

        return Inertia::render('Cajero/Efectivo', [
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
    /**
 * Confirma recepción del efectivo: pendiente → validado.
 *
 * La lógica real no vive aquí.
 * Se delega a DonacionService para que la validación actualice también
 * la campaña y registre trazabilidad.
 */
    public function confirmar(Request $request, Donacion $donacion): RedirectResponse
    {
        try {
            $this->donacionService->confirmarPagoEfectivo(
                $donacion,
                $request->user()?->id,
            );

            return redirect()
                ->route('cajero.efectivo.pendientes')
                ->with('success', 'Pago en efectivo confirmado en caja.');
        } catch (ValidationException $e) {
            return redirect()
                ->route('cajero.efectivo.pendientes')
                ->with('error', $e->errors()['donacion'][0] ?? 'No se pudo confirmar la donación.');
        }
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
