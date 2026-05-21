<?php

namespace Tests\Feature\Emprendedor;

use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_emprendedor_accede_a_su_panel(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        Emprendedor::query()->create([
            'user_id' => $user->id,
            'nombre' => 'Ana',
            'apellidos' => 'Torres',
            'descripcion' => 'Descripción de prueba con más de diez caracteres.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'activo',
            'meta_monto' => 500,
        ]);

        $response = $this->actingAs($user)->get(route('emprendedor.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Emprendedor/Dashboard')
            ->where('emprendedor.nombre_completo', 'Ana Torres'));
    }

    public function test_turista_no_puede_acceder_al_panel_emprendedor(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('emprendedor.dashboard'))
            ->assertForbidden();
    }

    private function crearUsuarioEmprendedor(): User
    {
        $rol = Rol::query()->firstOrCreate(['nombre' => 'emprendedor']);
        $user = User::factory()->create();
        UserRol::query()->create([
            'user_id' => $user->id,
            'role_id' => $rol->id,
        ]);

        return $user;
    }
}
