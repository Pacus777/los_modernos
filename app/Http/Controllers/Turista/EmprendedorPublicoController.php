<?php

namespace App\Http\Controllers\Turista;

use App\Http\Controllers\Controller;
use App\Models\Emprendedor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmprendedorPublicoController extends Controller
{
    /**
     * Muestra el perfil público del emprendedor.
     *
     * Esta pantalla es pública porque el turista accede desde un QR.
     * No requiere login.
     *
     * Aquí se preparan los datos necesarios para React:
     * - datos básicos del emprendedor,
     * - campaña activa,
     * - monto recaudado,
     * - meta de apoyo,
     * - porcentaje de progreso.
     */
    public function show(Request $request, int $id): Response
    {
        $emprendedor = Emprendedor::query()
            ->with([
                'campanas' => function ($query) {
                    $query
                        ->where('estado', 'activa')
                        ->withSum([
                            'donaciones as monto_validado' => function ($query) {
                                $query->where('estado_pago', 'validado');
                            },
                        ], 'monto')
                        ->orderByDesc('fecha_inicio')
                        ->limit(1);
                },
            ])
            ->findOrFail($id);

        $campanaActiva = $emprendedor->campanas->first();

        $meta = $campanaActiva
            ? (float) $campanaActiva->meta_apoyo
            : (float) ($emprendedor->meta_monto ?? 0);

        $montoRecaudado = $campanaActiva
            ? (float) ($campanaActiva->monto_validado ?? $campanaActiva->monto_recaudado ?? 0)
            : 0;

        $porcentaje = $meta > 0
            ? min(round(($montoRecaudado / $meta) * 100, 2), 100)
            : 0;

        return Inertia::render('Turista/Perfil', [
            'emprendedor' => [
                'id' => $emprendedor->id,
                'nombre' => $emprendedor->nombre,
                'apellidos' => $emprendedor->apellidos,
                'descripcion' => $emprendedor->descripcion,
                'fotografia' => $emprendedor->fotografia,
                'qr_url' => $emprendedor->qr_url,
                'estado' => $emprendedor->estado,
            ],

            'campanaActiva' => $campanaActiva ? [
                'id' => $campanaActiva->id,
                'titulo' => $campanaActiva->titulo,
                'meta_apoyo' => $meta,
                'monto_recaudado' => $montoRecaudado,
                'fecha_inicio' => $campanaActiva->fecha_inicio,
                'fecha_fin' => $campanaActiva->fecha_fin,
                'estado' => $campanaActiva->estado,
            ] : null,

            'progreso' => [
                'meta' => $meta,
                'monto_recaudado' => $montoRecaudado,
                'monto_faltante' => max($meta - $montoRecaudado, 0),
                'porcentaje' => $porcentaje,
            ],
        ]);
    }
}