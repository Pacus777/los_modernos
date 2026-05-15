<?php

namespace Tests\Unit;

use App\Models\Donacion;
use App\Support\PagoPendienteTurista;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PagoPendienteTuristaTest extends TestCase
{
    public function test_calcula_vencimiento_y_segundos_restantes(): void
    {
        Config::set('wayna.pago_pendiente_minutos', 15);

        $creado = Carbon::parse('2026-05-15 10:00:00');
        $ahora = Carbon::parse('2026-05-15 10:05:00');

        $this->assertTrue(
            PagoPendienteTurista::venceEn($creado)->equalTo($creado->copy()->addMinutes(15)),
        );
        $this->assertFalse(PagoPendienteTurista::plazoVencido($creado, $ahora));
        $this->assertSame(600, PagoPendienteTurista::segundosRestantes($creado, $ahora));
    }

    public function test_detecta_plazo_vencido_sin_cambiar_estado(): void
    {
        Config::set('wayna.pago_pendiente_minutos', 15);

        $creado = Carbon::parse('2026-05-15 10:00:00');
        $ahora = Carbon::parse('2026-05-15 10:20:00');

        $this->assertTrue(PagoPendienteTurista::plazoVencido($creado, $ahora));
        $this->assertSame(0, PagoPendienteTurista::segundosRestantes($creado, $ahora));
    }

    public function test_para_confirmacion_incluye_datos_para_frontend(): void
    {
        Config::set('wayna.pago_pendiente_minutos', 15);

        $donacion = new Donacion([
            'estado_pago' => Donacion::ESTADO_PENDIENTE,
        ]);
        $donacion->created_at = Carbon::parse('2026-05-15 10:00:00');
        $ahora = Carbon::parse('2026-05-15 10:02:00');

        $payload = PagoPendienteTurista::paraConfirmacion($donacion, $ahora);

        $this->assertSame(15, $payload['minutos_plazo']);
        $this->assertFalse($payload['plazo_vencido']);
        $this->assertSame(780, $payload['segundos_restantes']);
        $this->assertArrayHasKey('vence_at', $payload);
        $this->assertArrayHasKey('created_at', $payload);
    }
}
