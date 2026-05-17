<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Normalización de texto para el chat y búsqueda por palabras clave.
 */
final class TextoPregunta
{
    public static function normalizar(string $texto): string
    {
        $texto = Str::ascii($texto);

        return trim(mb_strtolower($texto, 'UTF-8'));
    }

    /**
     * @return list<string>
     */
    public static function tokens(string $texto): array
    {
        preg_match_all('/[\pL\pN]+/u', self::normalizar($texto), $matches);

        $tokens = $matches[0] ?? [];

        $stopwords = [
            'el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas',
            'de', 'del', 'a', 'en', 'y', 'o', 'que', 'como', 'por',
            'para', 'con', 'mi', 'me', 'es', 'le', 'se', 'su', 'sus',
            'the', 'a', 'an', 'to', 'and', 'or', 'is', 'are', 'how',
            'what', 'can', 'i', 'want', 'vi', 'ver', 'vi', 'saw',
        ];

        return collect($tokens)
            ->filter(fn (string $token) => mb_strlen($token) >= 3)
            ->reject(fn (string $token) => in_array($token, $stopwords, true))
            ->unique()
            ->values()
            ->all();
    }

    public static function contieneAlguna(string $texto, array $palabras): bool
    {
        $normalizado = self::normalizar($texto);

        foreach ($palabras as $palabra) {
            $p = self::normalizar($palabra);
            if ($p !== '' && Str::contains($normalizado, $p)) {
                return true;
            }
        }

        return false;
    }
}
