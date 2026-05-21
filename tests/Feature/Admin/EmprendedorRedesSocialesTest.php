<?php

namespace Tests\Feature\Admin;

use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EmprendedorRedesSocialesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_registrar_redes_al_crear_emprendedor(): void
    {
        $admin = $this->crearUsuarioAdmin();

        $response = $this->actingAs($admin)->post(route('admin.emprendedores.store'), [
            'nombre' => 'Rosa',
            'apellidos' => 'Mendoza',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'descripcion' => 'Tejidos de lana de alpaca.',
            'meta_monto' => 1500,
            'estado' => 'activo',
            'whatsapp' => '71234567',
            'instagram' => 'https://instagram.com/rosa_wayna',
            'facebook' => 'facebook.com/rosawayna',
            'tiktok' => 'https://tiktok.com/@rosawayna',
            'sitio_web' => 'wayna.example.com',
        ]);

        $emprendedor = Emprendedor::query()->where('nombre', 'Rosa')->first();
        $response->assertRedirect(route('admin.emprendedores.finalizar', $emprendedor));

        $this->assertNotNull($emprendedor);
        $this->assertSame('59171234567', $emprendedor->whatsapp);
        $this->assertSame('https://instagram.com/rosa_wayna', $emprendedor->instagram);
        $this->assertSame('facebook.com/rosawayna', $emprendedor->facebook);
        $this->assertSame('https://tiktok.com/@rosawayna', $emprendedor->tiktok);
        $this->assertSame('wayna.example.com', $emprendedor->sitio_web);
    }

    public function test_rechaza_whatsapp_con_menos_de_ocho_digitos(): void
    {
        $admin = $this->crearUsuarioAdmin();

        $response = $this->actingAs($admin)->post(route('admin.emprendedores.store'), [
            'nombre' => 'Ana',
            'apellidos' => 'Paz',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'descripcion' => 'Tejidos.',
            'meta_monto' => 1000,
            'estado' => 'activo',
            'whatsapp' => '71234',
        ]);

        $response->assertSessionHasErrors('whatsapp');
    }

    public function test_admin_puede_actualizar_redes(): void
    {
        $admin = $this->crearUsuarioAdmin();

        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Pedro',
            'apellidos' => 'Soto',
            'descripcion' => 'Miel.',
            'tipo_emprendimiento' => 'gastronomia',
            'departamento' => 'cochabamba',
            'estado' => 'activo',
            'meta_monto' => 500,
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.emprendedores.update', $emprendedor),
            [
                'nombre' => 'Pedro',
                'apellidos' => 'Soto',
                'tipo_emprendimiento' => 'gastronomia',
                'departamento' => 'cochabamba',
                'descripcion' => 'Miel orgánica.',
                'meta_monto' => 500,
                'estado' => 'activo',
                'whatsapp' => '',
                'instagram' => 'https://instagram.com/pedromiel',
                'facebook' => '',
                'tiktok' => '',
                'sitio_web' => 'https://pedromiel.bo',
            ],
        );

        $response->assertRedirect(route('admin.emprendedores.index'));

        $emprendedor->refresh();

        $this->assertNull($emprendedor->whatsapp);
        $this->assertSame('https://instagram.com/pedromiel', $emprendedor->instagram);
        $this->assertSame('https://pedromiel.bo', $emprendedor->sitio_web);
    }

    public function test_perfil_turista_expone_enlaces_normalizados(): void
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Lucía',
            'apellidos' => 'Vega',
            'descripcion' => 'Cerámica.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'santa_cruz',
            'estado' => 'activo',
            'meta_monto' => 800,
            'whatsapp' => '59170000001', // ya guardado con código país
            'instagram' => 'instagram.com/luciavega',
            'sitio_web' => 'https://luciavega.bo',
        ]);

        $response = $this->get(route('turista.emprendedor.show', $emprendedor));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Turista/Perfil')
            ->where('redes.whatsapp', 'https://wa.me/59170000001')
            ->where('redes.instagram', 'https://instagram.com/luciavega')
            ->where('redes.sitio_web', 'https://luciavega.bo')
            ->where('redes.facebook', null)
            ->where('redes.tiktok', null));
    }

    private function crearUsuarioAdmin(): User
    {
        $rol = Rol::query()->create(['nombre' => 'admin']);
        $user = User::factory()->create();

        UserRol::query()->create([
            'user_id' => $user->id,
            'role_id' => $rol->id,
        ]);

        return $user;
    }
}
