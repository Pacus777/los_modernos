<?php

namespace Tests\Feature;

use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\TipoPago;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonacionCampanaMontoRecaudadoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: Emprendedor, 1: Campana, 2: TipoPago}
     */
    private function crearEmprendedorCampanaYTipo(): array
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Prueba',
            'descripcion' => null,
            'fotografia' => null,
            'qr_url' => null,
            'estado' => 'activo',
            'meta_monto' => 0,
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña test',
            'meta_apoyo' => 1000,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $tipoPago = TipoPago::query()->create([
            'codigo' => 'efectivo_test',
            'nombre' => 'Efectivo',
            'descripcion' => null,
            'activo' => true,
        ]);

        return [$emprendedor, $campana, $tipoPago];
    }

    public function test_al_validar_donacion_se_incrementa_monto_recaudado_de_la_campana(): void
    {
        [, $campana, $tipoPago] = $this->crearEmprendedorCampanaYTipo();

        $donacion = Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'visitante_id' => null,
            'monto' => 50.25,
            'metodo' => 'efectivo',
            'estado_pago' => Donacion::ESTADO_PENDIENTE,
            'referencia_pago' => 'REF-1',
        ]);

        $this->assertSame('0.00', $campana->fresh()->monto_recaudado);

        $donacion->update(['estado_pago' => Donacion::ESTADO_VALIDADO]);

        $this->assertEquals('50.25', $campana->fresh()->monto_recaudado);
    }

    public function test_al_pasar_de_validado_a_rechazado_se_decrementa_monto_recaudado(): void
    {
        [, $campana, $tipoPago] = $this->crearEmprendedorCampanaYTipo();

        $donacion = Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'visitante_id' => null,
            'monto' => 40,
            'metodo' => 'efectivo',
            'estado_pago' => Donacion::ESTADO_VALIDADO,
            'referencia_pago' => 'REF-2',
        ]);

        $this->assertEquals('40.00', $campana->fresh()->monto_recaudado);

        $donacion->update(['estado_pago' => Donacion::ESTADO_RECHAZADO]);

        $this->assertEquals('0.00', $campana->fresh()->monto_recaudado);
    }
}
