<?php

namespace App\Services;

use App\Http\Requests\Emprendedor\UpdatePerfilPublicoRequest;
use App\Models\Emprendedor;
use Illuminate\Support\Facades\Storage;

/**
 * E-06 — Actualización del perfil público por el emprendedor autenticado.
 */
class EmprendedorPerfilService
{
    public function __construct(
        protected ImageStorageService $imageStorageService,
        protected EmprendedorMediosService $emprendedorMediosService,
    ) {
    }

    public function actualizar(Emprendedor $emprendedor, UpdatePerfilPublicoRequest $request): Emprendedor
    {
        $data = collect($request->validated())->except([
            'fotografia',
            'foto_empresa',
            'galeria',
            'galeria_nuevas',
            'galeria_conservar',
            'video',
            'video_enlace',
            'quitar_video',
        ])->all();

        if ($request->hasFile('fotografia')) {
            if ($emprendedor->fotografia) {
                Storage::disk('public')->delete($emprendedor->fotografia);
            }

            $data['fotografia'] = $this->imageStorageService->storePublicImageAsWebp(
                $request->file('fotografia'),
                'emprendedores/fotografias',
            );
        }

        $emprendedor->update($data);
        $this->emprendedorMediosService->sincronizarDesdeRequest($emprendedor->fresh(), $request);

        return $emprendedor->fresh();
    }
}
