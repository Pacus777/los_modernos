<?php

namespace App\Support;

use App\Models\Donacion;
use App\Models\TipoPago;

/**
 * S2-07 — QR BCP estático de WAYNA (cuenta única del proyecto).
 */
final class WaynaBcpQr
{
    public static function habilitado(): bool
    {
        return (bool) config('wayna.bcp_qr.enabled', true);
    }

    public static function urlImagen(): string
    {
        $path = (string) config('wayna.bcp_qr.image_path', '/images/wayna-qr-bcp.svg');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }

    /**
     * Pagos QR / billetera con el QR fijo de WAYNA (no Libélula ni efectivo).
     */
    public static function aplicaADonacion(Donacion $donacion): bool
    {
        if (! self::habilitado()) {
            return false;
        }

        $donacion->loadMissing('tipoPago');

        if ($donacion->tipoPago?->esLibelula()) {
            return false;
        }

        $metodo = strtolower((string) $donacion->metodo);

        if (str_contains($metodo, 'efectivo')) {
            return false;
        }

        if ($donacion->tipoPago?->proveedor === TipoPago::PROVEEDOR_BANCO) {
            return true;
        }

        return str_contains($metodo, 'qr')
            || str_contains($metodo, 'bcp')
            || str_contains($metodo, 'billetera');
    }

    /**
     * @return array<string, mixed>
     */
    public static function paraFrontend(?Donacion $donacion = null): array
    {
        $base = [
            'habilitado' => self::habilitado(),
            'imagen_url' => self::urlImagen(),
            'titular' => (string) config('wayna.bcp_qr.titular', 'WAYNA Conecta'),
            'banco' => (string) config('wayna.bcp_qr.banco', 'BCP'),
        ];

        if ($donacion === null) {
            return $base;
        }

        return array_merge($base, [
            'monto' => (float) $donacion->monto,
            'referencia_pago' => $donacion->referencia_pago,
            'moneda' => $donacion->moneda ?? Donacion::MONEDA_BOB,
        ]);
    }
}
