<?php

namespace Tests\Feature\Admin;

use App\Enums\Departamento;
use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmprendedorDepartamentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_debe_indicar_departamento_al_crear(): void
    {
        $admin = $this->crearUsuarioAdmin();

        $response = $this->actingAs($admin)->post(route('admin.emprendedores.store'), [
            'nombre' => 'Luis',
            'apellidos' => 'Mamani',
            'tipo_emprendimiento' => 'artesania',
            'descripcion' => 'Textiles andinos de alta calidad',
            'meta_monto' => 3000,
            'estado' => 'activo',
        ]);

        $response->assertSessionHasErrors('departamento');
    }

    public function test_admin_puede_crear_emprendedor_con_departamento_valido(): void
    {
        $admin = $this->crearUsuarioAdmin();

        $response = $this->actingAs($admin)->post(route('admin.emprendedores.store'), [
            'nombre' => 'Luis',
            'apellidos' => 'Mamani',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => Departamento::SantaCruz->value,
            'descripcion' => 'Textiles andinos de alta calidad',
            'meta_monto' => 3000,
            'estado' => 'activo',
        ]);

        $emprendedor = Emprendedor::query()->where('nombre', 'Luis')->first();
        $response->assertRedirect(route('admin.emprendedores.finalizar', $emprendedor));

        $this->assertNotNull($emprendedor);
        $this->assertSame(Departamento::SantaCruz, $emprendedor->departamento);
    }

    public function test_rechaza_departamento_invalido(): void
    {
        $admin = $this->crearUsuarioAdmin();

        $response = $this->actingAs($admin)->post(route('admin.emprendedores.store'), [
            'nombre' => 'Luis',
            'apellidos' => 'Mamani',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'amazonas',
            'descripcion' => 'Textiles andinos de alta calidad',
            'meta_monto' => 3000,
            'estado' => 'activo',
        ]);

        $response->assertSessionHasErrors('departamento');
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
