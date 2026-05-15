<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoEmprendimiento;
use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmprendedorTipoEmprendimientoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_debe_indicar_tipo_emprendimiento_al_crear(): void
    {
        $admin = $this->crearUsuarioAdmin();

        $response = $this->actingAs($admin)->post(route('admin.emprendedores.store'), [
            'nombre' => 'Ana',
            'apellidos' => 'Ríos',
            'descripcion' => 'Comida típica chiquitania',
            'meta_monto' => 5000,
            'estado' => 'activo',
        ]);

        $response->assertSessionHasErrors('tipo_emprendimiento');
    }

    public function test_admin_puede_crear_emprendedor_con_tipo_valido(): void
    {
        $admin = $this->crearUsuarioAdmin();

        $response = $this->actingAs($admin)->post(route('admin.emprendedores.store'), [
            'nombre' => 'Ana',
            'apellidos' => 'Ríos',
            'tipo_emprendimiento' => TipoEmprendimiento::Gastronomia->value,
            'departamento' => 'chuquisaca',
            'descripcion' => 'Comida típica de la Chiquitania boliviana',
            'meta_monto' => 5000,
            'estado' => 'activo',
        ]);

        $response->assertRedirect(route('admin.emprendedores.index'));

        $emprendedor = Emprendedor::query()->where('nombre', 'Ana')->first();

        $this->assertNotNull($emprendedor);
        $this->assertSame(TipoEmprendimiento::Gastronomia, $emprendedor->tipo_emprendimiento);
    }

    public function test_rechaza_tipo_emprendimiento_invalido(): void
    {
        $admin = $this->crearUsuarioAdmin();

        $response = $this->actingAs($admin)->post(route('admin.emprendedores.store'), [
            'nombre' => 'Ana',
            'apellidos' => 'Ríos',
            'tipo_emprendimiento' => 'minería',
            'departamento' => 'la_paz',
            'descripcion' => 'Descripción válida de prueba',
            'meta_monto' => 5000,
            'estado' => 'activo',
        ]);

        $response->assertSessionHasErrors('tipo_emprendimiento');
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
