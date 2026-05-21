<?php

namespace App\Services;

use App\DataTransferObjects\LibelulaDeudaRespuesta;
use App\Exceptions\LibelulaApiException;
use App\Models\Donacion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * S2-03 — Integración Libélula: registrar deuda y persistir transaction_id en donaciones.
 */
class LibelulaService
{
    private const RUTA_REGISTRAR_DEUDA = '/rest/deuda/registrar';

    /**
     * Registra la deuda en Libélula y guarda id_transaccion + URL de pago en la donación.
     *
     * @param  array{email?: string|null, nombre?: string|null, apellido?: string|null}  $cliente
     */
    public function crearDeuda(Donacion $donacion, array $cliente = []): LibelulaDeudaRespuesta
    {
        $donacion->loadMissing(['campana', 'visitante', 'tipoPago']);

        if (! $donacion->tipoPago?->esLibelula()) {
            throw ValidationException::withMessages([
                'tipo_pago' => 'El tipo de pago seleccionado no utiliza Libélula.',
            ]);
        }

        if (! $donacion->estaPendiente()) {
            throw ValidationException::withMessages([
                'donacion' => 'Solo se puede crear deuda Libélula para donaciones pendientes.',
            ]);
        }

        if (filled($donacion->transaction_id)) {
            return new LibelulaDeudaRespuesta(
                idTransaccion: (string) $donacion->transaction_id,
                urlPasarela: (string) ($donacion->checkout_url ?? ''),
                qrSimpleUrl: is_array($donacion->metadata_pago)
                    ? ($donacion->metadata_pago['libelula_qr_simple_url'] ?? null)
                    : null,
                mensaje: 'Deuda ya registrada previamente.',
                simulada: (bool) ($donacion->metadata_pago['libelula_simulada'] ?? false),
            );
        }

        $respuesta = $this->appKeyConfigurada()
            ? $this->invocarRegistrarDeuda($donacion, $cliente)
            : $this->respuestaSimulada($donacion);

        $this->persistirEnDonacion($donacion, $respuesta);

        return $respuesta;
    }

    /**
     * @param  array{email?: string|null, nombre?: string|null, apellido?: string|null}  $cliente
     */
    private function invocarRegistrarDeuda(Donacion $donacion, array $cliente): LibelulaDeudaRespuesta
    {
        $payload = $this->armarPayload($donacion, $cliente);

        $http = Http::timeout((int) config('libelula.timeout_seconds', 30))
            ->acceptJson()
            ->asJson();

        $response = $http->post($this->urlRegistrarDeuda(), $payload);

        if (! $response->successful()) {
            throw new LibelulaApiException(
                'Libélula respondió con error HTTP '.$response->status().'.',
                ['body' => $response->json() ?? $response->body()],
            );
        }

        /** @var array<string, mixed> $data */
        $data = $response->json() ?? [];

        return $this->interpretarRespuesta($data);
    }

