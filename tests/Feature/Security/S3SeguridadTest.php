<?php

namespace Tests\Feature\Security;

use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\ProcessedWebhook;
use App\Models\TipoPago;
use App\Models\Visitante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * S3-10 — Tests PHPUnit de seguridad: doble cobro, webhook duplicado, XSS.
 */
class S3SeguridadTest extends TestCase
{
    use RefreshDatabase;

    public function test_doble_cobro_rechaza_mismo_payment_uuid(): void
    {
        [$campana, $tipoPago] = $this->crearCampanaActiva();
        $uuid = (string) Str::uuid();

        $payload = [
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => 15,
            'metodo' => 'efectivo_vis',
            'payment_uuid' => $uuid,
        ];

        $this->post(route('turista.donaciones.store'), $payload)->assertRedirect();
        $this->assertSame(1, Donacion::query()->count());

        $this->post(route('turista.donaciones.store'), $payload)
            ->assertSessionHasErrors('payment_uuid');

        $this->assertSame(1, Donacion::query()->count());
    }

    public function test_webhook_duplicado_no_valida_dos_veces(): void
    {
        $this->crearDonacionPendienteLibelula('tx-s3-10');

        $payload = [
            'event_id' => 'evt-s3-10',
            'transaction_id' => 'tx-s3-10',
            'status' => 'paid',
        ];

        $this->postJson(route('webhooks.libelula'), $payload)
            ->assertOk()
            ->assertJsonPath('message', 'processed');

        $this->assertSame(Donacion::ESTADO_VALIDADO, Donacion::query()->first()->estado_pago);

        $this->postJson(route('webhooks.libelula'), $payload)
            ->assertOk()
            ->assertJsonPath('message', 'already_processed')
            ->assertJsonPath('duplicate', true);

        $this->assertSame(1, ProcessedWebhook::query()->count());
    }

    public function test_xss_elimina_script_del_nombre_visitante(): void
    {
        [$campana, $tipoPago] = $this->crearCampanaActiva();

        $this->post(route('turista.donaciones.store'), [
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'visitante_nombre' => '<script>alert(1)</script>María',
            'monto' => 20,
            'metodo' => 'efectivo_vis',
            'payment_uuid' => (string) Str::uuid(),
        ])->assertRedirect();

        $visitante = Visitante::query()->first();

        $this->assertNotNull($visitante);
        $this->assertSame('María', $visitante->nombre);
        $this->assertStringNotContainsString('<script>', $visitante->nombre);
    }

    /**
     * @return array{0: Campana, 1: TipoPago}
     */
    private function crearCampanaActiva(): array
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Test',
            'apellidos' => 'Seguridad',
            'descripcion' => null,
            'fotografia' => null,
            'qr_url' => null,
            'estado' => 'activo',
            'meta_monto' => 0,
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña S3-10',
            'meta_apoyo' => 100,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $tipoPago = TipoPago::query()->create([
            'codigo' => 'efectivo_vis',
            'nombre' => 'Efectivo',
            'descripcion' => null,
            'activo' => true,
        ]);

        return [$campana, $tipoPago];
    }

    private function crearDonacionPendienteLibelula(string $transactionId): Donacion
    {
        [$campana, $tipoPago] = $this->crearCampanaActiva();

        $tipoLibelula = TipoPago::query()->firstOrCreate(
            ['codigo' => 'libelula_qr'],
            ['nombre' => 'Libélula', 'proveedor' => 'libelula', 'activo' => true],
        );

        return Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoLibelula->id,
            'monto' => 50,
            'metodo' => 'libelula_qr',
            'estado_pago' => Donacion::ESTADO_PENDIENTE,
            'referencia_pago' => 'WAYNA-S3-10',
            'transaction_id' => $transactionId,
            'proveedor_pago' => 'libelula',
        ]);
    }
}
