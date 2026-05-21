<?php

namespace Tests\Feature\Turista;

use App\Mail\ConfirmarSeguimientoEmprendedorMail;
use App\Models\Emprendedor;
use App\Models\EmprendedorSeguidor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SeguirEmprendedorTest extends TestCase
{
    use RefreshDatabase;

    public function test_solicitud_envia_correo_y_queda_pendiente(): void
    {
        Mail::fake();

        $emprendedor = $this->crearEmprendedor();

        $this->post(route('turista.seguir.store', $emprendedor->id), [
            'email' => 'turista@wayna.test',
        ])->assertRedirect(route('turista.emprendedor.show', $emprendedor->id));

        $registro = EmprendedorSeguidor::query()->first();

        $this->assertNotNull($registro);
        $this->assertNull($registro->confirmado_en);
        $this->assertNotNull($registro->token_confirm);
        $this->assertNotNull($registro->token_unsub);

        Mail::assertSent(ConfirmarSeguimientoEmprendedorMail::class);
    }

    public function test_confirmar_token_activa_seguimiento(): void
    {
        $emprendedor = $this->crearEmprendedor();

        $registro = EmprendedorSeguidor::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'visitante_id' => $this->crearVisitante()->id,
            'email' => 'confirm@wayna.test',
            'token_confirm' => 'token-confirm-test',
            'token_unsub' => 'token-unsub-test',
        ]);

        $this->get(route('turista.seguir.confirmar', ['token' => 'token-confirm-test']))
            ->assertOk();

        $registro->refresh();

        $this->assertNotNull($registro->confirmado_en);
        $this->assertNull($registro->token_confirm);
    }

    public function test_token_unsub_elimina_seguimiento(): void
    {
        $emprendedor = $this->crearEmprendedor();

        EmprendedorSeguidor::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'visitante_id' => $this->crearVisitante()->id,
            'email' => 'baja@wayna.test',
            'token_unsub' => 'token-baja-test',
            'confirmado_en' => now(),
        ]);

        $this->get(route('turista.seguir.baja', ['token' => 'token-baja-test']))
            ->assertOk();

        $this->assertSame(0, EmprendedorSeguidor::query()->count());
    }

    private function crearEmprendedor(): Emprendedor
    {
        return Emprendedor::query()->create([
            'nombre' => 'María',
            'apellidos' => 'Seguir',
            'descripcion' => 'Test S4-06',
            'estado' => 'activo',
            'meta_monto' => 0,
        ]);
    }

    private function crearVisitante(): \App\Models\Visitante
    {
        return \App\Models\Visitante::query()->create([
            'codigo' => 'VIS-'.uniqid(),
            'idioma' => 'es',
        ]);
    }
}
