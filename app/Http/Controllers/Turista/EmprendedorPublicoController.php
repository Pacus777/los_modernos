<?php

namespace App\Http\Controllers\Turista;

use App\Http\Controllers\Controller;
use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\TipoPago;
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
     * - progreso (T-29 / PB-09): meta, monto acumulado por donaciones validadas y porcentaje
     *   calculados en servidor; el frontend solo muestra props.
     */
    public function show(Request $request, int $id): Response
    {
        $emprendedor = Emprendedor::query()
            ->with([
                'campanas' => function ($query) {
                    $query
                        ->visibleEnPerfilTurista()
                        ->withSum([
                            'donaciones as monto_validado' => function ($query) {
                                $query->where('estado_pago', Donacion::ESTADO_VALIDADO);
                            },
                        ], 'monto')
                        ->orderByDesc('fecha_inicio');
                },
            ])
            ->findOrFail($id);

        $campanasActivas = $emprendedor->campanas;

        $campanaActiva = $campanasActivas->first();

        $progreso = $this->calcularProgresoCampanaActiva($campanaActiva, $emprendedor);

        return Inertia::render('Turista/Perfil', [
            'emprendedor' => [
                'id' => $emprendedor->id,
                'nombre' => $emprendedor->nombre,
                'apellidos' => $emprendedor->apellidos,
                'descripcion' => $emprendedor->descripcion,
                'tipo_emprendimiento' => $emprendedor->tipo_emprendimiento?->value,
                'tipo_emprendimiento_etiqueta' => $emprendedor->tipo_emprendimiento?->etiqueta(),
                'departamento' => $emprendedor->departamento?->value,
                'departamento_etiqueta' => $emprendedor->departamento?->etiqueta(),
                'fotografia' => $emprendedor->fotografia,
                'qr_url' => $emprendedor->qr_url,
                'estado' => $emprendedor->estado,
            ],

            'campanaActiva' => $campanaActiva ? [
                'id' => $campanaActiva->id,
                'titulo' => $campanaActiva->titulo,
                'meta_apoyo' => $progreso['meta'],
                'monto_recaudado' => $progreso['monto_recaudado'],
                'fecha_inicio' => $campanaActiva->fecha_inicio,
                'fecha_fin' => $campanaActiva->fecha_fin,
                'estado' => $campanaActiva->estado,
            ] : null,

            'campanasActivas' => $campanasActivas
                ->map(fn (Campana $c) => [
                    'id' => $c->id,
                    'titulo' => $c->titulo,
                ])
                ->values()
                ->all(),

            'progreso' => $progreso,

            'tipoPagos' => TipoPago::query()
                ->select('id', 'nombre', 'codigo')
                ->orderBy('id')
                ->get(),
        ]);
    }

    /**
     * Progreso de la campaña activa según la suma de donaciones validadas (PB-09 / T-29).
     *
     * @return array{meta: float, monto_recaudado: float, monto_faltante: float, porcentaje: float}
     */
    private function calcularProgresoCampanaActiva(?Campana $campana, Emprendedor $emprendedor): array
    {
        $meta = $campana
            ? (float) $campana->meta_apoyo
            : (float) ($emprendedor->meta_monto ?? 0);

        $montoValidado = $campana
            ? (float) ($campana->monto_validado ?? 0)
            : 0.0;

        $porcentaje = $meta > 0
            ? min(round(($montoValidado / $meta) * 100, 2), 100.0)
            : 0.0;

        return [
            'meta' => $meta,
            'monto_recaudado' => $montoValidado,
            'monto_faltante' => max($meta - $montoValidado, 0.0),
            'porcentaje' => $porcentaje,
        ];
    }
}