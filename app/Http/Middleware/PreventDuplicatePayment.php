<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Evita registrar el mismo intento de pago dos veces (S3-01).
 *
 * Usa payment_uuid + Redis (o el store configurado) como candado de corta duración.
 */
class PreventDuplicatePayment
{
    public function handle(Request $request, Closure $next): Response
    {
        $uuid = $request->input('payment_uuid') ?? $request->header('X-Payment-Uuid');

        if (! is_string($uuid) || ! Str::isUuid($uuid)) {
            throw ValidationException::withMessages([
                'payment_uuid' => __('donacion.payment_uuid_invalid'),
            ]);
        }

        $uuid = strtolower($uuid);
        $key = 'wayna:payment_uuid:'.$uuid;
        $ttlSeconds = (int) config('wayna.payment_lock_ttl_seconds', 900);
        $cache = $this->paymentLockCache();

        if (! $cache->add($key, 1, $ttlSeconds)) {
            throw ValidationException::withMessages([
                'payment_uuid' => __('donacion.payment_duplicate'),
            ]);
        }

        try {
            $response = $next($request);

            if ($response->getStatusCode() >= 400) {
                $cache->forget($key);
            }

            return $response;
        } catch (Throwable $e) {
            $cache->forget($key);

            throw $e;
        }
    }

    /**
     * Store para el candado: Redis si está disponible; si no, cache database (local).
     */
    private function paymentLockCache(): Repository
    {
        $configured = (string) config('wayna.payment_lock_store', 'redis');

        if ($configured !== 'redis') {
            return Cache::store($configured);
        }

        if ($this->redisEstaDisponible()) {
            return Cache::store('redis');
        }

        if (app()->environment('local')) {
            Log::warning(
                'WAYNA: Redis no disponible (falta extensión phpredis o predis). '
                .'Usando cache database para payment_uuid. '
                .'Activá php_redis en Laragon o definí WAYNA_PAYMENT_LOCK_STORE=database.',
            );

            return Cache::store('database');
        }

        throw new \RuntimeException(
            'Redis no está disponible para el candado de pagos. '
            .'Instalá la extensión phpredis, predis/predis, o configurá WAYNA_PAYMENT_LOCK_STORE=database.',
        );
    }

    private function redisEstaDisponible(): bool
    {
        if (extension_loaded('redis')) {
            return true;
        }

        return class_exists(\Predis\Client::class);
    }
}
