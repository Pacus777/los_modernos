<?php

namespace App\Services;

use App\Models\Campana;
use App\Models\EmprendedorPost;
use App\Models\EmprendedorSeguidor;
use App\Models\TuristaNotificacion;
use App\Models\User;
use App\Enums\EmprendedorPostEstado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TuristaNotificacionService
{
    public function resumenParaNavbar(User $user): array
    {
        $items = TuristaNotificacion::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn (TuristaNotificacion $n) => $this->formatear($n));

        return [
            'no_leidas' => $this->contarNoLeidas($user),
            'items' => $items->all(),
        ];
    }

    public function listar(User $user, int $porPagina = 15): LengthAwarePaginator
    {
        return TuristaNotificacion::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($porPagina)
            ->through(fn (TuristaNotificacion $n) => $this->formatear($n));
    }

    public function contarNoLeidas(User $user): int
    {
        return TuristaNotificacion::query()
            ->where('user_id', $user->id)
            ->whereNull('leida_at')
            ->count();
    }

    public function marcarLeida(User $user, int $notificacionId): bool
    {
        $notificacion = TuristaNotificacion::query()
            ->where('user_id', $user->id)
            ->whereKey($notificacionId)
            ->first();

        if (! $notificacion || $notificacion->estaLeida()) {
            return false;
        }

        $notificacion->update(['leida_at' => now()]);

        return true;
    }

    public function marcarTodasLeidas(User $user): int
    {
        return TuristaNotificacion::query()
            ->where('user_id', $user->id)
            ->whereNull('leida_at')
            ->update(['leida_at' => now()]);
    }

    public function registrarNuevoPostParaSeguidores(EmprendedorPost $post): void
    {
        $post->loadMissing('emprendedor');

        if ($post->estado !== EmprendedorPostEstado::Publicado || $post->publicado_en === null) {
            return;
        }

        $emprendedor = $post->emprendedor;
        if (! $emprendedor) {
            return;
        }

        EmprendedorSeguidor::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->whereNotNull('email')
            ->whereNotNull('confirmado_en')
            ->whereNotNull('token_unsub')
            ->orderBy('id')
            ->chunkById(50, function ($seguidores) use ($emprendedor, $post): void {
                foreach ($seguidores as $seguimiento) {
                    $user = User::where('email', $seguimiento->email)->first();
                    
                    if ($user && ! $user->tieneAlgunoDeEstosRoles(['admin', 'cajero', 'emprendedor'])) {
                        TuristaNotificacion::firstOrCreate(
                            [
                                'user_id' => $user->id,
                                'tipo' => TuristaNotificacion::TIPO_NUEVO_POST,
                                'emprendedor_post_id' => $post->id,
                            ],
                            [
                                'emprendedor_id' => $emprendedor->id,
                                'titulo' => 'Nueva publicación',
                                'mensaje' => "{$emprendedor->nombreCompleto()} compartió una nueva publicación.",
                                'url' => route('turista.emprendedor.show', $emprendedor->id),
                            ]
                        );
                    }
                }
            });
    }

    public function registrarNuevaMetaParaSeguidores(Campana $campana): void
    {
        $campana->loadMissing('emprendedor');

        if (! $campana->estaVisibleEnPerfilTurista()) {
            return;
        }

        $emprendedor = $campana->emprendedor;
        if (! $emprendedor) {
            return;
        }

        EmprendedorSeguidor::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->whereNotNull('email')
            ->whereNotNull('confirmado_en')
            ->whereNotNull('token_unsub')
            ->orderBy('id')
            ->chunkById(50, function ($seguidores) use ($emprendedor, $campana): void {
                foreach ($seguidores as $seguimiento) {
                    $user = User::where('email', $seguimiento->email)->first();

                    if ($user && ! $user->tieneAlgunoDeEstosRoles(['admin', 'cajero', 'emprendedor'])) {
                        TuristaNotificacion::firstOrCreate(
                            [
                                'user_id' => $user->id,
                                'tipo' => TuristaNotificacion::TIPO_NUEVA_META,
                                'campana_id' => $campana->id,
                            ],
                            [
                                'emprendedor_id' => $emprendedor->id,
                                'titulo' => 'Nueva meta de apoyo',
                                'mensaje' => "{$emprendedor->nombreCompleto()} publicó una nueva meta: {$campana->titulo}.",
                                'url' => route('turista.emprendedor.show', $emprendedor->id),
                            ]
                        );
                    }
                }
            });
    }

    private function formatear(TuristaNotificacion $notificacion): array
    {
        return [
            'id' => $notificacion->id,
            'tipo' => $notificacion->tipo,
            'titulo' => $notificacion->titulo,
            'mensaje' => $notificacion->mensaje,
            'url' => $notificacion->url,
            'leida' => $notificacion->estaLeida(),
            'created_at' => $notificacion->created_at?->toIso8601String(),
            'created_at_humano' => $notificacion->created_at?->diffForHumans(),
        ];
    }
}
