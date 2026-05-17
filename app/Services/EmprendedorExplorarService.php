<?php

namespace App\Services;

use App\Enums\Departamento;
use App\Enums\TipoEmprendimiento;
use App\Models\Emprendedor;
use App\Models\Punto;
use App\Support\RedesSocialesEmprendedor;
use Illuminate\Support\Str;

class EmprendedorExplorarService
{
    /**
     * @param  array{q?: string, tipo_emprendimiento?: string, departamento?: string, punto_id?: int|string|null}  $filtros
     * @return list<array<string, mixed>>
     */
    public function listarTarjetas(array $filtros = []): array
    {
        $query = Emprendedor::query()
            ->where('estado', 'activo')
            ->with(['puntos:id,nombre,slug'])
            ->orderBy('nombre')
            ->orderBy('apellidos');

        if (! empty($filtros['q'])) {
            $termino = '%'.Str::lower(trim((string) $filtros['q'])).'%';
            $query->where(function ($q) use ($termino): void {
                $q->whereRaw('LOWER(nombre) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(apellidos) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(descripcion) LIKE ?', [$termino]);
            });
        }

        if (! empty($filtros['tipo_emprendimiento'])) {
            $query->where('tipo_emprendimiento', $filtros['tipo_emprendimiento']);
        }

        if (! empty($filtros['departamento'])) {
            $query->where('departamento', $filtros['departamento']);
        }

        if (! empty($filtros['punto_id'])) {
            $query->whereHas('puntos', fn ($q) => $q->where('puntos_fisicos.id', (int) $filtros['punto_id']));
        }

        return $query
            ->get()
            ->map(fn (Emprendedor $emprendedor) => $this->formatearTarjeta($emprendedor))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function formatearTarjeta(Emprendedor $emprendedor): array
    {
        $galeria = is_array($emprendedor->galeria) ? $emprendedor->galeria : [];
        $redes = RedesSocialesEmprendedor::paraFrontend(
            $emprendedor->whatsapp,
            $emprendedor->instagram,
            $emprendedor->facebook,
            $emprendedor->tiktok,
        );

        return [
            'id' => $emprendedor->id,
            'nombre' => $emprendedor->nombre,
            'apellidos' => $emprendedor->apellidos,
            'descripcion' => Str::limit((string) $emprendedor->descripcion, 140),
            'tipo_emprendimiento' => $emprendedor->tipo_emprendimiento?->value,
            'tipo_emprendimiento_etiqueta' => $emprendedor->tipo_emprendimiento?->etiqueta(),
            'departamento' => $emprendedor->departamento?->value,
            'departamento_etiqueta' => $emprendedor->departamento?->etiqueta(),
            'foto_portada' => $emprendedor->urlFotoPerfil(),
            'tiene_video' => filled($emprendedor->video_url),
            'cantidad_fotos' => count($galeria),
            'tiene_redes' => collect($redes)->filter()->isNotEmpty(),
            'puntos' => $emprendedor->puntos
                ->map(fn ($punto) => [
                    'id' => $punto->id,
                    'nombre' => $punto->nombre,
                    'slug' => $punto->slug,
                ])
                ->values()
                ->all(),
            'perfil_url' => route('turista.emprendedor.show', $emprendedor->id),
        ];
    }

    /**
     * @return list<array{id: int, nombre: string, slug: string}>
     */
    public function puntosActivos(): array
    {
        return Punto::query()
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'slug'])
            ->map(fn (Punto $punto) => [
                'id' => $punto->id,
                'nombre' => $punto->nombre,
                'slug' => $punto->slug,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{filtros: array<string, mixed>, catalogos: array<string, mixed>}
     */
    public function filtrosDesdeRequest(\Illuminate\Http\Request $request): array
    {
        $filtros = [
            'q' => $request->string('q')->trim()->toString(),
            'tipo_emprendimiento' => $request->string('tipo_emprendimiento')->toString(),
            'departamento' => $request->string('departamento')->toString(),
            'punto_id' => $request->input('punto_id'),
        ];

        if ($filtros['tipo_emprendimiento'] !== ''
            && ! in_array($filtros['tipo_emprendimiento'], TipoEmprendimiento::valores(), true)) {
            $filtros['tipo_emprendimiento'] = '';
        }

        if ($filtros['departamento'] !== ''
            && ! in_array($filtros['departamento'], Departamento::valores(), true)) {
            $filtros['departamento'] = '';
        }

        if ($filtros['punto_id'] !== null && $filtros['punto_id'] !== '') {
            $filtros['punto_id'] = (int) $filtros['punto_id'];
        } else {
            $filtros['punto_id'] = null;
        }

        return [
            'filtros' => $filtros,
            'catalogos' => [
                'tiposEmprendimiento' => TipoEmprendimiento::opcionesParaFormulario(),
                'departamentos' => Departamento::opcionesParaFormulario(),
                'puntos' => $this->puntosActivos(),
            ],
        ];
    }
}
