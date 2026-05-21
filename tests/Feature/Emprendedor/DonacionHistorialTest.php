<?php

namespace Tests\Feature\Emprendedor;

use App\Enums\AuditAction;
use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\TipoPago;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonacionHistorialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_emprendedor_ve_solo_sus_donaciones(): void
    {
        [$userA, $empA, $campanaA, $tipoPago] = $this->escenarioEmprendedor('Ana');
        [, $empB, $campanaB] = $this->escenarioEmprendedor('Luis');

        $this->crearDonacion($campanaA, $tipoPago, 100, Donacion::ESTADO_VALIDADO);
        $this->crearDonacion($campanaB, $tipoPago, 200, Donacion::ESTADO_VALIDADO);

        $this->actingAs($userA)
            ->get(route('emprendedor.donaciones.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Emprendedor/Donaciones/Index')
                ->has('donaciones.data', 1)
                ->where('donaciones.data.0.monto', 100)
                ->where('resumen.total_registros', 1));
    }

    public function test_filtro_por_estado_en_historial(): void
    {
        [$user, , $campana, $tipoPago] = $this->escenarioEmprendedor('Marta');

        $this->crearDonacion($campana, $tipoPago, 50, Donacion::ESTADO_PENDIENTE);
        $this->crearDonacion($campana, $tipoPago, 80, Donacion::ESTADO_VALIDADO);

        $this->actingAs($user)
            ->get(route('emprendedor.donaciones.index', ['estado_pago' => Donacion::ESTADO_VALIDADO]))
            ->assertInertia(fn ($page) => $page
                ->has('donaciones.data', 1)
                ->where('donaciones.data.0.estado_pago', Donacion::ESTADO_VALIDADO));
    }

    public function test_exportar_excel_wayna_con_filtros_y_auditoria(): void
    {
        [$user, $emprendedor, $campana, $tipoPago] = $this->escenarioEmprendedor('Carlos');

        $this->crearDonacion($campana, $tipoPago, 120, Donacion::ESTADO_VALIDADO);

        $response = $this->actingAs($user)->get(route('emprendedor.donaciones.exportar'));

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', strtolower($response->headers->get('content-type') ?? ''));
        $this->assertStringContainsString('.xls', $response->headers->get('content-disposition') ?? '');

        $contenido = $response->streamedContent();
        $this->assertStringContainsString('WAYNA', $contenido);
        $this->assertStringContainsString('#f07e26', $contenido);
        $this->assertStringContainsString('Validado', $contenido);
        $this->assertStringContainsString('120,00', $contenido);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::EmprendedorDonationsExported->value,
            'user_id' => $user->id,
        ]);
    }

    public function test_exportar_pdf_wayna_con_filtros_y_auditoria(): void
    {
        [$user, $emprendedor, $campana, $tipoPago] = $this->escenarioEmprendedor('Diana');

        $this->crearDonacion($campana, $tipoPago, 85, Donacion::ESTADO_VALIDADO);

        $response = $this->actingAs($user)->get(route('emprendedor.donaciones.exportar.pdf'));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', strtolower($response->headers->get('content-type') ?? ''));
        $this->assertStringContainsString('.pdf', $response->headers->get('content-disposition') ?? '');
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::EmprendedorDonationsExported->value,
            'user_id' => $user->id,
        ]);
    }

    public function test_turista_no_accede_al_historial(): void
    {
        $this->get(route('emprendedor.donaciones.index'))->assertRedirect(route('login'));
    }

    /**
     * @return array{0: User, 1: Emprendedor, 2: Campana, 3: TipoPago}
     */
    private function escenarioEmprendedor(string $nombre): array
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

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña '.$nombre,
            'meta_apoyo' => 1000,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addMonth(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $tipoPago = TipoPago::query()->create([
            'codigo' => 'efectivo_'.$nombre,
            'nombre' => 'Efectivo',
            'activo' => true,
            'requiere_validacion_manual' => true,
        ]);

        return [$user, $emprendedor, $campana, $tipoPago];
    }

    private function crearDonacion(
        Campana $campana,
        TipoPago $tipoPago,
        float $monto,
        string $estado,
    ): Donacion {
        return Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => $monto,
            'moneda' => Donacion::MONEDA_BOB,
            'metodo' => 'efectivo',
            'estado_pago' => $estado,
            'proveedor_pago' => Donacion::PROVEEDOR_MANUAL,
            'referencia_pago' => 'REF-'.uniqid(),
        ]);
    }
}
