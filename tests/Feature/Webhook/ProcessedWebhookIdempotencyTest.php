<?php

namespace Tests\Feature\Webhook;

use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\ProcessedWebhook;
use App\Models\TipoPago;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessedWebhookIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_duplicado_no_valida_dos_veces_la_donacion(): void
    {
        $donacion = $this->crearDonacionPendienteLibelula('tx-wayna-001');

        $payload = [
            'event_id' => 'evt-100',
            'transaction_id' => 'tx-wayna-001',
            'status' => 'paid',
        ];

        $primera = $this->postJson(route('webhooks.libelula'), $payload);
        $primera->assertOk();
        $primera->assertJsonPath('message', 'processed');

        $donacion->refresh();
        $this->assertSame(Donacion::ESTADO_VALIDADO, $donacion->estado_pago);

        $segunda = $this->postJson(route('webhooks.libelula'), $payload);
        $segunda->assertOk();
        $segunda->assertJsonPath('message', 'already_processed');
        $segunda->assertJsonPath('duplicate', true);

        $this->assertSame(
            1,
            ProcessedWebhook::query()
                ->where('proveedor', 'libelula')
                ->where('idempotency_key', 'libelula:event:evt-100')
                ->count(),
        );
    }

    public function test_webhook_sin_identificadores_devuelve_error(): void
    {
        $response = $this->postJson(route('webhooks.libelula'), [
            'status' => 'paid',
        ]);

        $response->assertUnprocessable();
        $this->assertSame(0, ProcessedWebhook::query()->count());
    }

    private function crearDonacionPendienteLibelula(string $transactionId): Donacion
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Test',
            'apellidos' => 'Webhook',
            'descripcion' => null,
            'fotografia' => null,
            'qr_url' => null,
            'estado' => 'activo',
            'meta_monto' => 0,
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña webhook',
            'meta_apoyo' => 100,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $tipoPago = TipoPago::query()->create([
            'codigo' => 'libelula_qr',
            'nombre' => 'Libélula',
            'descripcion' => null,
            'proveedor' => 'libelula',
            'activo' => true,
        ]);

        return Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => 50,
            'metodo' => 'libelula_qr',
            'estado_pago' => Donacion::ESTADO_PENDIENTE,
            'referencia_pago' => 'WAYNA-TEST-001',
            'transaction_id' => $transactionId,
            'proveedor_pago' => 'libelula',
        ]);
    }
}
