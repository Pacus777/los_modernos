<?php

namespace Tests\Feature\Emprendedor;

use App\Enums\AuditAction;
use App\Models\Campana;
use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmprendedorMetaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_emprendedor_crea_meta_activa(): void
    {
        [$user, $emprendedor] = $this->escenarioEmprendedor();

        $hoy = now()->toDateString();
        $fin = now()->addMonth()->toDateString();

        $response = $this->actingAs($user)->post(route('emprendedor.meta.store'), [
            'titulo' => 'Ampliar mi taller',
            'meta_apoyo' => 3500,
            'fecha_inicio' => $hoy,
            'fecha_fin' => $fin,
        ]);

        $response->assertRedirect(route('emprendedor.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('campanas', [
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Ampliar mi taller',
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::EmprendedorMetaCreated->value,
            'user_id' => $user->id,
        ]);
    }

    public function test_no_puede_crear_segunda_meta_activa(): void
    {
        [$user, $emprendedor] = $this->escenarioEmprendedor();

        $this->crearCampanaActiva($emprendedor);

        $hoy = now()->toDateString();

        $this->actingAs($user)
            ->post(route('emprendedor.meta.store'), [
                'titulo' => 'Otra meta',
                'meta_apoyo' => 1000,
                'fecha_inicio' => $hoy,
                'fecha_fin' => now()->addWeeks(2)->toDateString(),
            ])
            ->assertSessionHasErrors('titulo');
    }

    public function test_emprendedor_actualiza_su_meta(): void
    {
        [$user, $emprendedor] = $this->escenarioEmprendedor();
        $campana = $this->crearCampanaActiva($emprendedor);

        $response = $this->actingAs($user)->put(route('emprendedor.meta.update', $campana), [
            'titulo' => 'Meta actualizada',
            'meta_apoyo' => 5000,
            'fecha_inicio' => $campana->fecha_inicio->toDateString(),
            'fecha_fin' => now()->addMonths(2)->toDateString(),
        ]);

        $response->assertRedirect(route('emprendedor.dashboard'));
        $campana->refresh();
        $this->assertSame('Meta actualizada', $campana->titulo);
        $this->assertEquals(5000.0, (float) $campana->meta_apoyo);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::EmprendedorMetaUpdated->value,
            'user_id' => $user->id,
        ]);
    }

    public function test_emprendedor_cierra_su_meta(): void
    {
        [$user, $emprendedor] = $this->escenarioEmprendedor();
        $campana = $this->crearCampanaActiva($emprendedor);

        $response = $this->actingAs($user)->post(route('emprendedor.meta.close', $campana));

        $response->assertRedirect(route('emprendedor.dashboard'));
        $campana->refresh();
        $this->assertSame(Campana::ESTADO_FINALIZADA, $campana->estado);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::EmprendedorMetaClosed->value,
            'user_id' => $user->id,
        ]);
    }

    public function test_emprendedor_no_modifica_meta_de_otro(): void
    {
        [$userA, $empA] = $this->escenarioEmprendedor('Ana');
        [, $empB] = $this->escenarioEmprendedor('Luis');
        $campanaB = $this->crearCampanaActiva($empB);

        $this->actingAs($userA)
            ->put(route('emprendedor.meta.update', $campanaB), [
                'titulo' => 'Hack',
                'meta_apoyo' => 1,
                'fecha_inicio' => now()->toDateString(),
                'fecha_fin' => now()->addMonth()->toDateString(),
            ])
            ->assertForbidden();

        $this->actingAs($userA)
            ->post(route('emprendedor.meta.close', $campanaB))
            ->assertForbidden();
    }

    public function test_formulario_crear_meta(): void
    {
        [$user] = $this->escenarioEmprendedor();

        $this->actingAs($user)
            ->get(route('emprendedor.meta.create'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Emprendedor/Meta/Form')
                ->where('modo', 'crear'));
    }

    /**
     * @return array{0: User, 1: Emprendedor}
     */
    private function escenarioEmprendedor(string $nombre = 'Ana'): array
    {
        $rol = Rol::query()->firstOrCreate(['nombre' => 'emprendedor']);
        $user = User::factory()->create();
        UserRol::query()->create(['user_id' => $user->id, 'role_id' => $rol->id]);

        $emprendedor = Emprendedor::query()->create([
            'user_id' => $user->id,
            'nombre' => $nombre,
            'apellidos' => 'Test',
            'descripcion' => 'Descripción de prueba con más de diez caracteres.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'activo',
            'meta_monto' => 500,
        ]);

        return [$user, $emprendedor];
    }

    private function crearCampanaActiva(Emprendedor $emprendedor): Campana
    {
        return Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña '.$emprendedor->nombre,
            'meta_apoyo' => 2000,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addMonth(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);
    }
}
