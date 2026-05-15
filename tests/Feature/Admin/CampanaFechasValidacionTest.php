<?php

namespace Tests\Feature\Admin;

use App\Models\Campana;
use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampanaFechasValidacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_fecha_fin_no_puede_ser_anterior_a_fecha_inicio(): void
    {
        $admin = $this->crearUsuarioAdmin();
        $emprendedor = $this->crearEmprendedor();

        $response = $this->actingAs($admin)->post(route('admin.campanas.store'), [
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña prueba',
            'meta_apoyo' => 1000,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->subDay()->toDateString(),
            'estado' => Campana::ESTADO_INACTIVA,
        ]);

        $response->assertSessionHasErrors('fecha_fin');
    }

    public function test_fecha_inicio_debe_ser_hoy_o_posterior_al_crear(): void
    {
        $admin = $this->crearUsuarioAdmin();
        $emprendedor = $this->crearEmprendedor();

        $response = $this->actingAs($admin)->post(route('admin.campanas.store'), [
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña futura',
            'meta_apoyo' => 1000,
            'fecha_inicio' => now()->subDays(2)->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => Campana::ESTADO_INACTIVA,
        ]);

        $response->assertSessionHasErrors('fecha_inicio');
    }

    public function test_fecha_fin_es_obligatoria(): void
    {
        $admin = $this->crearUsuarioAdmin();
        $emprendedor = $this->crearEmprendedor();

        $response = $this->actingAs($admin)->post(route('admin.campanas.store'), [
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Sin fecha fin',
            'meta_apoyo' => 1000,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => '',
            'estado' => Campana::ESTADO_INACTIVA,
        ]);

        $response->assertSessionHasErrors('fecha_fin');
    }

    public function test_campana_vencida_no_aparece_en_perfil_turista(): void
    {
        $emprendedor = $this->crearEmprendedor();

        Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña vencida',
            'meta_apoyo' => 500,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->subMonth()->toDateString(),
            'fecha_fin' => now()->subDay()->toDateString(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $response = $this->get(route('turista.emprendedor.show', $emprendedor->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Turista/Perfil')
            ->where('campanaActiva', null)
        );
    }

    public function test_modelo_detecta_campana_vencida(): void
    {
        $campana = new Campana([
            'estado' => Campana::ESTADO_ACTIVA,
            'fecha_inicio' => now()->subMonth()->toDateString(),
            'fecha_fin' => now()->subDay()->toDateString(),
        ]);

        $this->assertFalse($campana->estaVisibleEnPerfilTurista());
    }

    private function crearEmprendedor(): Emprendedor
    {
        return Emprendedor::query()->create([
            'nombre' => 'Test',
            'apellidos' => 'Emprendedor',
            'descripcion' => null,
            'fotografia' => null,
            'qr_url' => null,
            'estado' => 'activo',
            'meta_monto' => 0,
        ]);
    }

    private function crearUsuarioAdmin(): User
    {
        $rol = Rol::query()->create(['nombre' => 'admin']);
        $user = User::factory()->create();

        UserRol::query()->create([
            'user_id' => $user->id,
            'role_id' => $rol->id,
        ]);

        return $user->fresh();
    }
}
