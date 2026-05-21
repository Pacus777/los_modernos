<?php

namespace Tests\Feature\Emprendedor;

use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use App\Services\EmprendedorOnboardingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmprendedorOnboardingServiceTest extends TestCase
{
    use RefreshDatabase;

    private EmprendedorOnboardingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(EmprendedorOnboardingService::class);
    }

    public function test_perfil_incompleto_devuelve_completo_false(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $emprendedor = $this->crearPerfilIncompleto($user);

        $checklist = $this->service->checklist($emprendedor);

        $this->assertFalse($checklist['completo']);
        $this->assertFalse($this->service->estaCompleto($emprendedor));
        $this->assertLessThan(100, $checklist['porcentaje']);
    }

    public function test_perfil_completo_con_bloqueantes_devuelve_completo_true(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $emprendedor = $this->crearPerfilCompleto($user);

        $checklist = $this->service->checklist($emprendedor);

        $this->assertTrue($checklist['completo']);
        $this->assertTrue($this->service->estaCompleto($emprendedor));
    }

    public function test_recomendados_incompletos_no_bloquean_el_acceso(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $emprendedor = $this->crearPerfilCompleto($user);

        // Sin redes ni campaña activa (recomendados)
        $emprendedor->update([
            'whatsapp' => null,
            'instagram' => null,
            'facebook' => null,
            'tiktok' => null,
            'sitio_web' => null,
        ]);

        $checklist = $this->service->checklist($emprendedor);

        $this->assertTrue($checklist['completo']);
        $this->assertTrue($this->service->estaCompleto($emprendedor));
        $this->assertLessThan(100, $checklist['porcentaje']); // El porcentaje debe ser menor a 100% si faltan recomendados
    }

    public function test_porcentaje_se_calcula_y_contiene_items(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $emprendedor = $this->crearPerfilIncompleto($user);

        $checklist = $this->service->checklist($emprendedor);

        $this->assertIsInt($checklist['porcentaje']);
        $this->assertGreaterThanOrEqual(0, $checklist['porcentaje']);
        $this->assertLessThanOrEqual(100, $checklist['porcentaje']);
        $this->assertCount(5, $checklist['items']);
    }

    public function test_checklist_devuelve_estructura_correcta_de_items(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $emprendedor = $this->crearPerfilIncompleto($user);

        $checklist = $this->service->checklist($emprendedor);

        foreach ($checklist['items'] as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('titulo', $item);
            $this->assertArrayHasKey('descripcion', $item);
            $this->assertArrayHasKey('completo', $item);
            $this->assertArrayHasKey('bloqueante', $item);
            $this->assertArrayHasKey('url', $item);
        }
    }

    private function crearUsuarioEmprendedor(): User
    {
        $rol = Rol::query()->firstOrCreate(['nombre' => 'emprendedor']);
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);
        UserRol::query()->create([
            'user_id' => $user->id,
            'role_id' => $rol->id,
        ]);

        return $user;
    }

    private function crearPerfilIncompleto(User $user): Emprendedor
    {
        return Emprendedor::query()->create([
            'user_id' => $user->id,
            'nombre' => '', // Incompleto
            'apellidos' => '', // Incompleto
            'descripcion' => '', // Incompleto
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'fotografia' => null, // Incompleto
            'estado' => 'activo',
        ]);
    }

    private function crearPerfilCompleto(User $user): Emprendedor
    {
        return Emprendedor::query()->create([
            'user_id' => $user->id,
            'nombre' => 'Juan Carlos',
            'apellidos' => 'Pérez Quispe',
            'descripcion' => 'Descripción de prueba con más de 10 caracteres.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'fotografia' => 'fotografias/perfil_test.jpg',
            'whatsapp' => '71234567',
            'estado' => 'activo',
        ]);
    }
}
