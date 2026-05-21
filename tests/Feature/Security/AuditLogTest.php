<?php

namespace Tests\Feature\Security;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\TipoPago;
use App\Models\User;
use App\Models\UserRol;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_login_exitoso(): void
    {
        $rol = Rol::query()->create(['nombre' => 'admin']);
        $user = User::factory()->create();
        UserRol::query()->create([
            'user_id' => $user->id,
            'role_id' => $rol->id,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard', absolute: false));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => AuditAction::AuthLoginSuccess->value,
        ]);
    }

    public function test_registra_login_fallido_sin_user_id(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => null,
            'action' => AuditAction::AuthLoginFailed->value,
        ]);

        $log = AuditLog::query()->where('action', AuditAction::AuthLoginFailed->value)->first();
        $this->assertSame($user->email, $log->metadata['email'] ?? null);
    }

    public function test_registra_validacion_de_donacion_por_admin(): void
    {
        $admin = $this->crearUsuarioAdmin();
        $donacion = $this->crearDonacionPendiente();

        $this->actingAs($admin)
            ->patch(route('admin.donaciones.validar', $donacion))
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => AuditAction::AdminDonationValidated->value,
            'subject_type' => $donacion->getMorphClass(),
            'subject_id' => $donacion->id,
        ]);
    }

    public function test_no_inserta_si_auditoria_desactivada(): void
    {
        config(['wayna.audit_log.enabled' => false]);

        app(AuditLogService::class)->registrar(
            AuditAction::AuthLogout,
            actor: User::factory()->create(),
        );

        $this->assertDatabaseCount('audit_logs', 0);
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

    private function crearDonacionPendiente(): Donacion
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Audit',
            'apellidos' => 'Test',
            'descripcion' => 'Prueba',
            'estado' => 'activo',
            'meta_monto' => 500,
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña audit',
            'meta_apoyo' => 500,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $tipoPago = TipoPago::query()->create([
            'codigo' => 'efectivo_audit',
            'nombre' => 'Efectivo',
            'descripcion' => null,
            'activo' => true,
        ]);

        return Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => 40,
            'metodo' => 'efectivo',
            'estado_pago' => Donacion::ESTADO_PENDIENTE,
            'referencia_pago' => 'REF-AUDIT-001',
        ]);
    }
}
