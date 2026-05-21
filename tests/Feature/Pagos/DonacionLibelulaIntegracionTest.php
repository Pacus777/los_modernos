<?php

namespace Tests\Feature\Pagos;

use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\TipoPago;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class DonacionLibelulaIntegracionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        config([
            'libelula.app_key' => null,
            'libelula.fake_when_unconfigured' => true,
        ]);
    }

    public function test_registrar_donacion_tarjeta_asigna_transaction_id_simulado(): void
    {
        Http::fake();

        [$campana, $tipoTarjeta] = $this->escenario();

        $uuid = (string) Str::uuid();

        $this->post(route('turista.donaciones.store'), [
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoTarjeta->id,
            'monto' => 40,
            'metodo' => 'tarjeta',
            'payment_uuid' => $uuid,
        ])->assertRedirect();

        $donacion = Donacion::query()->where('payment_uuid', $uuid)->first();

        $this->assertNotNull($donacion);
        $this->assertNotNull($donacion->transaction_id);
        $this->assertNotNull($donacion->checkout_url);
        $this->assertSame(Donacion::PROVEEDOR_LIBELULA, $donacion->proveedor_pago);
    }

    /**
     * @return array{0: Campana, 1: TipoPago}
     */
    private function escenario(): array
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Marta',
            'apellidos' => 'Pago',
            'descripcion' => 'Descripción de prueba con más de diez caracteres para perfil.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'activo',
            'meta_monto' => 100,
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña activa',
            'meta_apoyo' => 1000,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addMonth(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $tipoTarjeta = TipoPago::query()->create([
            'codigo' => 'tarjeta',
            'nombre' => 'Tarjeta',
            'proveedor' => TipoPago::PROVEEDOR_LIBELULA,
            'activo' => true,
            'requiere_validacion_manual' => false,
        ]);

        TipoPago::query()->create([
            'codigo' => 'efectivo',
            'nombre' => 'Efectivo',
            'proveedor' => 'manual',
            'activo' => true,
            'requiere_validacion_manual' => true,
        ]);

        return [$campana, $tipoTarjeta];
    }
}
