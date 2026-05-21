<?php

namespace Tests\Unit;

use App\Services\TipoCambioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TipoCambioServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_usa_valor_referencial_bcb_como_en_bcb_gob_bo(): void
    {
        Http::fake([
            'https://bcb.cucu.bo/api/v1/tc/usd' => Http::response([
                'tc_referencial_usd' => [
                    'compra' => 9.92,
                    'venta' => 10.13,
                    'moneda' => 'USD/BOB',
                    'fecha' => '2026-05-21',
                ],
                'fuente' => 'Banco Central de Bolivia (bcb.gob.bo)',
            ]),
        ]);

        config([
            'tipocambio.enabled' => true,
            'tipocambio.provider' => 'bcb',
            'tipocambio.tipo' => 'referencial',
        ]);

        $datos = app(TipoCambioService::class)->obtenerUsdBobReferencial();

        $this->assertEquals(9.92, $datos['compra']);
        $this->assertEquals(10.13, $datos['usd_to_bob']);
        $this->assertEqualsWithDelta(round(50 / 10.13, 2), round(50 * $datos['usd_por_bs'], 2), 0.02);
        $this->assertStringContainsString('Banco Central', $datos['source']);
        $this->assertSame('referencial', $datos['tipo_bcb']);
    }
}
