<?php

namespace App\Observers;

use App\Enums\EmprendedorPostEstado;
use App\Events\EmprendedorPostPublicado;
use App\Models\EmprendedorPost;
use Illuminate\Support\Facades\Storage;

/**
 * Reglas al publicar u ocultar posts del feed (S4-02).
 */
class EmprendedorPostObserver
{
    public function saving(EmprendedorPost $post): void
    {
        if ($post->estado === EmprendedorPostEstado::Publicado) {
            if ($post->publicado_en === null) {
                $post->publicado_en = now();
            }

            return;
        }

        if ($post->isDirty('estado')) {
            $post->publicado_en = null;
        }
    }

    public function saved(EmprendedorPost $post): void
    {
        if ($post->estado !== EmprendedorPostEstado::Publicado) {
            return;
        }

        if ($post->wasRecentlyCreated || $post->wasChanged('estado')) {
            EmprendedorPostPublicado::dispatch($post);
        }
    }

    public function deleted(EmprendedorPost $post): void
    {
        if (! filled($post->media_path)) {
            return;
        }

        Storage::disk('public')->delete($post->media_path);
    }
}
