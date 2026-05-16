<?php

namespace App\Services;

use App\Models\Traduccion;
use Google\Cloud\Translate\V2\TranslateClient;
use Illuminate\Support\Facades\Log;

class GoogleTranslationService
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

        if (! config('googletranslate.enabled') || blank(config('googletranslate.project_id'))) {
            return $texto;
        }

        try {
            $textoTraducido = $this->traducirConGoogle(
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
                'proveedor' => 'google',
            ]);

            return $textoTraducido;
        } catch (\Throwable $e) {
            Log::warning('No se pudo traducir texto con Google Translation.', [
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

    private function traducirConGoogle(
        string $texto,
        string $idiomaDestino,
        string $idiomaOrigen = 'es',
    ): string {
        $clientConfig = [
            'projectId' => config('googletranslate.project_id'),
        ];

        if (filled(config('googletranslate.credentials'))) {
            $clientConfig['keyFilePath'] = config('googletranslate.credentials');
        }

        $translate = new TranslateClient($clientConfig);

        $resultado = $translate->translate($texto, [
            'source' => $idiomaOrigen,
            'target' => $idiomaDestino,
            'format' => 'text',
        ]);

        return (string) ($resultado['text'] ?? '');
    }
}