<?php

namespace App\Services;

use App\Models\Traduccion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LibreTranslationService
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

        if (! config('libretranslate.enabled')) {
            return $texto;
        }

        try {
            $textoTraducido = $this->traducirConLibreTranslate(
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
                'proveedor' => 'libretranslate',
            ]);

            return $textoTraducido;
        } catch (\Throwable $e) {
            Log::warning('No se pudo traducir texto con LibreTranslate.', [
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

    private function traducirConLibreTranslate(
        string $texto,
        string $idiomaDestino,
        string $idiomaOrigen = 'es',
    ): string {
        $payload = [
            'q' => $texto,
            'source' => $idiomaOrigen,
            'target' => $idiomaDestino,
            'format' => 'text',
        ];

        if (filled(config('libretranslate.api_key'))) {
            $payload['api_key'] = config('libretranslate.api_key');
        }

        $response = Http::timeout(20)
            ->asJson()
            ->post(config('libretranslate.api_url'), $payload);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'LibreTranslate respondió con estado '.$response->status()
            );
        }

        return (string) data_get($response->json(), 'translatedText', '');
    }
}