<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Donacion;
use App\Models\WebhookLog;
use App\Services\DonacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class LibelulaWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        DonacionService $donacionService,
    ): JsonResponse {
        $payload = $request->all();

        $eventId = $this->extraerValor($payload, [
            'event_id',
            'eventId',
            'id_evento',
            'idEvento',
            'id',
        ]) ?? $this->hashPayload($request);

        $transactionId = $this->extraerValor($payload, [
            'transaction_id',
            'transactionId',
            'id_transaccion',
            'idTransaccion',
            'transaccion',
        ]);

        $evento = $this->extraerValor($payload, [
            'evento',
            'event',
            'type',
            'tipo_evento',
            'tipoEvento',
            'accion',
        ]) ?? 'webhook.libelula';

        $estadoProveedor = $this->extraerValor($payload, [
            'estado',
            'status',
            'state',
            'estado_pago',
            'estadoPago',
            'estado_transaccion',
            'estadoTransaccion',
        ]);

        $webhookLog = WebhookLog::create([
            'proveedor' => WebhookLog::PROVEEDOR_LIBELULA,
            'event_id' => $eventId,
            'transaction_id' => $transactionId,
            'evento' => $evento,
            'estado' => WebhookLog::ESTADO_RECIBIDO,
            'headers' => $this->headersSeguros($request),
            'payload' => $payload,
            'recibido_en' => now(),
        ]);

        try {
            if (! $this->firmaValida($request)) {
                $webhookLog->marcarComoError('Firma inválida.');

                return response()->json([
                    'ok' => false,
                    'message' => 'Firma inválida.',
                ], 401);
            }

            if (! $transactionId) {
                $webhookLog->marcarComoError('Webhook sin transaction_id.');

                return response()->json([
                    'ok' => false,
                    'message' => 'Webhook sin transaction_id.',
                ], 422);
            }

            if ($this->webhookYaProcesado($eventId, $transactionId, $evento, $webhookLog->id)) {
                $webhookLog->marcarComoIgnorado('Webhook duplicado.');

                return response()->json([
                    'ok' => true,
                    'message' => 'Webhook duplicado ignorado.',
                ]);
            }

            $donacion = Donacion::query()
                ->where('transaction_id', $transactionId)
                ->first();

            if (! $donacion) {
                $webhookLog->marcarComoError("No existe donación con transaction_id {$transactionId}.");

                return response()->json([
                    'ok' => false,
                    'message' => 'Donación no encontrada.',
                ], 404);
            }

            $webhookLog->forceFill([
                'donacion_id' => $donacion->id,
            ])->save();

            if (! $this->representaPagoExitoso($payload, $estadoProveedor)) {
                $this->actualizarEstadoProveedor($donacion, $estadoProveedor, $payload);

                $webhookLog->marcarComoIgnorado(
                    'Webhook recibido, pero no representa pago confirmado.',
                );

                return response()->json([
                    'ok' => true,
                    'message' => 'Webhook registrado sin confirmar pago.',
                ]);
            }

            DB::transaction(function () use (
                $donacion,
                $donacionService,
                $transactionId,
                $estadoProveedor,
                $payload
            ): void {
                $donacion->refresh();

                $metadata = array_merge($donacion->metadata_pago ?? [], [
                    'libelula_webhook_confirmado_en' => now()->toIso8601String(),
                    'libelula_webhook_payload' => $payload,
                ]);

                $donacionService->confirmarPagoDesdeWebhook(
                    donacion: $donacion,
                    proveedor: Donacion::PROVEEDOR_LIBELULA,
                    transactionId: $transactionId,
                    estadoProveedor: $estadoProveedor ?? 'pagado',
                    metadataPago: $metadata,
                );
            });

            $webhookLog->marcarComoProcesado();

            return response()->json([
                'ok' => true,
                'message' => 'Pago confirmado desde webhook.',
            ]);
        } catch (ValidationException $exception) {
            $webhookLog->marcarComoError(
                Arr::first(Arr::flatten($exception->errors())) ?? $exception->getMessage(),
            );

            return response()->json([
                'ok' => false,
                'message' => 'Webhook no pudo procesarse.',
                'errors' => $exception->errors(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            $webhookLog->marcarComoError($exception->getMessage());

            return response()->json([
                'ok' => false,
                'message' => 'Error interno procesando webhook.',
            ], 500);
        }
    }

    private function webhookYaProcesado(
        string $eventId,
        string $transactionId,
        string $evento,
        int $webhookLogActualId,
    ): bool {
        return WebhookLog::query()
            ->libelula()
            ->where('id', '!=', $webhookLogActualId)
            ->where('estado', WebhookLog::ESTADO_PROCESADO)
            ->where(function ($query) use ($eventId, $transactionId, $evento): void {
                $query
                    ->where('event_id', $eventId)
                    ->orWhere(function ($q) use ($transactionId, $evento): void {
                        $q->where('transaction_id', $transactionId)
                            ->where('evento', $evento);
                    });
            })
            ->exists();
    }

    private function actualizarEstadoProveedor(
        Donacion $donacion,
        ?string $estadoProveedor,
        array $payload,
    ): void {
        $metadata = array_merge($donacion->metadata_pago ?? [], [
            'libelula_ultimo_webhook_en' => now()->toIso8601String(),
            'libelula_ultimo_webhook_payload' => $payload,
        ]);

        $donacion->forceFill([
            'proveedor_pago' => Donacion::PROVEEDOR_LIBELULA,
            'estado_proveedor' => $estadoProveedor ?? $donacion->estado_proveedor,
            'metadata_pago' => $metadata,
        ])->save();
    }

    private function representaPagoExitoso(array $payload, ?string $estadoProveedor): bool
    {
        $estado = strtolower(trim((string) $estadoProveedor));

        if (in_array($estado, [
            'pagado',
            'paid',
            'confirmado',
            'confirmed',
            'completado',
            'completed',
            'aprobado',
            'approved',
            'success',
            'successful',
            'validado',
        ], true)) {
            return true;
        }

        $pagado = $payload['pagado']
            ?? $payload['paid']
            ?? $payload['confirmado']
            ?? $payload['confirmed']
            ?? null;

        return $this->valorBooleano($pagado);
    }

    private function firmaValida(Request $request): bool
    {
        $secret = config('libelula.webhook_secret');

        if (! filled($secret)) {
            return true;
        }

        $firmaRecibida = $request->header('X-Libelula-Signature')
            ?? $request->header('X-Signature')
            ?? $request->header('X-Hub-Signature-256');

        if (! $firmaRecibida) {
            return false;
        }

        $firmaRecibida = str_replace('sha256=', '', $firmaRecibida);

        $firmaCalculada = hash_hmac(
            'sha256',
            $request->getContent(),
            (string) $secret,
        );

        return hash_equals($firmaCalculada, $firmaRecibida);
    }

    /**
     * @param  list<string>  $claves
     */
    private function extraerValor(array $payload, array $claves): ?string
    {
        foreach ($claves as $clave) {
            $valor = data_get($payload, $clave);

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

        if (is_numeric($valor)) {
            return (int) $valor === 1;
        }

        if (is_string($valor)) {
            return in_array(strtolower(trim($valor)), [
                '1',
                'true',
                'yes',
                'si',
                'sí',
                'pagado',
                'paid',
                'confirmed',
                'confirmado',
            ], true);
        }

        return false;
    }

    private function hashPayload(Request $request): string
    {
        return 'payload-'.sha1($request->getContent());
    }

    /**
     * @return array<string, string>
     */
    private function headersSeguros(Request $request): array
    {
        return collect($request->headers->all())
            ->map(fn (array $values): string => implode(', ', $values))
            ->all();
    }
}