<?php

namespace Tests\Feature\Admin;

use App\Models\Emprendedor;
use App\Models\Punto;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PuntoEmprendedoresSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_crear_punto_y_asociar_emprendedores(): void
    {
        Storage::fake('public');

        $admin = $this->crearUsuarioAdmin();
        $e1 = $this->crearEmprendedor(['nombre' => 'Ana']);
        $e2 = $this->crearEmprendedor(['nombre' => 'Luis']);

        $response = $this->actingAs($admin)->post(route('admin.puntos.store'), [
            'nombre' => 'Mesa principal',
            'descripcion' => 'Punto de feria',
            'ubicacion' => 'Feria Wayna',
            'estado' => 'activo',
            'emprendedores' => [$e1->id, $e2->id],
        ]);

        $response->assertRedirect(route('admin.puntos.index'));
        $response->assertSessionHas('success');

        $punto = Punto::query()->where('nombre', 'Mesa principal')->first();

        $this->assertNotNull($punto);
        $this->assertSame('mesa-principal', $punto->slug);
        $this->assertNotNull($punto->qr_url);
        Storage::disk('public')->assertExists($punto->qr_url);

        $this->assertEqualsCanonicalizing(
            [$e1->id, $e2->id],
            $punto->emprendedores()->pluck('emprendedor_id')->all(),
        );
    }

    public function test_admin_puede_actualizar_asociacion_de_emprendedores(): void
    {
        $admin = $this->crearUsuarioAdmin();
        $e1 = $this->crearEmprendedor(['nombre' => 'Ana']);
        $e2 = $this->crearEmprendedor(['nombre' => 'Luis']);
        $e3 = $this->crearEmprendedor(['nombre' => 'María']);

        $punto = Punto::query()->create([
            'nombre' => 'Mostrador',
            'slug' => 'mostrador',
            'descripcion' => null,
            'ubicacion' => null,
            'qr_url' => 'puntos/qrs/punto-test.png',
            'estado' => 'activo',
        ]);

        $punto->emprendedores()->sync([$e1->id, $e2->id]);

        $response = $this->actingAs($admin)->put(route('admin.puntos.update', $punto), [
            'nombre' => 'Mostrador actualizado',
            'descripcion' => null,
            'ubicacion' => 'Lobby',
            'estado' => 'activo',
            'emprendedores' => [$e2->id, $e3->id],
        ]);

        $response->assertRedirect(route('admin.puntos.index'));
        $response->assertSessionHas('success');

        $punto->refresh();

        $this->assertSame('Mostrador actualizado', $punto->nombre);
        $this->assertSame('mostrador', $punto->slug);

        $this->assertEqualsCanonicalizing(
            [$e2->id, $e3->id],
            $punto->emprendedores()->pluck('emprendedor_id')->all(),
        );
    }

    public function test_formulario_edicion_recibe_emprendedores_seleccionados(): void
    {
        $admin = $this->crearUsuarioAdmin();
        $e1 = $this->crearEmprendedor();
        $punto = Punto::query()->create([
            'nombre' => 'Punto test',
            'slug' => 'punto-test',
            'estado' => 'activo',
        ]);
        $punto->emprendedores()->sync([$e1->id]);

        $response = $this->actingAs($admin)->get(route('admin.puntos.edit', $punto));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Puntos/Form')
            ->where('modo', 'editar')
            ->has('emprendedores', 1)
            ->where('emprendedoresSeleccionados', [$e1->id]));
    }

    private function crearEmprendedor(array $extra = []): Emprendedor
    {
        return Emprendedor::query()->create(array_merge([
            'nombre' => 'Test',
            'apellidos' => 'Emprendedor',
            'descripcion' => null,
            'fotografia' => null,
            'qr_url' => null,
            'estado' => 'activo',
            'meta_monto' => 0,
        ], $extra));
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
