<?php

namespace App\Http\Requests\Admin\Concerns;

use Illuminate\Support\Arr;

trait ValidaMediosEmprendedor
{
    /**
     * @return array<string, mixed>
     */
    protected function reglasMediosEmprendedor(): array
    {
        return [
            'foto_empresa' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'galeria' => ['nullable', 'array', 'max:4'],
            'galeria.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'galeria_nuevas' => ['nullable', 'array', 'max:4'],
            'galeria_nuevas.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'galeria_conservar' => ['nullable', 'array', 'max:4'],
            'galeria_conservar.*' => ['string', 'max:500'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'max:15360'],
            'video_enlace' => ['nullable', 'url', 'max:500'],
            'quitar_video' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function mensajesMediosEmprendedor(): array
    {
        return [
            'foto_empresa.image' => 'La foto de la empresa debe ser una imagen válida.',
            'foto_empresa.max' => 'La foto de la empresa no debe pesar más de 2 MB.',
            'galeria.max' => 'La galería admite como máximo 4 imágenes.',
            'galeria.*.max' => 'Cada imagen de la galería no debe pesar más de 2 MB.',
            'galeria_nuevas.max' => 'La galería admite como máximo 4 imágenes.',
            'video.max' => 'El video no debe pesar más de 15 MB.',
            'video_enlace.url' => 'El enlace del video debe ser una URL válida (YouTube o Vimeo).',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->hasFile('video') && $this->filled('video_enlace')) {
                $validator->errors()->add(
                    'video_enlace',
                    'Elegí subir un archivo de video o pegar un enlace, no ambos a la vez.',
                );
            }

            $nuevas = count(Arr::wrap($this->file('galeria')))
                + count(Arr::wrap($this->file('galeria_nuevas')));
            $conservar = count((array) $this->input('galeria_conservar', []));

            if ($nuevas + $conservar > 4) {
                $validator->errors()->add(
                    'galeria',
                    'La galería no puede tener más de 4 imágenes en total.',
                );
            }
        });
    }
}
