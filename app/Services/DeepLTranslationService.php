<?php

namespace App\Services;

use App\Models\Traduccion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeepLTranslationService
{
    public function traducirCampo(
        string $entidadTipo,
        int|string|null $entidadId,
        string $campo,
        ?string $texto,
        string $idiomaDestino,
        string $idiomaOrigen = 'es',
    ): string {
        $texto = trim((string) $texto);
        $idiomaDestino = strtolower($idiomaDestino);
        $idiomaOrigen = strtolower($idiomaOrigen);

        if ($texto === '') {
            return '';
        }

        if ($idiomaDestino === $idiomaOrigen) {
            return $texto;
        }

        $hash = Traduccion::hashTexto($texto);

        $traduccionExistente = Traduccion::query()
            ->where('entidad_tipo', $entidadTipo)
            ->where('entidad_id', $entidadId)
            ->where('campo', $campo)
            ->where('idioma_origen', $idiomaOrigen)
            ->where('idioma_destino', $idiomaDestino)
            ->where('texto_original_hash', $hash)
            ->first();

        if ($traduccionExistente) {
            return $traduccionExistente->texto_traducido;
        }

        if (! config('deepl.enabled') || blank(config('deepl.auth_key'))) {
            return $texto;
        }

        try {
            $textoTraducido = $this->traducirConDeepL(
                texto: $texto,
                idiomaDestino: $idiomaDestino,
                idiomaOrigen: $idiomaOrigen,
            );

            if ($textoTraducido === '') {
                return $texto;
            }

            Traduccion::query()->create([
                'entidad_tipo' => $entidadTipo,
                'entidad_id' => $entidadId,
                'campo' => $campo,
                'idioma_origen' => $idiomaOrigen,
                'idioma_destino' => $idiomaDestino,
                'texto_original_hash' => $hash,
                'texto_original' => $texto,
                'texto_traducido' => $textoTraducido,
                'proveedor' => 'deepl',
            ]);

            return $textoTraducido;
        } catch (\Throwable $e) {
            Log::warning('No se pudo traducir texto con DeepL.', [
                'entidad_tipo' => $entidadTipo,
                'entidad_id' => $entidadId,
                'campo' => $campo,
                'idioma_origen' => $idiomaOrigen,
                'idioma_destino' => $idiomaDestino,
                'error' => $e->getMessage(),
            ]);

            return $texto;
        }
    }

    public function traducirTextoLibre(
        ?string $texto,
        string $idiomaDestino,
        string $idiomaOrigen = 'es',
    ): string {
        return $this->traducirCampo(
            entidadTipo: 'texto_libre',
            entidadId: null,
            campo: 'contenido',
            texto: $texto,
            idiomaDestino: $idiomaDestino,
            idiomaOrigen: $idiomaOrigen,
        );
    }

    private function traducirConDeepL(
        string $texto,
        string $idiomaDestino,
        string $idiomaOrigen = 'es',
    ): string {
        $response = Http::asForm()
            ->timeout(10)
            ->post(config('deepl.api_url'), [
                'auth_key' => config('deepl.auth_key'),
                'text' => $texto,
                'source_lang' => Str::upper($idiomaOrigen),
                'target_lang' => $this->mapearIdiomaDeepL($idiomaDestino),
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'DeepL respondió con estado '.$response->status()
            );
        }

        return (string) data_get($response->json(), 'translations.0.text', '');
    }

    private function mapearIdiomaDeepL(string $idioma): string
    {
        return match (strtolower($idioma)) {
            'en' => 'EN-US',
            'pt' => 'PT-BR',
            default => Str::upper($idioma),
        };
    }
}