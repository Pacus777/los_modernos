<?php

namespace App\Services;

use App\Enums\EmprendedorPostEstado;
use App\Enums\EmprendedorPostReaccionTipo;
use App\Models\EmprendedorPost;
use App\Models\EmprendedorPostReaccion;
use App\Models\Visitante;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PostReaccionService
{
    public function __construct(
        private readonly VisitanteService $visitanteService,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function postsParaPerfil(int $emprendedorId, ?Visitante $visitante): array
    {
        $posts = EmprendedorPost::query()
            ->where('emprendedor_id', $emprendedorId)
            ->publicados()
            ->get();

        if ($posts->isEmpty()) {
            return [];
        }

        $postIds = $posts->pluck('id');

        $conteos = EmprendedorPostReaccion::query()
            ->whereIn('emprendedor_post_id', $postIds)
            ->selectRaw('emprendedor_post_id, tipo, count(*) as total')
            ->groupBy('emprendedor_post_id', 'tipo')
            ->get()
            ->groupBy('emprendedor_post_id');

        $miReaccionPorPost = collect();

        if ($visitante) {
            $miReaccionPorPost = EmprendedorPostReaccion::query()
                ->whereIn('emprendedor_post_id', $postIds)
                ->where('actor_type', $visitante->getMorphClass())
                ->where('actor_id', $visitante->id)
                ->get()
                ->keyBy('emprendedor_post_id');
        }

        return $posts->map(function (EmprendedorPost $post) use ($conteos, $miReaccionPorPost) {
            $filas = $conteos->get($post->id, collect());
            $totales = $this->totalesDesdeFilas($filas);
            $mi = $miReaccionPorPost->get($post->id);

            return [
                'id' => $post->id,
                'tipo' => $post->tipo->value,
                'contenido' => $post->contenido,
                'media_url' => $post->urlMediaPublica(),
                'enlace_externo' => $post->enlace_externo,
                'publicado_en' => $post->publicado_en?->toIso8601String(),
                'totales' => $totales,
                'mi_reaccion' => $mi?->tipo->value,
            ];
        })->values()->all();
    }

    /**
     * @return array{post_id: int, mi_reaccion: ?string, totales: array<string, int>}
     */
    public function alternar(Request $request, EmprendedorPost $post, EmprendedorPostReaccionTipo $tipo): array
    {
        $this->asegurarPostPublico($post);

        $visitante = $this->visitanteService->asegurarEnSesion($request);

        $reaccion = EmprendedorPostReaccion::query()
            ->where('emprendedor_post_id', $post->id)
            ->where('actor_type', $visitante->getMorphClass())
            ->where('actor_id', $visitante->id)
            ->first();

        if ($reaccion && $reaccion->tipo === $tipo) {
            $reaccion->delete();
        } elseif ($reaccion) {
            $reaccion->update(['tipo' => $tipo]);
        } else {
            EmprendedorPostReaccion::query()->create([
                'emprendedor_post_id' => $post->id,
                'actor_type' => $visitante->getMorphClass(),
                'actor_id' => $visitante->id,
                'tipo' => $tipo,
            ]);
        }

        return [
            'post_id' => $post->id,
            'mi_reaccion' => $this->miReaccionValor($post, $visitante),
            'totales' => $this->totalesDelPost($post),
        ];
    }

    public function asegurarPostPublico(EmprendedorPost $post): void
    {
        if ($post->estado !== EmprendedorPostEstado::Publicado || $post->publicado_en === null) {
            abort(404);
        }

        $post->loadMissing('emprendedor');

        if ($post->emprendedor?->estado !== 'activo') {
            abort(404);
        }
    }

    /**
     * @return array<string, int>
     */
    public function totalesDelPost(EmprendedorPost $post): array
    {
        $filas = EmprendedorPostReaccion::query()
            ->where('emprendedor_post_id', $post->id)
            ->selectRaw('tipo, count(*) as total')
            ->groupBy('tipo')
            ->get();

        return $this->totalesDesdeFilas($filas);
    }

    private function miReaccionValor(EmprendedorPost $post, Visitante $visitante): ?string
    {
        $reaccion = EmprendedorPostReaccion::query()
            ->where('emprendedor_post_id', $post->id)
            ->where('actor_type', $visitante->getMorphClass())
            ->where('actor_id', $visitante->id)
            ->first();

        return $reaccion?->tipo->value;
    }

    /**
     * @param  Collection<int, object>  $filas
     * @return array<string, int>
     */
    private function totalesDesdeFilas(Collection $filas): array
    {
        $totales = array_fill_keys(EmprendedorPostReaccionTipo::valores(), 0);

        foreach ($filas as $fila) {
            $clave = $fila->tipo instanceof EmprendedorPostReaccionTipo
                ? $fila->tipo->value
                : (string) $fila->tipo;
            $totales[$clave] = (int) $fila->total;
        }

        return $totales;
    }
}
