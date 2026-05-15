<?php

namespace App\Http\Controllers\Cajero;

use App\Http\Controllers\Controller;
use App\Models\Donacion;
use App\Services\DonacionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EfectivoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | EfectivoController
    |--------------------------------------------------------------------------
    |
    | Controller exclusivo para el flujo de confirmación de pagos en efectivo.
    |
    | Puede ser usado por:
    | - cajero
    | - admin
    |
    | El cajero no debe acceder al panel admin completo.
    | Solo debe ver y confirmar donaciones en efectivo pendientes.
    |
    */

    /**
     * Muestra las donaciones en efectivo pendientes.
     *
     * Esta pantalla será usada después por:
     * resources/js/Pages/Cajero/Efectivo.jsx
     */
    public function index(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Donaciones pendientes en efectivo
        |--------------------------------------------------------------------------
        |
        | Cargamos campaña y emprendedor para mostrar datos útiles en la pantalla:
        | - nombre del emprendedor
        | - monto
        | - referencia de pago
        | - fecha
        |
        */

        $donaciones = Donacion::query()
            ->with([
                'tipoPago',
                'campana.emprendedor',
            ])
            ->where('estado_pago', Donacion::ESTADO_PENDIENTE)
            ->where(function ($query) {
                $query
                    ->where('metodo', 'efectivo')
                    ->orWhereHas('tipoPago', function ($tipoPagoQuery) {
                        $tipoPagoQuery->where('codigo', 'efectivo');
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Cajero/Efectivo', [
            'donaciones' => $donaciones,
        ]);
    }

    /**
     * Confirma una donación en efectivo.
     *
     * No se valida directamente aquí.
     * Se delega la lógica a DonacionService para reutilizar el mismo flujo
     * de actualización de estado y campaña.
     */
    public function confirmar(
        Donacion $donacion,
        DonacionService $donacionService
    ): RedirectResponse {
        $donacionService->confirmarPagoEfectivo($donacion);

        return redirect()
            ->route('cajero.efectivo')
            ->with('success', 'Pago en efectivo confirmado correctamente.');
    }
}