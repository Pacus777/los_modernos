<?php

namespace App\Services;

use App\Models\Emprendedor;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EmprendedorMediosService
{
    public function __construct(
        protected ImageStorageService $imageStorageService,
    ) {
    }

    /**
     * Procesa subidas y cambios de medios tras crear o actualizar un emprendedor.
     */
    public function sincronizarDesdeRequest(Emprendedor $emprendedor, Request $request): void
    {
        $cambios = [];

        if ($request->hasFile('foto_empresa')) {
            if ($emprendedor->foto_empresa) {
                Storage::disk('public')->delete($emprendedor->foto_empresa);
            }

            $cambios['foto_empresa'] = $this->imageStorageService->storePublicImageAsWebp(
                $request->file('foto_empresa'),
                'emprendedores/empresa',
            );
        }

        if ($request->has('galeria_conservar') || $request->hasFile('galeria') || $request->hasFile('galeria_nuevas')) {
            $cambios['galeria'] = $this->resolverGaleria($emprendedor, $request);
        }

        if ($request->boolean('quitar_video') && $emprendedor->video_url) {
            $this->eliminarVideoAlmacenado($emprendedor->video_url);
            $cambios['video_url'] = null;
        } elseif ($request->hasFile('video')) {
            if ($emprendedor->video_url && ! $this->esEnlaceExterno($emprendedor->video_url)) {
                $this->eliminarVideoAlmacenado($emprendedor->video_url);
            }

            $cambios['video_url'] = $request->file('video')->store('emprendedores/videos', 'public');
        } elseif ($request->filled('video_enlace')) {
            if ($emprendedor->video_url && ! $this->esEnlaceExterno($emprendedor->video_url)) {
                $this->eliminarVideoAlmacenado($emprendedor->video_url);
            }

            $cambios['video_url'] = trim((string) $request->input('video_enlace'));
        }

        if ($cambios !== []) {
            $emprendedor->update($cambios);
        }
    }

    /**
     * @return list<string>
     */
    private function resolverGaleria(Emprendedor $emprendedor, Request $request): array
    {
        $existente = is_array($emprendedor->galeria) ? $emprendedor->galeria : [];
        $conservar = array_values(array_filter(
            (array) $request->input('galeria_conservar', []),
            fn ($ruta) => is_string($ruta) && $ruta !== '' && in_array($ruta, $existente, true),
        ));

        $eliminadas = array_diff($existente, $conservar);
        foreach ($eliminadas as $ruta) {
            Storage::disk('public')->delete($ruta);
        }

        $nuevas = $request->file('galeria_nuevas') ?? $request->file('galeria') ?? [];
        if (! is_array($nuevas)) {
            $nuevas = [$nuevas];
        }

        $rutasNuevas = [];
        foreach ($nuevas as $archivo) {
            if ($archivo instanceof UploadedFile) {
                $rutasNuevas[] = $this->imageStorageService->storePublicImageAsWebp(
                    $archivo,
                    'emprendedores/galeria',
                );
            }
        }

        return array_slice(array_values(array_merge($conservar, $rutasNuevas)), 0, 4);
    }

    private function eliminarVideoAlmacenado(string $ruta): void
    {
        if (! $this->esEnlaceExterno($ruta)) {
            Storage::disk('public')->delete($ruta);
        }
    }

    public function esEnlaceExterno(string $valor): bool
    {
        return str_starts_with($valor, 'http://') || str_starts_with($valor, 'https://');
    }

    public static function urlAlmacenPublico(?string $ruta): ?string
    {
        if ($ruta === null || $ruta === '') {
            return null;
        }

        if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://')) {
            return $ruta;
        }

        return '/storage/'.$ruta;
    }

    /**
     * @return array{titulo: string, tipo: 'none'|'archivo'|'embed', src: string|null, embed_url: string|null}
     */
    public static function presentacionVideo(?string $videoUrl): array
    {
        if ($videoUrl === null || $videoUrl === '') {
            return [
                'tipo' => 'none',
                'src' => null,
                'embed_url' => null,
            ];
        }

        if (str_starts_with($videoUrl, 'http://') || str_starts_with($videoUrl, 'https://')) {
            $embed = self::embedDesdeEnlace($videoUrl);

            return [
                'tipo' => $embed ? 'embed' : 'none',
                'src' => $videoUrl,
                'embed_url' => $embed,
            ];
        }

        return [
            'tipo' => 'archivo',
            'src' => self::urlAlmacenPublico($videoUrl),
            'embed_url' => null,
        ];
    }

    public static function embedDesdeEnlace(string $url): ?string
    {
        if (preg_match(
            '/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
            $url,
            $coincidencias,
        )) {
            return 'https://www.youtube.com/embed/'.$coincidencias[1];
        }

        if (preg_match('/vimeo\.com\/(\d+)/', $url, $coincidencias)) {
            return 'https://player.vimeo.com/video/'.$coincidencias[1];
        }

        return null;
    }
}
