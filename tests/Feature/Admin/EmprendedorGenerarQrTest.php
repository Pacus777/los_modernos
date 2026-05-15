<?php

namespace Tests\Feature\Admin;

use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmprendedorGenerarQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_generar_qr_si_esta_pendiente(): void
    {
        Storage::fake('public');

        $admin = $this->crearUsuarioAdmin();
        $emprendedor = $this->crearEmprendedor(['qr_url' => null]);

        $response = $this->actingAs($admin)->post(
            route('admin.emprendedores.generar-qr', $emprendedor),
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $emprendedor->refresh();

        $this->assertNotNull($emprendedor->qr_url);
        Storage::disk('public')->assertExists($emprendedor->qr_url);
    }

    public function test_no_regenera_si_ya_tiene_qr(): void
    {
        $admin = $this->crearUsuarioAdmin();
        $emprendedor = $this->crearEmprendedor([
            'qr_url' => 'emprendedores/qrs/emprendedor-99.png',
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.emprendedores.generar-qr', $emprendedor),
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
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
