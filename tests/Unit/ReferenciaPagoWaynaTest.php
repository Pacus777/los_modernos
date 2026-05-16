<?php

namespace Tests\Unit;

use App\Support\ReferenciaPagoWayna;
use Carbon\Carbon;
use Tests\TestCase;

class ReferenciaPagoWaynaTest extends TestCase
{
    public function test_genera_formato_wayna_fecha_codigo(): void
    {
        $fecha = Carbon::parse('2026-05-15 14:30:00');

        $referencia = ReferenciaPagoWayna::generar($fecha);

        $this->assertMatchesRegularExpression('/^WAYNA-20260515-[A-Z0-9]{5}$/', $referencia);
    }

    public function test_valida_formato_correcto(): void
    {
        $this->assertTrue(ReferenciaPagoWayna::esFormatoValido('WAYNA-20260515-ABCDE'));
        $this->assertFalse(ReferenciaPagoWayna::esFormatoValido('REF-123'));
    }
}
