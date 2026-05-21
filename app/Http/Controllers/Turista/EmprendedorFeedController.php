<?php

namespace App\Http\Controllers\Turista;

use App\Enums\Departamento;
use App\Enums\TipoEmprendimiento;
use App\Http\Controllers\Controller;
use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Services\LibreTranslationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class EmprendedorFeedController extends Controller
{
    public function __invoke(Request $request, LibreTranslationService $translator): Response
    {
        $locale = $this->obtenerLocaleTurista($request);

        // 1. Query base: solo emprendedores activos
        $query = Emprendedor::query()
            ->where('estado', 'activo');

        // 2. Filtro: Búsqueda textual (nombre, apellidos, descripción)
        $q = $request->string('q')->trim()->toString();
        if ($q !== '') {
            $termino = '%' . Str::lower($q) . '%';
            $query->where(function ($query) use ($termino) {
                $query->whereRaw('LOWER(nombre) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(apellidos) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(descripcion) LIKE ?', [$termino]);
            });
        }

        // 3. Filtro: Departamento
        $departamento = $request->string('departamento')->trim()->toString();
        if ($departamento !== '' && in_array($departamento, Departamento::valores(), true)) {
            $query->where('departamento', $departamento);
        }

        // 4. Filtro: Tipo de Emprendimiento (soporta tipo o tipo_emprendimiento)
        $tipo = trim((string) $request->input('tipo_emprendimiento', $request->input('tipo', '')));
        if ($tipo !== '' && in_array($tipo, TipoEmprendimiento::valores(), true)) {
            $query->where('tipo_emprendimiento', $tipo);
        }

        // 5. Cargar relaciones y cuentas necesarias
        $query->withCount(['posts as publicaciones_count' => function ($q) {
            $q->where('estado', \App\Enums\EmprendedorPostEstado::Publicado)
              ->whereNotNull('publicado_en');
        }]);

        $query->with(['campanas' => function ($q) {
            $q->visibleEnPerfilTurista()
              ->withSum(['donaciones as monto_validado' => function ($q) {
                  $q->where('estado_pago', Donacion::ESTADO_VALIDADO);
              }], 'monto');
        }]);

        // 6. Ordenamiento
        $orden = $request->string('orden', 'recientes')->trim()->toString();
        if (!in_array($orden, ['recientes', 'mas_publicaciones', 'mas_apoyados', 'cerca_meta'], true)) {
            $orden = 'recientes';
        }

        if ($orden === 'mas_publicaciones') {
            $query->orderByDesc('publicaciones_count')->orderByDesc('created_at')->orderByDesc('id');
        } elseif ($orden === 'mas_apoyados') {
            // Suma del monto_recaudado acumulado en todas las campañas
            $query->withSum('campanas as total_apoyado', 'monto_recaudado')
                ->orderByDesc('total_apoyado')
                ->orderByDesc('created_at')
                ->orderByDesc('id');
        } elseif ($orden === 'cerca_meta') {
            // Ordenar por porcentaje de campaña activa: monto_recaudado / meta_apoyo
            // COALESCE y NULLIF para robustez en PostgreSQL y prevenir división por cero
            $query->select('emprendedores.*')
                ->selectSub(function ($q) {
                    $q->selectRaw('COALESCE((monto_recaudado * 1.0) / NULLIF(meta_apoyo, 0), 0)')
                      ->from('campanas')
                      ->whereColumn('emprendedores.id', 'campanas.emprendedor_id')
                      ->where('estado', Campana::ESTADO_ACTIVA)
                      ->limit(1);
                }, 'porcentaje_cumplimiento')
                ->orderByDesc('porcentaje_cumplimiento')
                ->orderByDesc('created_at')
                ->orderByDesc('id');
        } else {
            // recientes
            $query->orderByDesc('created_at')->orderByDesc('id');
        }

        // 7. Paginación de resultados (9 por página)
        $paginador = $query->paginate(9)->withQueryString();

        // 8. Transformar los datos para el frontend
        $items = collect($paginador->items())->map(function (Emprendedor $emprendedor) use ($translator, $locale) {
            $campanaActiva = $emprendedor->campanas->first();
            $progreso = $this->calcularProgresoCampanaActiva($campanaActiva, $emprendedor);

            $descripcionTraducida = $translator->traducirCampo(
                entidadTipo: 'emprendedor',
                entidadId: $emprendedor->id,
                campo: 'descripcion',
                texto: $emprendedor->descripcion,
                idiomaDestino: $locale,
            );

            $tipoEtiqueta = $translator->traducirCampo(
                entidadTipo: 'emprendedor',
                entidadId: $emprendedor->id,
                campo: 'tipo_emprendimiento_etiqueta',
                texto: $emprendedor->tipo_emprendimiento?->etiqueta(),
                idiomaDestino: $locale,
            );

            $deptoEtiqueta = $translator->traducirCampo(
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

            return [
                'id' => $emprendedor->id,
                'slug' => $emprendedor->slug,
                'nombre' => $emprendedor->nombre,
                'apellidos' => $emprendedor->apellidos,
                'nombre_completo' => $emprendedor->nombreCompleto(),
                'descripcion' => Str::limit($descripcionTraducida, 140),
                'tipo_emprendimiento' => $emprendedor->tipo_emprendimiento?->value,
                'tipo_emprendimiento_etiqueta' => $tipoEtiqueta ?: $emprendedor->tipo_emprendimiento?->value,
                'departamento' => $emprendedor->departamento?->value,
                'departamento_etiqueta' => $deptoEtiqueta ?: $emprendedor->departamento?->value,
                'foto_portada' => $emprendedor->urlFotoPerfil(),
                'perfil_url' => $emprendedor->rutaPublica(),
                'publicaciones_count' => (int) $emprendedor->publicaciones_count,
                'campana_activa' => $campanaActiva ? [
                    'id' => $campanaActiva->id,
                    'titulo' => $campanaActivaTitulo,
                    'meta_apoyo' => $progreso['meta'],
                    'monto_recaudado' => $progreso['monto_recaudado'],
                    'fecha_fin' => $campanaActiva->fecha_fin,
                ] : null,
                'progreso' => $progreso,
            ];
        });

        // Reemplazar los items crudos del paginador por los formateados para no romper los links de paginación
        $paginadorData = $paginador->toArray();
        $paginadorData['data'] = $items->all();

        return Inertia::render('Turista/Emprendedores/Index', [
            'emprendedores' => $paginadorData,
            'filtros' => [
                'q' => $q,
                'departamento' => $departamento,
                'tipo_emprendimiento' => $tipo,
                'orden' => $orden,
            ],
            'catalogos' => [
                'departamentos' => Departamento::opcionesParaFormulario(),
                'tiposEmprendimiento' => TipoEmprendimiento::opcionesParaFormulario(),
            ],
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
     * Calcula el progreso de la campaña activa (PB-09 / T-29).
     */
    private function calcularProgresoCampanaActiva(?Campana $campana, Emprendedor $emprendedor): array
    {
        $meta = $campana
            ? (float) $campana->meta_apoyo
            : (float) ($emprendedor->meta_monto ?? 0);

        // Usamos el monto_recaudado de campana (mantenido por el observer de donaciones validadas)
        $montoValidado = $campana
            ? (float) ($campana->monto_recaudado ?? 0)
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
