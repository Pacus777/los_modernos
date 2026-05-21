<?php

namespace Tests\Feature\Security;

use App\Models\Campana;
use App\Models\Emprendedor;
use App\Models\TipoPago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EndpointRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_devuelve_429_al_superar_limite_de_ruta(): void
    {
        config([
            'wayna.rate_limit.login.max_attempts' => 2,
            'wayna.rate_limit.login.decay_minutes' => 1,
            'wayna.rate_limit.login.failed_max_attempts' => 50,
        ]);

        User::factory()->create(['email' => 'limite@wayna.test']);

        $payload = [
            'email' => 'limite@wayna.test',
            'password' => 'incorrecta',
        ];

        $this->post('/login', $payload)->assertSessionHasErrors('email');
        $this->post('/login', $payload)->assertSessionHasErrors('email');

        $this->post('/login', $payload)->assertStatus(429);
    }

    public function test_donaciones_devuelve_429_al_superar_limite(): void
    {
        config([
            'wayna.rate_limit.donaciones.max_attempts' => 2,
            'wayna.rate_limit.donaciones.decay_minutes' => 1,
        ]);

        [$campana, $tipoPago] = $this->crearCampanaActiva();

        $base = [
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => 10,
            'metodo' => 'efectivo_vis',
        ];

        $this->post(route('turista.donaciones.store'), $base + [
            'payment_uuid' => (string) Str::uuid(),
        ])->assertRedirect();

        $this->post(route('turista.donaciones.store'), $base + [
            'payment_uuid' => (string) Str::uuid(),
        ])->assertRedirect();

        $this->post(route('turista.donaciones.store'), $base + [
            'payment_uuid' => (string) Str::uuid(),
        ])->assertStatus(429);
    }

    public function test_webhook_devuelve_429_al_superar_limite(): void
    {
        config([
            'wayna.rate_limit.webhooks.max_attempts' => 2,
            'wayna.rate_limit.webhooks.decay_minutes' => 1,
        ]);

        $payload = ['status' => 'paid'];

        $this->postJson(route('webhooks.libelula'), $payload)->assertUnprocessable();
        $this->postJson(route('webhooks.libelula'), $payload)->assertUnprocessable();

        $this->postJson(route('webhooks.libelula'), $payload)->assertStatus(429);
    }

    /**
     * @return array{0: Campana, 1: TipoPago}
     */
    private function crearCampanaActiva(): array
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Test',
            'apellidos' => 'Rate',
            'descripcion' => null,
            'fotografia' => null,
            'qr_url' => null,
            'estado' => 'activo',
            'meta_monto' => 0,
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña rate limit',
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

        return [$campana, $tipoPago];
    }
}
