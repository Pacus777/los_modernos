<?php

namespace Tests\Feature\Turista;

use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\TipoPago;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PreventDuplicatePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_rechaza_segundo_post_con_mismo_payment_uuid(): void
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

        $this->post(route('turista.donaciones.store'), $payload)
            ->assertRedirect();

        $this->assertSame(1, Donacion::query()->count());

        $this->post(route('turista.donaciones.store'), $payload)
            ->assertSessionHasErrors('payment_uuid');

        $this->assertSame(1, Donacion::query()->count());
    }

    public function test_permite_dos_donaciones_con_payment_uuid_distintos(): void
    {
        [$campana, $tipoPago] = $this->crearCampanaActiva();

        $this->post(route('turista.donaciones.store'), [
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => 10,
            'metodo' => 'efectivo_vis',
            'payment_uuid' => (string) Str::uuid(),
        ])->assertRedirect();

        $this->post(route('turista.donaciones.store'), [
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => 20,
            'metodo' => 'efectivo_vis',
            'payment_uuid' => (string) Str::uuid(),
        ])->assertRedirect();

        $this->assertSame(2, Donacion::query()->count());
    }

    public function test_rechaza_payment_uuid_invalido(): void
    {
        [$campana, $tipoPago] = $this->crearCampanaActiva();

        $this->post(route('turista.donaciones.store'), [
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => 10,
            'metodo' => 'efectivo_vis',
            'payment_uuid' => 'no-es-uuid',
        ])->assertSessionHasErrors('payment_uuid');

        $this->assertSame(0, Donacion::query()->count());
    }

    public function test_guarda_payment_uuid_en_la_donacion(): void
    {
        [$campana, $tipoPago] = $this->crearCampanaActiva();
        $uuid = (string) Str::uuid();

        $this->post(route('turista.donaciones.store'), [
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => 30,
            'metodo' => 'efectivo_vis',
            'payment_uuid' => $uuid,
        ])->assertRedirect();

        $donacion = Donacion::query()->first();

        $this->assertNotNull($donacion);
        $this->assertSame(strtolower($uuid), $donacion->payment_uuid);
    }

    /**
     * @return array{0: Campana, 1: TipoPago}
     */
    private function crearCampanaActiva(): array
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Test',
            'apellidos' => 'Emprendedor',
            'descripcion' => null,
            'fotografia' => null,
            'qr_url' => null,
            'estado' => 'activo',
            'meta_monto' => 0,
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña test',
            'meta_apoyo' => 500,
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
}
