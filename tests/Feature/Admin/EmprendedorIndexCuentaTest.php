<?php

namespace Tests\Feature\Admin;

use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmprendedorIndexCuentaTest extends TestCase
{
    use RefreshDatabase;

    public function test_directorio_incluye_resumen_de_cuenta_vinculada(): void
    {
        $admin = $this->crearAdmin();
        $this->asegurarRolEmprendedor();

        $user = User::factory()->create([
            'email' => 'ana.torres@gmail.com',
            'name' => 'Ana Torres',
        ]);

        UserRol::query()->create([
            'user_id' => $user->id,
            'role_id' => Rol::query()->where('nombre', 'emprendedor')->value('id'),
        ]);

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

        $response = $this->actingAs($admin)->get(route('admin.emprendedores.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Emprendedores/Index')
            ->has('emprendedores.data', 1)
            ->where('emprendedores.data.0.cuenta.tiene_cuenta', true)
            ->where('emprendedores.data.0.cuenta.email', 'ana.torres@gmail.com'));
    }

    private function crearAdmin(): User
    {
        $rol = Rol::query()->firstOrCreate(['nombre' => 'admin']);
        $user = User::factory()->create();
        UserRol::query()->create([
            'user_id' => $user->id,
            'role_id' => $rol->id,
        ]);

        return $user;
    }

    private function asegurarRolEmprendedor(): void
    {
        Rol::query()->firstOrCreate(['nombre' => 'emprendedor']);
    }
}
