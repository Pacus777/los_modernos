<?php

namespace Tests\Feature\Emprendedor;

use App\Enums\AuditAction;
use App\Mail\EmprendedorDonacionRecibidaMail;
use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\EmprendedorNotificacion;
use App\Models\Rol;
use App\Models\TipoPago;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmprendedorDonacionNotificacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Mail::fake();
    }

    public function test_envia_correo_al_validar_donacion_con_preferencia_activa(): void
    {
        [$user, $emprendedor, $campana, $tipoPago] = $this->escenarioEmprendedor(notificarEmail: true, notificarPanel: true);

        $donacion = $this->crearDonacionPendiente($campana, $tipoPago, 75);

        $donacion->update(['estado_pago' => Donacion::ESTADO_VALIDADO]);

        Mail::assertSent(EmprendedorDonacionRecibidaMail::class, function (EmprendedorDonacionRecibidaMail $mail) use ($user, $emprendedor, $donacion) {
            return $mail->hasTo($user->email)
                && $mail->emprendedor->is($emprendedor)
                && $mail->donacion->id === $donacion->id;
        });
    }

    public function test_no_envia_correo_si_preferencia_desactivada(): void
    {
        [, , $campana, $tipoPago] = $this->escenarioEmprendedor(notificarEmail: false, notificarPanel: true);

        $donacion = $this->crearDonacionPendiente($campana, $tipoPago, 40);
        $donacion->update(['estado_pago' => Donacion::ESTADO_VALIDADO]);

        Mail::assertNothingSent();
    }

    public function test_crea_aviso_en_panel_al_validar_con_preferencia_activa(): void
    {
        [, $emprendedor, $campana, $tipoPago] = $this->escenarioEmprendedor(notificarEmail: false, notificarPanel: true);

        $donacion = $this->crearDonacionPendiente($campana, $tipoPago, 120);
        $donacion->update(['estado_pago' => Donacion::ESTADO_VALIDADO]);

        $this->assertDatabaseHas('emprendedor_notificaciones', [
            'emprendedor_id' => $emprendedor->id,
            'donacion_id' => $donacion->id,
            'tipo' => EmprendedorNotificacion::TIPO_DONACION_VALIDADA,
        ]);
    }

    public function test_no_crea_aviso_en_panel_si_preferencia_desactivada(): void
    {
        [, , $campana, $tipoPago] = $this->escenarioEmprendedor(notificarEmail: true, notificarPanel: false);

        $donacion = $this->crearDonacionPendiente($campana, $tipoPago, 55);
        $donacion->update(['estado_pago' => Donacion::ESTADO_VALIDADO]);

        $this->assertDatabaseCount('emprendedor_notificaciones', 0);
    }

    public function test_no_reenvia_correo_si_donacion_ya_estaba_validada(): void
    {
        [, , $campana, $tipoPago] = $this->escenarioEmprendedor(notificarEmail: true, notificarPanel: true);

        $donacion = Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => 30,
            'moneda' => Donacion::MONEDA_BOB,
            'metodo' => 'efectivo',
            'estado_pago' => Donacion::ESTADO_VALIDADO,
            'proveedor_pago' => Donacion::PROVEEDOR_MANUAL,
            'referencia_pago' => 'REF-ok',
        ]);

        Mail::assertSent(EmprendedorDonacionRecibidaMail::class, 1);

        $donacion->update(['referencia_pago' => 'REF-cambio']);

        Mail::assertSent(EmprendedorDonacionRecibidaMail::class, 1);
    }

    public function test_emprendedor_actualiza_preferencias_y_auditoria(): void
    {
        [$user] = $this->escenarioEmprendedor(notificarEmail: true, notificarPanel: true);

        $this->actingAs($user)
            ->put(route('emprendedor.preferencias.update'), [
                'notificar_donaciones_email' => false,
                'notificar_donaciones_panel' => true,
            ])
            ->assertRedirect(route('emprendedor.preferencias.edit'));

        $this->assertDatabaseHas('emprendedores', [
            'user_id' => $user->id,
            'notificar_donaciones_email' => 0,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::EmprendedorNotificationPreferencesUpdated->value,
        ]);
    }

    public function test_pagina_preferencias_solo_emprendedor(): void
    {
        [$user] = $this->escenarioEmprendedor(notificarEmail: true, notificarPanel: true);

        $this->actingAs($user)
            ->get(route('emprendedor.preferencias.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Emprendedor/Preferencias/Index')
                ->where('notificar_donaciones_email', true)
                ->where('notificar_donaciones_panel', true));

        auth()->logout();

        $this->get(route('emprendedor.preferencias.edit'))->assertRedirect(route('login'));
    }

    /**
     * @return array{0: User, 1: Emprendedor, 2: Campana, 3: TipoPago}
     */
    private function escenarioEmprendedor(bool $notificarEmail = true, bool $notificarPanel = true): array
    {
        $rol = Rol::query()->firstOrCreate(['nombre' => 'emprendedor']);
        $user = User::factory()->create(['email' => 'emprendedor-notif@test.local']);
        UserRol::query()->create(['user_id' => $user->id, 'role_id' => $rol->id]);

        $emprendedor = Emprendedor::query()->create([
            'user_id' => $user->id,
            'nombre' => 'Naz',
            'apellidos' => 'Test',
            'descripcion' => 'Descripción de prueba con más de diez caracteres.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'activo',
            'meta_monto' => 500,
            'notificar_donaciones_email' => $notificarEmail,
            'notificar_donaciones_panel' => $notificarPanel,
            'fotografia' => 'fotografias/test.jpg',
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña notif',
            'meta_apoyo' => 1000,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addMonth(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $tipoPago = TipoPago::query()->create([
            'codigo' => 'efectivo_notif',
            'nombre' => 'Efectivo',
            'activo' => true,
        ]);

        return [$user, $emprendedor, $campana, $tipoPago];
    }

    private function crearDonacionPendiente(Campana $campana, TipoPago $tipoPago, float $monto): Donacion
    {
        return Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => $monto,
            'moneda' => Donacion::MONEDA_BOB,
            'metodo' => 'efectivo',
            'estado_pago' => Donacion::ESTADO_PENDIENTE,
            'proveedor_pago' => Donacion::PROVEEDOR_MANUAL,
            'referencia_pago' => 'REF-'.uniqid(),
        ]);
    }
}