    /**
     * @param  array{email?: string|null, nombre?: string|null, apellido?: string|null}  $cliente
     * @return array<string, mixed>
     */
    private function armarPayload(Donacion $donacion, array $cliente): array
    {
        [$nombre, $apellido] = $this->partirNombreCliente(
            $cliente['nombre'] ?? $donacion->visitante?->nombre,
        );

        $identificador = $donacion->referencia_pago
            ?? 'WAYNA-DON-'.$donacion->id;

        $descripcion = 'Aporte WAYNA — '.($donacion->campana?->titulo ?? 'Campaña');

        return [
            'appkey' => (string) config('libelula.app_key'),
            'email_cliente' => $this->resolverEmailCliente($donacion, $cliente['email'] ?? null),
            'identificador_deuda' => $identificador,
            'identificador' => $identificador,
            'descripcion' => Str::limit($descripcion, 200, ''),
            'callback_url' => route('webhooks.libelula'),
            'url_retorno' => route('turista.donaciones.exitosa', $donacion),
            'nombre_cliente' => $nombre,
            'apellido_cliente' => $apellido,
            'moneda' => Donacion::MONEDA_BOB,
            'fecha_vencimiento' => now()
                ->addMinutes((int) config('wayna.pago_pendiente_minutos', 15))
                ->format('Y-m-d'),
            'lineas_detalle_deuda' => [
                [
                    'concepto' => Str::limit($descripcion, 120, ''),
                    'cantidad' => 1,
                    'costo_unitario' => (float) $donacion->monto,
                    'codigo_producto' => 'WAYNA-'.$donacion->id,
                ],
            ],
            'lineas_metadatos' => [
                ['nombre' => 'donacion_id', 'dato' => (string) $donacion->id],
                ['nombre' => 'referencia_wayna', 'dato' => $identificador],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function interpretarRespuesta(array $data): LibelulaDeudaRespuesta
    {
        $error = $this->valorBooleano($data['error'] ?? $data['Error'] ?? false);

        if ($error) {
            $mensaje = (string) ($data['mensaje'] ?? $data['Mensaje'] ?? 'Error al registrar deuda en Libélula.');

            throw new LibelulaApiException($mensaje, $data);
        }

        $idTransaccion = $this->extraerCadena($data, [
            'id_transaccion',
            'idTransaccion',
            'transaction_id',
        ]);

        $urlPasarela = $this->extraerCadena($data, [
            'url_pasarela_pagos',
            'urlPasarelaPagos',
            'checkout_url',
        ]);

        if ($idTransaccion === null || $urlPasarela === null) {
            throw new LibelulaApiException(
                'La respuesta de Libélula no incluye id_transaccion ni url_pasarela_pagos.',
                $data,
            );
        }

        return new LibelulaDeudaRespuesta(
            idTransaccion: $idTransaccion,
            urlPasarela: $urlPasarela,
            qrSimpleUrl: $this->extraerCadena($data, ['qr_simple_url', 'qrSimpleUrl']),
            mensaje: $this->extraerCadena($data, ['mensaje', 'Mensaje']),
        );
    }

    private function respuestaSimulada(Donacion $donacion): LibelulaDeudaRespuesta
    {
        if (! config('libelula.fake_when_unconfigured', true)) {
            throw new LibelulaApiException(
                'Libélula no está configurada (falta LIBELULA_APP_KEY).',
            );
        }

        $id = 'sim-'.Str::lower(Str::uuid()->toString());

        return new LibelulaDeudaRespuesta(
            idTransaccion: $id,
            urlPasarela: route('turista.donaciones.exitosa', $donacion).'?libelula_simulada=1',
            qrSimpleUrl: null,
            mensaje: 'Deuda simulada (sin LIBELULA_APP_KEY).',
            simulada: true,
        );
    }

    private function persistirEnDonacion(Donacion $donacion, LibelulaDeudaRespuesta $respuesta): void
    {
        $metadata = array_merge($donacion->metadata_pago ?? [], [
            'libelula_registrada_en' => now()->toIso8601String(),
            'libelula_simulada' => $respuesta->simulada,
        ]);

        if ($respuesta->qrSimpleUrl) {
            $metadata['libelula_qr_simple_url'] = $respuesta->qrSimpleUrl;
        }

        $donacion->update([
            'transaction_id' => $respuesta->idTransaccion,
            'checkout_url' => $respuesta->urlPasarela,
            'proveedor_pago' => Donacion::PROVEEDOR_LIBELULA,
            'estado_proveedor' => 'deuda_registrada',
            'metadata_pago' => $metadata,
        ]);
    }

    private function urlRegistrarDeuda(): string
    {
        $base = config('libelula.sandbox', true)
            ? config('libelula.sandbox_base_url')
            : config('libelula.base_url');

        return rtrim((string) $base, '/').self::RUTA_REGISTRAR_DEUDA;
    }

    private function appKeyConfigurada(): bool
    {
        return filled(config('libelula.app_key'));
    }

    private function resolverEmailCliente(Donacion $donacion, ?string $email): string
    {
        $email = trim((string) $email);

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        $dominio = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'wayna.local';

        return 'donacion+'.$donacion->id.'@'.$dominio;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function partirNombreCliente(?string $nombreCompleto): array
    {
        $nombreCompleto = trim((string) $nombreCompleto);

        if ($nombreCompleto === '') {
            return ['Visitante', 'WAYNA'];
        }

        $partes = preg_split('/\s+/', $nombreCompleto, 2) ?: [];

        return [
            $partes[0] ?? 'Visitante',
            $partes[1] ?? 'WAYNA',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $claves
     */
    private function extraerCadena(array $data, array $claves): ?string
    {
        foreach ($claves as $clave) {
            if (! array_key_exists($clave, $data)) {
                continue;
            }

            $valor = $data[$clave];

            if (is_string($valor) && trim($valor) !== '') {
                return trim($valor);
            }

            if (is_numeric($valor)) {
                return (string) $valor;
            }
        }

        return null;
    }

    private function valorBooleano(mixed $valor): bool
    {
        if (is_bool($valor)) {
            return $valor;
        }

        if (is_string($valor)) {
            return in_array(strtolower($valor), ['1', 'true', 'yes', 'si', 'sí'], true);
        }

        return (bool) $valor;
    }
}
