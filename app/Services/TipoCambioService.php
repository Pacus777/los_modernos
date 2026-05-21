<?php

namespace App\Services;

use App\Models\TipoCambio;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Tipo de cambio USD/BOB desde el Banco Central de Bolivia (BCB).
 *
 * Por defecto usa el valor referencial del dólar (compra/venta ~9,92 / 10,13),
 * no el tipo de cambio oficial (~6,86 / 6,96).
 */
class TipoCambioService
{
    public function obtenerUsdBobReferencial(): array
    {
        if (! config('tipocambio.enabled', true)) {
            return [
                'activo' => false,
                'usd_to_bob' => null,
                'usd_por_bs' => 0.0,
                'label' => 'Tipo de cambio desactivado',
                'source' => (string) config('tipocambio.institucion_label'),
                'tipo_bcb' => null,
                'updated_at' => null,
            ];
        }

        $cacheMinutes = max(1, (int) config('tipocambio.cache_minutes', 60));
        $cacheKey = 'tipo_cambio_bcb_'.strtolower((string) config('tipocambio.tipo', 'referencial'));

        return Cache::remember(
            $cacheKey,
            now()->addMinutes($cacheMinutes),
            fn () => $this->resolverTipoCambio()
        );
    }

    private function resolverTipoCambio(): array
    {
        try {
            $desdeBcb = $this->obtenerDesdeBcb();

            if ($desdeBcb !== null) {
                $registro = TipoCambio::query()->create([
                    'base' => 'USD',
                    'quote' => 'BOB',
                    'compra' => $desdeBcb['compra'],
                    'venta' => $desdeBcb['venta'],
                    'promedio' => $desdeBcb['promedio'],
                    'fuente' => $desdeBcb['fuente'],
                    'tipo' => (string) config('tipocambio.tipo', 'referencial'),
                    'consultado_en' => now(),
                    'metadata' => $desdeBcb['metadata'] ?? [],
                ]);

                return $this->respuestaDesdeRegistro($registro, $desdeBcb['label'] ?? null);
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo obtener tipo de cambio desde BCB.', [
                'error' => $e->getMessage(),
            ]);
        }

