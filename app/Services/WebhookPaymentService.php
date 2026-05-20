<?php

namespace App\Services;

use App\Models\Donacion;
use App\Models\ProcessedWebhook;
use App\Models\WebhookLog;
use App\Support\WebhookIdempotencyResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Orquesta recepción de webhooks de pago con idempotencia (S3-02).
 */
class WebhookPaymentService
{
    public function __construct(
        protected WebhookLogService $webhookLogService,
        protected ProcessedWebhookService $processedWebhookService,
        protected DonacionService $donacionService,
    ) {}

    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function procesarLibelula(Request $request): array
    {
        $payload = $request->all();

        $eventId = $this->extraerCadena($payload, ['event_id', 'eventId', 'id']);
        $transactionId = $this->extraerCadena($payload, [
            'transaction_id',
            'transactionId',
            'transaction',
        ]);
        $estadoProveedor = $this->extraerCadena($payload, ['status', 'estado', 'state']);

        $log = $this->webhookLogService->registrarEntrada(
            proveedor: Donacion::PROVEEDOR_LIBELULA,
            payload: $payload,
            request: $request,
            eventId: $eventId,
            transactionId: $transactionId,
        );

        try {
            $idempotencyKey = $this->processedWebhookService->resolverClaveIdempotencia(
                Donacion::PROVEEDOR_LIBELULA,
                $eventId,
                $transactionId,
            );
        } catch (\InvalidArgumentException $exception) {
            $log->marcarComoError($exception->getMessage());

            return [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'body' => [
                    'message' => $exception->getMessage(),
                ],
            ];
        }

        $idempotencia = $this->processedWebhookService->reclamar(
            proveedor: Donacion::PROVEEDOR_LIBELULA,
            idempotencyKey: $idempotencyKey,
            eventId: $eventId,
            transactionId: $transactionId,
            webhookLogId: $log->id,
        );

        if ($idempotencia->alreadyProcessed) {
            $log->marcarComoIgnorado('Webhook duplicado: ya procesado (idempotencia).');

            return $this->respuestaDuplicado($idempotencia);
        }

        if ($idempotencia->concurrentProcessing) {
            $log->marcarComoIgnorado('Webhook duplicado: procesamiento en curso.');

            return [
                'status' => Response::HTTP_OK,
                'body' => [
                    'message' => 'processing_in_progress',
                    'duplicate' => true,
                ],
            ];
        }

        $record = $idempotencia->record;
        assert($record instanceof ProcessedWebhook);

        if (! $this->estadoIndicaPagoExitoso($estadoProveedor)) {
            $log->marcarComoIgnorado('Estado de pasarela no confirma pago.');

            $this->processedWebhookService->marcarCompletado(
                $record,
                Response::HTTP_OK,
            );

            return [
                'status' => Response::HTTP_OK,
                'body' => [
                    'message' => 'ignored_non_success_status',
                    'estado_proveedor' => $estadoProveedor,
                ],
            ];
        }

        $donacion = $this->resolverDonacion($transactionId, $payload);

        if (! $donacion) {
            $mensaje = 'No se encontró donación para el transaction_id del webhook.';
            $log->marcarComoError($mensaje);
            $this->processedWebhookService->marcarFallido($record, Response::HTTP_UNPROCESSABLE_ENTITY);

            return [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'body' => [
                    'message' => $mensaje,
                ],
            ];
        }

        $log->update(['donacion_id' => $donacion->id]);

        try {
            $donacion = DB::transaction(function () use (
                $donacion,
                $transactionId,
                $estadoProveedor,
                $payload,
            ) {
                return $this->donacionService->confirmarPagoDesdeWebhook(
                    $donacion,
                    Donacion::PROVEEDOR_LIBELULA,
                    $transactionId,
                    $estadoProveedor,
                    $payload,
                );
            });

            $log->marcarComoProcesado();
            $this->processedWebhookService->marcarCompletado(
                $record,
                Response::HTTP_OK,
                $donacion->id,
            );

            return [
                'status' => Response::HTTP_OK,
                'body' => [
                    'message' => 'processed',
                    'donacion_id' => $donacion->id,
                    'estado_pago' => $donacion->estado_pago,
                ],
            ];
        } catch (\Throwable $exception) {
            $log->marcarComoError($exception->getMessage());
            $this->processedWebhookService->marcarFallido(
                $record,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $donacion->id,
            );

            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'body' => [
                    'message' => 'processing_failed',
                ],
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolverDonacion(?string $transactionId, array $payload): ?Donacion
    {
        if (filled($transactionId)) {
            $porTx = Donacion::query()
                ->where('transaction_id', $transactionId)
                ->first();

            if ($porTx) {
                return $porTx;
            }
        }

        $referencia = $this->extraerCadena($payload, [
            'referencia_pago',
            'reference',
            'referencia',
            'payment_reference',
        ]);

        if (filled($referencia)) {
            return Donacion::query()
                ->where('referencia_pago', $referencia)
                ->first();
        }

        return null;
    }

    private function estadoIndicaPagoExitoso(?string $estado): bool
    {
        if (! filled($estado)) {
            return false;
        }

        $normalizado = strtolower(trim($estado));

        return in_array($normalizado, [
            'paid',
            'success',
            'successful',
            'completed',
            'approved',
            'pagado',
            'exitoso',
            'completado',
        ], true);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $claves
     */
    private function extraerCadena(array $payload, array $claves): ?string
    {
        foreach ($claves as $clave) {
            if (! empty($payload[$clave]) && is_scalar($payload[$clave])) {
                return trim((string) $payload[$clave]);
            }
        }

        return null;
    }

    private function respuestaDuplicado(WebhookIdempotencyResult $idempotencia): array
    {
        return [
            'status' => Response::HTTP_OK,
            'body' => [
                'message' => 'already_processed',
                'duplicate' => true,
                'donacion_id' => $idempotencia->record?->donacion_id,
                'processed_at' => $idempotencia->record?->completed_at?->toIso8601String(),
            ],
        ];
    }
}
