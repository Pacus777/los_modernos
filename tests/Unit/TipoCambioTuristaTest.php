<?php

namespace Tests\Unit;

use App\Support\TipoCambioTurista;
use Tests\TestCase;

class TipoCambioTuristaTest extends TestCase
{
    public function test_convierte_bolivianos_a_usd_con_tasa_configurada(): void
    {
        config(['wayna.usd_por_bs' => 0.145]);

        $this->assertSame(7.25, TipoCambioTurista::bolivianosAUsd(50));
    }

    public function test_retorna_null_si_monto_invalido(): void
    {
        config(['wayna.usd_por_bs' => 0.145]);

        $this->assertNull(TipoCambioTurista::bolivianosAUsd(0));
        $this->assertNull(TipoCambioTurista::bolivianosAUsd(-10));
    }

    public function test_desactivado_si_tasa_es_cero(): void
    {
        config(['wayna.usd_por_bs' => 0]);

        $this->assertFalse(TipoCambioTurista::estaActivo());
        $this->assertNull(TipoCambioTurista::bolivianosAUsd(50));
    }
}
