<?php

namespace Tests\Unit;

use App\Support\TipoCambioTurista;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TipoCambioTuristaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        config([
            'tipocambio.enabled' => true,
            'tipocambio.provider' => 'bcb',
            'tipocambio.tipo' => 'referencial',
        ]);

        Http::fake([
            'https://bcb.cucu.bo/api/v1/tc/usd' => Http::response([
                'tc_referencial_usd' => [
                    'compra' => 9.92,
                    'venta' => 10.13,
                    'fecha' => '2026-05-21',
                ],
                'fuente' => 'Banco Central de Bolivia (bcb.gob.bo)',
            ]),
        ]);
    }

    public function test_convierte_con_dolar_referencial_bcb_venta(): void
    {
        $datos = TipoCambioTurista::paraFrontend();

        $this->assertTrue($datos['activo']);
        $this->assertEquals(10.13, $datos['usd_to_bob']);
        $this->assertEquals(9.92, $datos['compra']);
        $this->assertEqualsWithDelta(round(50 / 10.13, 2), TipoCambioTurista::bolivianosAUsd(50), 0.02);
    }

    public function test_retorna_null_si_monto_invalido(): void
    {
        $this->assertNull(TipoCambioTurista::bolivianosAUsd(0));
    }

    public function test_desactivado_si_tipo_cambio_esta_apagado(): void
    {
        Cache::flush();
        config(['tipocambio.enabled' => false]);

        $this->assertFalse(TipoCambioTurista::estaActivo());
    }
}
