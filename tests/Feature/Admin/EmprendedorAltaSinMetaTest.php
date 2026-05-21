<?php

namespace Tests\Feature\Admin;

use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmprendedorAltaSinMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_alta_sin_meta_referencial_guarda_cero_y_redirige_a_finalizar(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->post(route('admin.emprendedores.store'), [
            'nombre' => 'Richard',
            'apellidos' => 'Pérez',
            'tipo_emprendimiento' => 'textil',
            'departamento' => 'santa_cruz',
            'descripcion' => 'Textiles artesanales de Santa Cruz.',
            'estado' => 'activo',
        ]);

        $emprendedor = Emprendedor::query()->where('nombre', 'Richard')->first();
        $this->assertNotNull($emprendedor);
        $response->assertRedirect(route('admin.emprendedores.finalizar', $emprendedor));
        $this->assertEquals(0.0, (float) $emprendedor->meta_monto);
    }

    public function test_alta_sin_descripcion_es_valida(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->post(route('admin.emprendedores.store'), [
            'nombre' => 'Sin',
            'apellidos' => 'Descripcion',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'activo',
        ]);

        $emprendedor = Emprendedor::query()->where('nombre', 'Sin')->first();
        $response->assertRedirect(route('admin.emprendedores.finalizar', $emprendedor));
        $this->assertNull($emprendedor->descripcion);
    }

    private function crearAdmin(): User
    {
        $rol = Rol::query()->create(['nombre' => 'admin']);
        $user = User::factory()->create();
        UserRol::query()->create(['user_id' => $user->id, 'role_id' => $rol->id]);

        return $user;
    }
}
