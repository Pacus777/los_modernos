<?php

namespace Tests\Feature\Turista;

use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\TipoPago;
use App\Models\Visitante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DonacionVisitanteNombreTest extends TestCase
{
    use RefreshDatabase;

    public function test_donacion_con_nombre_opcional_crea_visitante_y_lo_asocia(): void
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Turista',
            'descripcion' => null,
            'fotografia' => null,
            'qr_url' => null,
            'estado' => 'activo',
            'meta_monto' => 0,
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Meta test',
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

        $response = $this->post(route('turista.donaciones.store'), [
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'visitante_nombre' => 'María Turista',
            'monto' => 25,
            'metodo' => 'efectivo_vis',
            'payment_uuid' => (string) Str::uuid(),
        ]);

        $response->assertRedirect();

        $donacion = Donacion::query()->latest('id')->first();

        $this->assertNotNull($donacion);
        $this->assertNotNull($donacion->visitante_id);

        $visitante = Visitante::query()->find($donacion->visitante_id);

        $this->assertNotNull($visitante);
        $this->assertSame('María Turista', $visitante->nombre);
        $this->assertNotEmpty($visitante->codigo);
    }

    public function test_donacion_sin_nombre_no_crea_visitante(): void
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Luis',
            'apellidos' => 'Test',
            'descripcion' => null,
            'fotografia' => null,
            'qr_url' => null,
            'estado' => 'activo',
            'meta_monto' => 0,
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña',
            'meta_apoyo' => 200,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $tipoPago = TipoPago::query()->create([
            'codigo' => 'qr_vis',
            'nombre' => 'QR',
            'descripcion' => null,
            'activo' => true,
        ]);

        $this->post(route('turista.donaciones.store'), [
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'visitante_nombre' => '',
            'monto' => 10,
            'metodo' => 'qr_vis',
            'payment_uuid' => (string) Str::uuid(),
        ])->assertRedirect();

        $donacion = Donacion::query()->latest('id')->first();

        $this->assertNull($donacion->visitante_id);
        $this->assertSame(0, Visitante::query()->count());
    }
}