        if (config('tipocambio.provider') !== 'bcb') {
            try {
                $desdeDolarApi = $this->obtenerDesdeDolarApi();

                if ($desdeDolarApi !== null) {
                    $registro = TipoCambio::query()->create([
                        'base' => 'USD',
                        'quote' => 'BOB',
                        'compra' => $desdeDolarApi['compra'],
                        'venta' => $desdeDolarApi['venta'],
                        'promedio' => $desdeDolarApi['promedio'],
                        'fuente' => $desdeDolarApi['fuente'],
                        'tipo' => 'referencial',
                        'consultado_en' => now(),
                        'metadata' => $desdeDolarApi['metadata'] ?? [],
                    ]);

                    return $this->respuestaDesdeRegistro($registro);
                }
            } catch (\Throwable $e) {
                Log::warning('No se pudo obtener tipo de cambio desde DolarApi.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $ultimo = TipoCambio::query()
            ->where('base', 'USD')
            ->where('quote', 'BOB')
            ->where('tipo', config('tipocambio.tipo', 'referencial'))
            ->latest('consultado_en')
            ->first();

        if ($ultimo) {
            return $this->respuestaDesdeRegistro($ultimo, 'Último valor guardado');
        }

        return $this->respuestaDesdeFallback();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function obtenerDesdeBcb(): ?array
    {
        $tipo = strtolower((string) config('tipocambio.tipo', 'referencial'));

        if ($tipo === 'oficial') {
            return $this->obtenerBcbOficial();
        }

        return $this->obtenerBcbReferencial();
    }

    /**
     * Valor referencial del dólar (el de la tabla «VALOR REFERENCIAL» en bcb.gob.bo).
     *
     * @return array<string, mixed>|null
     */
    private function obtenerBcbReferencial(): ?array
    {
        $response = Http::timeout(8)
            ->acceptJson()
            ->get((string) config('tipocambio.bcb_api_url_referencial'));

        if (! $response->successful()) {
            throw new \RuntimeException('BCB API respondió '.$response->status());
        }

        $data = $response->json();
        $tc = data_get($data, 'tc_referencial_usd');

        if (! is_array($tc)) {
            return null;
        }

        $compra = $this->extraerNumero(data_get($tc, 'compra'));
        $venta = $this->extraerNumero(data_get($tc, 'venta'));

        if ($venta <= 0 && $compra > 0) {
            $venta = $compra;
        }

        if ($venta <= 0) {
            return null;
        }

        return [
            'compra' => $compra > 0 ? $compra : null,
            'venta' => $venta,
            'promedio' => $compra > 0 ? round(($compra + $venta) / 2, 4) : $venta,
            'fuente' => data_get($data, 'fuente', (string) config('tipocambio.institucion_label')),
            'label' => 'Dólar referencial BCB (venta)',
            'tipo_bcb' => 'referencial',
            'metadata' => [
                'raw' => $tc,
                'fecha' => data_get($tc, 'fecha'),
                'api_url' => config('tipocambio.bcb_api_url_referencial'),
                'proveedor_api' => 'bcb.cucu.bo',
            ],
        ];
    }

    /**
     * Tipo de cambio oficial BCB (tabla «TIPO DE CAMBIO» en bcb.gob.bo).
     *
     * @return array<string, mixed>|null
     */
    private function obtenerBcbOficial(): ?array
    {
        $response = Http::timeout(8)
            ->acceptJson()
            ->get((string) config('tipocambio.bcb_api_url_oficial'));

        if (! $response->successful()) {
            throw new \RuntimeException('BCB API oficial respondió '.$response->status());
        }

        $data = $response->json();
        $detalle = collect(data_get($data, 'tc_oficial.detalle', []));

        $compra = $this->extraerNumero(
            $detalle->firstWhere('fecha_label', 'Compra')['valor'] ?? data_get($data, 'tc_oficial.valor')
        );

        $venta = $this->extraerNumero(
            $detalle->firstWhere('fecha_label', 'Venta')['valor'] ?? 0
        );

        if ($venta <= 0 && $compra > 0) {
            $venta = $compra;
        }

        if ($venta <= 0) {
            return null;
        }

        return [
            'compra' => $compra > 0 ? $compra : null,
            'venta' => $venta,
            'promedio' => $compra > 0 ? round(($compra + $venta) / 2, 4) : $venta,
            'fuente' => data_get($data, 'fuente', (string) config('tipocambio.institucion_label')),
            'label' => 'Tipo de cambio oficial BCB (venta)',
            'tipo_bcb' => 'oficial',
            'metadata' => [
                'raw' => data_get($data, 'tc_oficial'),
                'fecha' => data_get($data, 'tc_oficial.fecha'),
                'api_url' => config('tipocambio.bcb_api_url_oficial'),
                'proveedor_api' => 'bcb.cucu.bo',
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function obtenerDesdeDolarApi(): ?array
    {
        $response = Http::timeout(8)
            ->acceptJson()
            ->get(config('tipocambio.api_url'));

        if (! $response->successful()) {
            throw new \RuntimeException('DolarApi respondió '.$response->status());
        }

        $data = $response->json();
        $items = is_array($data) && array_is_list($data) ? $data : [$data];
        $casaPreferida = strtolower((string) config('tipocambio.casa_preferida', 'oficial'));

        $item = collect($items)->first(function ($row) use ($casaPreferida) {
            $casa = strtolower((string) data_get($row, 'casa', ''));
            $nombre = strtolower((string) data_get($row, 'nombre', ''));

            return $casa === $casaPreferida || str_contains($nombre, $casaPreferida);
        }) ?? collect($items)->first();

        if (! $item) {
            return null;
        }

        $compra = $this->extraerNumero(data_get($item, 'compra'));
        $venta = $this->extraerNumero(data_get($item, 'venta'));

        if ($venta <= 0 && $compra > 0) {
            $venta = $compra;
        }

        if ($venta <= 0) {
            return null;
        }

        return [
            'compra' => $compra > 0 ? $compra : null,
            'venta' => $venta,
            'promedio' => $compra > 0 ? round(($compra + $venta) / 2, 4) : $venta,
            'fuente' => 'DolarApi Bolivia (respaldo)',
            'label' => 'Dólar oficial vía DolarApi (respaldo)',
            'tipo_bcb' => 'oficial',
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

        $tipoBcb = data_get($tipoCambio->metadata, 'tipo_bcb')
            ?? $tipoCambio->tipo
            ?? config('tipocambio.tipo', 'referencial');

        return [
            'activo' => true,
            'usd_to_bob' => round($usdToBob, 4),
            'usd_por_bs' => round(1 / $usdToBob, 6),
            'compra' => $tipoCambio->compra ? (float) $tipoCambio->compra : null,
            'venta' => (float) $tipoCambio->venta,
            'promedio' => $tipoCambio->promedio ? (float) $tipoCambio->promedio : null,
            'label' => $label ?? 'Dólar referencial BCB (venta)',
            'source' => $tipoCambio->fuente,
            'tipo_bcb' => $tipoBcb,
            'updated_at' => optional($tipoCambio->consultado_en)->toISOString(),
        ];
    }

    private function respuestaDesdeFallback(): array
    {
        $venta = (float) config('tipocambio.fallback_usd_to_bob', 10.13);
        $compra = (float) config('tipocambio.fallback_compra', 9.92);

        if ($venta <= 0) {
            $venta = 10.13;
        }

        return [
            'activo' => true,
            'usd_to_bob' => round($venta, 4),
            'usd_por_bs' => round(1 / $venta, 6),
            'compra' => $compra > 0 ? $compra : null,
            'venta' => round($venta, 4),
            'promedio' => $compra > 0 ? round(($compra + $venta) / 2, 4) : $venta,
            'label' => 'Dólar referencial BCB (respaldo local)',
            'source' => (string) config('tipocambio.institucion_label'),
            'tipo_bcb' => 'referencial',
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
