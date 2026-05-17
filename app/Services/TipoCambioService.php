<?php

namespace App\Services;

use App\Models\TipoCambio;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TipoCambioService
{
    public function obtenerUsdBobReferencial(): array
    {
        if (! config('tipocambio.enabled', true)) {
            return $this->respuestaDesdeFallback();
        }

        $cacheMinutes = max((int) config('tipocambio.cache_minutes', 60), 1);

        return Cache::remember(
            'tipo_cambio_usd_bob_referencial',
            now()->addMinutes($cacheMinutes),
            fn () => $this->resolverTipoCambio()
        );
    }

    private function resolverTipoCambio(): array
    {
        try {
            $desdeApi = $this->obtenerDesdeDolarApi();

            if ($desdeApi !== null) {
                $registro = TipoCambio::query()->create([
                    'base' => 'USD',
                    'quote' => 'BOB',
                    'compra' => $desdeApi['compra'],
                    'venta' => $desdeApi['venta'],
                    'promedio' => $desdeApi['promedio'],
                    'fuente' => $desdeApi['fuente'],
                    'tipo' => 'referencial',
                    'consultado_en' => now(),
                    'metadata' => $desdeApi['metadata'] ?? [],
                ]);

                return $this->respuestaDesdeRegistro($registro);
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo obtener tipo de cambio referencial desde API.', [
                'error' => $e->getMessage(),
            ]);
        }

        $ultimo = TipoCambio::query()
            ->where('base', 'USD')
            ->where('quote', 'BOB')
            ->where('tipo', 'referencial')
            ->latest('consultado_en')
            ->first();

        if ($ultimo) {
            return $this->respuestaDesdeRegistro($ultimo, 'Último valor guardado');
        }

        return $this->respuestaDesdeFallback();
    }

    private function obtenerDesdeDolarApi(): ?array
    {
        $response = Http::timeout(8)
            ->acceptJson()
            ->get(config('tipocambio.api_url'));

        if (! $response->successful()) {
            throw new \RuntimeException('API respondió con estado '.$response->status());
        }

        $data = $response->json();

        /*
        | La API puede devolver una lista de cotizaciones.
        | Buscamos una opción referencial/paralela si existe.
        */

        $items = is_array($data) && array_is_list($data)
            ? $data
            : [$data];

        $item = collect($items)->first(function ($row) {
            $nombre = strtolower((string) data_get($row, 'nombre', ''));
            $casa = strtolower((string) data_get($row, 'casa', ''));
            $tipo = strtolower((string) data_get($row, 'tipo', ''));

            return str_contains($nombre, 'blue')
                || str_contains($nombre, 'paralelo')
                || str_contains($nombre, 'referencial')
                || str_contains($casa, 'blue')
                || str_contains($casa, 'paralelo')
                || str_contains($tipo, 'referencial');
        }) ?? collect($items)->first();

        if (! $item) {
            return null;
        }

        $compra = $this->extraerNumero(
            data_get($item, 'compra')
            ?? data_get($item, 'buy')
            ?? data_get($item, 'bid')
        );

        $venta = $this->extraerNumero(
            data_get($item, 'venta')
            ?? data_get($item, 'sell')
            ?? data_get($item, 'ask')
            ?? data_get($item, 'promedio')
            ?? data_get($item, 'rate')
        );

        if ($venta <= 0 && $compra > 0) {
            $venta = $compra;
        }

        if ($venta <= 0) {
            return null;
        }

        $promedio = $compra > 0
            ? round(($compra + $venta) / 2, 4)
            : $venta;

        return [
            'compra' => $compra > 0 ? $compra : null,
            'venta' => $venta,
            'promedio' => $promedio,
            'fuente' => 'DolarApi Bolivia',
            'metadata' => [
                'raw' => $item,
                'api_url' => config('tipocambio.api_url'),
            ],
        ];
    }

    private function respuestaDesdeRegistro(TipoCambio $tipoCambio, ?string $label = null): array
    {
        $usdToBob = (float) $tipoCambio->venta;

        if ($usdToBob <= 0) {
            return $this->respuestaDesdeFallback();
        }

        return [
            'activo' => true,
            'usd_to_bob' => round($usdToBob, 4),
            'usd_por_bs' => round(1 / $usdToBob, 6),
            'compra' => $tipoCambio->compra ? (float) $tipoCambio->compra : null,
            'venta' => (float) $tipoCambio->venta,
            'promedio' => $tipoCambio->promedio ? (float) $tipoCambio->promedio : null,
            'label' => $label ?? 'Tipo de cambio referencial',
            'source' => $tipoCambio->fuente,
            'updated_at' => optional($tipoCambio->consultado_en)->toISOString(),
        ];
    }

    private function respuestaDesdeFallback(): array
    {
        $usdToBob = (float) config('tipocambio.fallback_usd_to_bob', 10.25);

        if ($usdToBob <= 0) {
            $usdToBob = 10.25;
        }

        return [
            'activo' => true,
            'usd_to_bob' => round($usdToBob, 4),
            'usd_por_bs' => round(1 / $usdToBob, 6),
            'compra' => null,
            'venta' => round($usdToBob, 4),
            'promedio' => null,
            'label' => 'Tipo de cambio referencial de respaldo',
            'source' => 'Fallback local',
            'updated_at' => now()->toISOString(),
        ];
    }

    private function extraerNumero(mixed $valor): float
    {
        if ($valor === null) {
            return 0.0;
        }

        if (is_numeric($valor)) {
            return (float) $valor;
        }

        $normalizado = str_replace(',', '.', (string) $valor);

        return (float) preg_replace('/[^0-9.]/', '', $normalizado);
    }

    public function convertirBobAUsd(float|int|string $montoBob): float
    {
        $tipoCambio = $this->obtenerUsdBobReferencial();

        return round((float) $montoBob * (float) $tipoCambio['usd_por_bs'], 2);
    }
}