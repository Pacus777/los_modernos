<?php

namespace App\Http\Controllers\Turista;

use App\Services\TipoCambioService;
use App\Services\LibreTranslationService;
use App\Services\VisitanteService;
use App\Support\RedesSocialesEmprendedor;
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
    public function show(
        Request $request,
        int $id,
        LibreTranslationService $translator,
        TipoCambioService $tipoCambioService,
        VisitanteService $visitanteService,
    ): Response {
        $locale = $this->obtenerLocaleTurista($request);

        $emprendedor = Emprendedor::query()
            ->where('estado', 'activo')
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

        $descripcionEmprendedor = $translator->traducirCampo(
            entidadTipo: 'emprendedor',
            entidadId: $emprendedor->id,
            campo: 'descripcion',
            texto: $emprendedor->descripcion,
            idiomaDestino: $locale,
        );

        $tipoEmprendimientoEtiqueta = $translator->traducirCampo(
            entidadTipo: 'emprendedor',
            entidadId: $emprendedor->id,
            campo: 'tipo_emprendimiento_etiqueta',
            texto: $emprendedor->tipo_emprendimiento?->etiqueta(),
            idiomaDestino: $locale,
        );

        $departamentoEtiqueta = $translator->traducirCampo(
            entidadTipo: 'emprendedor',
            entidadId: $emprendedor->id,
            campo: 'departamento_etiqueta',
            texto: $emprendedor->departamento?->etiqueta(),
            idiomaDestino: $locale,
        );

        $campanaActivaTitulo = $campanaActiva
            ? $translator->traducirCampo(
                entidadTipo: 'campana',
                entidadId: $campanaActiva->id,
                campo: 'titulo',
                texto: $campanaActiva->titulo,
                idiomaDestino: $locale,
            )
            : null;

        return Inertia::render('Turista/Perfil', [
            'emprendedor' => [
                'id' => $emprendedor->id,
                'nombre' => $emprendedor->nombre,
                'apellidos' => $emprendedor->apellidos,
                'descripcion' => $descripcionEmprendedor,
                'tipo_emprendimiento' => $emprendedor->tipo_emprendimiento?->value,
                'tipo_emprendimiento_etiqueta' => $tipoEmprendimientoEtiqueta,
                'departamento' => $emprendedor->departamento?->value,
                'departamento_etiqueta' => $departamentoEtiqueta,
                'fotografia' => $emprendedor->fotografia,
                'foto_portada' => $emprendedor->urlFotoPerfil(),
                'qr_url' => $emprendedor->qr_url,
                'estado' => $emprendedor->estado,
                'tipoCambio' => $tipoCambioService->obtenerUsdBobReferencial(),
            ],

            'medios' => [
                'foto_empresa' => $emprendedor->urlFotoEmpresa(),
                'galeria' => $emprendedor->urlsGaleriaPublica(),
                'video' => $emprendedor->presentacionVideoPublico(),
            ],

            'redes' => RedesSocialesEmprendedor::paraFrontend(
                $emprendedor->whatsapp,
                $emprendedor->instagram,
                $emprendedor->facebook,
                $emprendedor->tiktok,
            ),

            'campanaActiva' => $campanaActiva ? [
                'id' => $campanaActiva->id,
                'titulo' => $campanaActivaTitulo,
                'meta_apoyo' => $progreso['meta'],
                'monto_recaudado' => $progreso['monto_recaudado'],
                'fecha_inicio' => $campanaActiva->fecha_inicio,
                'fecha_fin' => $campanaActiva->fecha_fin,
                'estado' => $campanaActiva->estado,
            ] : null,

            'campanasActivas' => $campanasActivas
                ->map(fn (Campana $c) => [
                    'id' => $c->id,
                    'titulo' => $translator->traducirCampo(
                        entidadTipo: 'campana',
                        entidadId: $c->id,
                        campo: 'titulo',
                        texto: $c->titulo,
                        idiomaDestino: $locale,
                    ),
                ])
                ->values()
                ->all(),

            'progreso' => $progreso,

            'tipoPagos' => TipoPago::query()
                ->where('activo', true)
                ->select('id', 'nombre', 'codigo')
                ->orderBy('id')
                ->get(),

            'visitanteNombrePrefill' => $visitanteService->nombreEnSesion($request),
        ]);
    }

    private function obtenerLocaleTurista(Request $request): string
    {
        $locale = $request->session()->get('locale')
            ?? $request->session()->get('idioma')
            ?? app()->getLocale()
            ?? 'es';

        return in_array($locale, ['es', 'en'], true)
            ? $locale
            : 'es';
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