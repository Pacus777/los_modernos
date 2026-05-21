<?php

namespace Tests\Unit\Support;

use App\Models\Donacion;
use App\Models\TipoPago;
use App\Support\WaynaBcpQr;
use Tests\TestCase;

class WaynaBcpQrTest extends TestCase
{
    public function test_aplica_a_tipo_pago_banco_qr(): void
    {
        config(['wayna.bcp_qr.enabled' => true]);

        $donacion = new Donacion([
            'metodo' => 'qr',
            'monto' => 50,
        ]);

        $donacion->setRelation('tipoPago', new TipoPago([
            'codigo' => 'qr',
            'proveedor' => TipoPago::PROVEEDOR_BANCO,
        ]));

        $this->assertTrue(WaynaBcpQr::aplicaADonacion($donacion));
    }

    public function test_no_aplica_a_libelula_ni_efectivo(): void
    {
        config(['wayna.bcp_qr.enabled' => true]);

        $libelula = new Donacion(['metodo' => 'tarjeta']);
        $libelula->setRelation('tipoPago', new TipoPago(['proveedor' => TipoPago::PROVEEDOR_LIBELULA]));
        $this->assertFalse(WaynaBcpQr::aplicaADonacion($libelula));

        $efectivo = new Donacion(['metodo' => 'efectivo']);
        $efectivo->setRelation('tipoPago', new TipoPago(['proveedor' => TipoPago::PROVEEDOR_MANUAL]));
        $this->assertFalse(WaynaBcpQr::aplicaADonacion($efectivo));
    }
}
