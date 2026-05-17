<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RagService
{
    private string $knowledgeBasePath;

    public function __construct(
        private ChatEntityResolver $entityResolver,
    ) {
        $this->knowledgeBasePath = resource_path('data/rag/knowledge-base.json');
    }

    /**
     * Busca la respuesta más parecida a la pregunta del turista.
     */
    public function responder(string $pregunta, string $idioma = 'es', ?int $contextEmprendedorId = null): array
    {
        $idioma = $this->normalizarIdioma($idioma);

        $resolucionEntidad = $this->entityResolver->intentarResolver(
            $pregunta,
            $idioma,
            $contextEmprendedorId,
        );

        if ($resolucionEntidad !== null) {
            return $resolucionEntidad;
        }

        $base = $this->cargarBaseConocimiento();

        $items = $base['items'] ?? [];

        $fallback = $this->obtenerFallback($items, $idioma);

        if (trim($pregunta) === '') {
            return $fallback;
        }

        $preguntaNormalizada = $this->normalizarTexto($pregunta);
        $tokensPregunta = $this->obtenerTokens($preguntaNormalizada);

        $mejorItem = null;
        $mejorPuntaje = 0;

        foreach ($items as $item) {
            if (($item['category'] ?? '') === 'fallback') {
                continue;
            }

            $puntaje = $this->calcularPuntaje(
                $item,
                $preguntaNormalizada,
                $tokensPregunta,
                $idioma
            );

            if ($puntaje > $mejorPuntaje) {
                $mejorPuntaje = $puntaje;
                $mejorItem = $item;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Umbral mínimo
        |--------------------------------------------------------------------------
        |
        | Si el puntaje es muy bajo, respondemos con fallback.
        | Esto evita que el agente dé una respuesta incorrecta por coincidencias
        | débiles como "qué", "cómo" o "pago".
        |
        */

        if (! $mejorItem || $mejorPuntaje < 2) {
            return $fallback;
        }

        return [
            'question' => $pregunta,
            'answer' => $mejorItem['answers'][$idioma]
                ?? $mejorItem['answers']['es']
                ?? $fallback['answer'],
            'matched_id' => $mejorItem['id'] ?? null,
            'intent' => $mejorItem['intent'] ?? null,
            'category' => $mejorItem['category'] ?? null,
            'score' => $mejorPuntaje,
            'language' => $idioma,
            'actions' => $this->accionesDesdeIntent($mejorItem, $idioma),
        ];
    }

    /**
     * Acciones sugeridas según la intención del conocimiento estático.
     *
     * @return list<array{label: string, href: string, variant: string}>
     */
    private function accionesDesdeIntent(array $item, string $idioma): array
    {
        $intent = $item['intent'] ?? '';

        if (in_array($intent, ['como_donar', 'metodos_disponibles', 'pago_efectivo', 'pago_qr_bancario'], true)) {
            return [
                [
                    'label' => trans('chat.actions.explore', [], $idioma),
                    'href' => url('/#explorar'),
                    'variant' => 'primary',
                ],
            ];
        }

        if (in_array($intent, ['explorar_emprendedores', 'encontrar_punto'], true)) {
            return [
                [
                    'label' => trans('chat.actions.explore', [], $idioma),
                    'href' => url('/#explorar'),
                    'variant' => 'primary',
                ],
            ];
        }

        return [];
    }

    /**
     * Carga y decodifica el JSON de conocimiento.
     */
    private function cargarBaseConocimiento(): array
    {
        if (! File::exists($this->knowledgeBasePath)) {
            return [
                'items' => [],
            ];
        }

        $contenido = File::get($this->knowledgeBasePath);

        $base = json_decode($contenido, true);

        if (! is_array($base)) {
            return [
                'items' => [],
            ];
        }

        return $base;
    }

    /**
     * Calcula un puntaje simple de similitud.
     *
     * Reglas:
     * - Coincidencia exacta de keyword completa: +3
     * - Coincidencia de palabra individual de keyword: +1
     * - Coincidencia parcial con preguntas registradas: +2
     */
    private function calcularPuntaje(
        array $item,
        string $preguntaNormalizada,
        array $tokensPregunta,
        string $idioma
    ): int {
        $puntaje = 0;

        foreach (($item['keywords'] ?? []) as $keyword) {
            $keywordNormalizada = $this->normalizarTexto($keyword);

            if ($keywordNormalizada === '') {
                continue;
            }

            if (Str::contains($preguntaNormalizada, $keywordNormalizada)) {
                $puntaje += 3;
            }

            $tokensKeyword = $this->obtenerTokens($keywordNormalizada);

            foreach ($tokensKeyword as $token) {
                if (in_array($token, $tokensPregunta, true)) {
                    $puntaje += 1;
                }
            }
        }

        $preguntas = $item['questions'][$idioma]
            ?? $item['questions']['es']
            ?? [];

        foreach ($preguntas as $preguntaBase) {
            $preguntaBaseNormalizada = $this->normalizarTexto($preguntaBase);
            $tokensBase = $this->obtenerTokens($preguntaBaseNormalizada);

            $coincidencias = array_intersect($tokensPregunta, $tokensBase);

            if (count($coincidencias) >= 2) {
                $puntaje += 2;
            }
        }

        return $puntaje;
    }

    /**
     * Obtiene la respuesta fallback del JSON.
     */
    private function obtenerFallback(array $items, string $idioma): array
    {
        $fallback = collect($items)->firstWhere('category', 'fallback');

        return [
            'question' => null,
            'answer' => $fallback['answers'][$idioma]
                ?? $fallback['answers']['es']
                ?? 'No encontré una respuesta exacta para tu pregunta.',
            'matched_id' => $fallback['id'] ?? 'fallback',
            'intent' => $fallback['intent'] ?? 'sin_respuesta',
            'category' => 'fallback',
            'score' => 0,
            'language' => $idioma,
            'actions' => [
                [
                    'label' => trans('chat.actions.explore', [], $idioma),
                    'href' => url('/#explorar'),
                    'variant' => 'secondary',
                ],
            ],
        ];
    }

    /**
     * Normaliza texto:
     * - minúsculas
     * - sin acentos
     * - sin símbolos innecesarios
     */
    private function normalizarTexto(string $texto): string
    {
        $texto = Str::ascii($texto);
        $texto = mb_strtolower($texto, 'UTF-8');

        return trim($texto);
    }

    /**
     * Convierte texto en tokens útiles.
     */
    private function obtenerTokens(string $texto): array
    {
        preg_match_all('/[\pL\pN]+/u', $texto, $matches);

        $tokens = $matches[0] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Stopwords básicas
        |--------------------------------------------------------------------------
        |
        | Evitamos que palabras demasiado comunes influyan mucho en la búsqueda.
        |
        */

        $stopwords = [
            'el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas',
            'de', 'del', 'a', 'en', 'y', 'o', 'que', 'como', 'por',
            'para', 'con', 'mi', 'me', 'es', 'the', 'a', 'an', 'to',
            'and', 'or', 'is', 'are', 'how', 'what', 'can', 'i',
        ];

        return collect($tokens)
            ->filter(fn ($token) => mb_strlen($token) >= 3)
            ->reject(fn ($token) => in_array($token, $stopwords, true))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Asegura que el idioma sea soportado por el MVP.
     */
    private function normalizarIdioma(?string $idioma): string
    {
        return in_array($idioma, ['es', 'en'], true)
            ? $idioma
            : 'es';
    }
}