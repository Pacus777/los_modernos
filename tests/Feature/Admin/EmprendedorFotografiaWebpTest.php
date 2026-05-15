<?php

namespace Tests\Feature\Admin;

use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use App\Services\ImageStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmprendedorFotografiaWebpTest extends TestCase
{
    use RefreshDatabase;

    public function test_fotografia_jpg_se_guarda_como_webp_cuando_gd_lo_permite(): void
    {
        $service = app(ImageStorageService::class);

        if (! $service->supportsWebpConversion()) {
            $this->markTestSkipped('Se requiere extensión GD con soporte WebP.');
        }

        Storage::fake('public');

        $file = UploadedFile::fake()->image('perfil.jpg', 800, 600);

        $path = $service->storePublicImageAsWebp($file, 'emprendedores/fotografias');

        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);

        $mime = mime_content_type(Storage::disk('public')->path($path));
        $this->assertSame('image/webp', $mime);
    }

    public function test_admin_puede_crear_emprendedor_con_fotografia_webp(): void
    {
        if (! app(ImageStorageService::class)->supportsWebpConversion()) {
            $this->markTestSkipped('Se requiere extensión GD con soporte WebP.');
        }

        Storage::fake('public');

        $admin = $this->crearUsuarioAdmin();

        $response = $this->actingAs($admin)->post(route('admin.emprendedores.store'), [
            'nombre' => 'Camila',
            'apellidos' => 'Sanchez',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'descripcion' => 'Manillas artesanales hechas a mano en la región.',
            'meta_monto' => 4000,
            'estado' => 'activo',
            'fotografia' => UploadedFile::fake()->image('foto.png', 400, 400),
        ]);

        $response->assertRedirect(route('admin.emprendedores.index'));

        $emprendedor = Emprendedor::query()->where('nombre', 'Camila')->first();

        $this->assertNotNull($emprendedor);
        $this->assertNotNull($emprendedor->fotografia);
        $this->assertStringEndsWith('.webp', $emprendedor->fotografia);
        Storage::disk('public')->assertExists($emprendedor->fotografia);
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
