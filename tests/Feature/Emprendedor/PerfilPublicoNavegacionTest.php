<?php

namespace Tests\Feature\Emprendedor;

use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerfilPublicoNavegacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_emprendedor_ve_perfil_propio_con_flag_es_perfil_propio(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $emprendedor = $this->vincularEmprendedor($user);

        $this->actingAs($user)
            ->get(route('turista.emprendedor.show', $emprendedor))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Turista/Perfil')
                ->where('esPerfilPropio', true));
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

    private function vincularEmprendedor(User $user): Emprendedor
    {
        return Emprendedor::query()->create([
            'user_id' => $user->id,
            'nombre' => 'Richard',
            'apellidos' => 'Pérez',
            'descripcion' => 'Descripción de prueba con más de diez caracteres.',
            'tipo_emprendimiento' => 'textil',
            'departamento' => 'santa_cruz',
            'estado' => 'activo',
            'meta_monto' => 6000,
        ]);
    }
}
