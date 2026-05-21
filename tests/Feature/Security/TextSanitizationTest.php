<?php

namespace Tests\Feature\Security;

use App\Models\Campana;
use App\Models\Emprendedor;
use App\Models\TipoPago;
use App\Models\Visitante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TextSanitizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_donacion_quita_etiquetas_del_nombre_visitante(): void
    {
        [$campana, $tipoPago] = $this->crearCampanaActiva();

        $this->post(route('turista.donaciones.store'), [
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'visitante_nombre' => '<script>alert(1)</script>María',
            'monto' => 20,
            'metodo' => 'efectivo_vis',
            'payment_uuid' => (string) Str::uuid(),
        ])->assertRedirect();

        $visitante = Visitante::query()->first();

        $this->assertNotNull($visitante);
        $this->assertSame('María', $visitante->nombre);
        $this->assertStringNotContainsString('<script>', $visitante->nombre);
    }

    public function test_chat_rechaza_pregunta_vacia_tras_limpiar_html(): void
    {
        $this->postJson(route('chat.store'), [
            'pregunta' => '<b></b>',
            'idioma' => 'es',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['pregunta']);
    }

    /**
     * @return array{0: Campana, 1: TipoPago}
     */
    private function crearCampanaActiva(): array
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Test',
            'apellidos' => 'XSS',
            'descripcion' => null,
            'fotografia' => null,
            'qr_url' => null,
            'estado' => 'activo',
            'meta_monto' => 0,
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña',
            'meta_apoyo' => 100,
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
