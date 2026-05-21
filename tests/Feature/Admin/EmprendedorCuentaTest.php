<?php

namespace Tests\Feature\Admin;

use App\Enums\AuditAction;
use App\Mail\EmprendedorCuentaCredencialesMail;
use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmprendedorCuentaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurarGmailSmtpDePrueba();
    }

    public function test_alta_redirige_a_finalizar_en_lugar_del_listado(): void
    {
        $admin = $this->crearAdmin();
        $this->asegurarRolEmprendedor();

        $response = $this->actingAs($admin)->post(route('admin.emprendedores.store'), [
            'nombre' => 'Nuevo',
            'apellidos' => 'Finalizar',
            'descripcion' => 'Descripción de prueba con más de diez caracteres.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'activo',
            'meta_monto' => 100,
        ]);

        $emprendedor = Emprendedor::query()->where('nombre', 'Nuevo')->first();
        $this->assertNotNull($emprendedor);

        $response->assertRedirect(route('admin.emprendedores.finalizar', $emprendedor));
    }

    public function test_admin_crea_cuenta_y_envia_correo(): void
    {
        Mail::fake();

        $admin = $this->crearAdmin();
        $emprendedor = $this->crearEmprendedor();
        $this->asegurarRolEmprendedor();

        $response = $this->actingAs($admin)->post(
            route('admin.emprendedores.cuenta.store', $emprendedor),
            [
                'email' => 'maria.emprendedor@gmail.com',
                'name' => 'María Emprendedora',
            ],
        );

        $response->assertRedirect(route('admin.emprendedores.edit', $emprendedor));
        $response->assertSessionHas('success');

        $emprendedor->refresh();
        $this->assertNotNull($emprendedor->user_id);

        $user = User::query()->find($emprendedor->user_id);
        $this->assertSame('maria.emprendedor@gmail.com', $user->email);
        $this->assertTrue($user->tieneRol('emprendedor'));

        Mail::assertSent(EmprendedorCuentaCredencialesMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::AdminEntrepreneurAccountCreated->value,
            'user_id' => $admin->id,
        ]);
    }

    public function test_no_permite_dos_cuentas_para_el_mismo_emprendedor(): void
    {
        Mail::fake();

        $admin = $this->crearAdmin();
        $emprendedor = $this->crearEmprendedor();
        $this->asegurarRolEmprendedor();

        $this->actingAs($admin)->post(route('admin.emprendedores.cuenta.store', $emprendedor), [
                'email' => 'primera.cuenta@gmail.com',
        ]);

        $segundo = $this->actingAs($admin)->post(route('admin.emprendedores.cuenta.store', $emprendedor), [
            'email' => 'segunda.cuenta@gmail.com',
        ]);

        $segundo->assertRedirect(route('admin.emprendedores.edit', $emprendedor));
        $segundo->assertSessionHas('error');

        $emprendedor->refresh();
        $this->assertSame('primera.cuenta@gmail.com', $emprendedor->user->email);
        $this->assertFalse(User::query()->where('email', 'segunda.cuenta@gmail.com')->exists());
    }

    public function test_crear_cuenta_desde_listado_redirige_al_directorio(): void
    {
        Mail::fake();

        $admin = $this->crearAdmin();
        $emprendedor = $this->crearEmprendedor();
        $this->asegurarRolEmprendedor();

        $response = $this->actingAs($admin)->post(
            route('admin.emprendedores.cuenta.store', $emprendedor),
            [
                'email' => 'desde.listado@gmail.com',
                'name' => 'Desde Listado',
                'desde_listado' => true,
            ],
        );

        $response->assertRedirect(route('admin.emprendedores.index'));
        $response->assertSessionHas('success');
    }

    public function test_reenviar_credenciales_cambia_password_y_envia_correo(): void
    {
        Mail::fake();

        $admin = $this->crearAdmin();
        $emprendedor = $this->crearEmprendedor();
        $this->asegurarRolEmprendedor();

        $this->actingAs($admin)->post(route('admin.emprendedores.cuenta.store', $emprendedor), [
            'email' => 'reenvio.cuenta@gmail.com',
        ]);

        $user = $emprendedor->fresh()->user;
        $hashAnterior = $user->password;

        Mail::fake();

        $response = $this->actingAs($admin)->post(
            route('admin.emprendedores.cuenta.reenviar', $emprendedor),
        );

        $response->assertRedirect(route('admin.emprendedores.edit', $emprendedor));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertNotSame($hashAnterior, $user->password);

        Mail::assertSent(EmprendedorCuentaCredencialesMail::class, function ($mail) {
            return $mail->esReenvio === true;
        });

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::AdminEntrepreneurCredentialsResent->value,
        ]);
    }

    public function test_acepta_correo_con_cualquier_dominio_valido(): void
    {
        Mail::fake();

        $admin = $this->crearAdmin();
        $emprendedor = $this->crearEmprendedor();
        $this->asegurarRolEmprendedor();

        $response = $this->actingAs($admin)->post(route('admin.emprendedores.cuenta.store', $emprendedor), [
            'email' => 'alumno@unifranz.edu.bo',
            'name' => 'Alumno Unifranz',
        ]);

        $response->assertRedirect(route('admin.emprendedores.edit', $emprendedor));
        $this->assertSame('alumno@unifranz.edu.bo', $emprendedor->fresh()->user->email);
    }

    public function test_rechaza_correo_sin_formato_valido(): void
    {
        $admin = $this->crearAdmin();
        $emprendedor = $this->crearEmprendedor();
        $this->asegurarRolEmprendedor();

        $response = $this->actingAs($admin)->post(route('admin.emprendedores.cuenta.store', $emprendedor), [
            'email' => 'usuario-sin-arroba',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertNull($emprendedor->fresh()->user_id);
    }

    public function test_correo_duplicado_rechazado_en_validacion(): void
    {
        $admin = $this->crearAdmin();
        $emprendedor = $this->crearEmprendedor();
        User::factory()->create(['email' => 'ocupado@gmail.com']);

        $response = $this->actingAs($admin)->post(route('admin.emprendedores.cuenta.store', $emprendedor), [
            'email' => 'ocupado@gmail.com',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertNull($emprendedor->fresh()->user_id);
    }

    private function crearAdmin(): User
    {
        $rol = Rol::query()->create(['nombre' => 'admin']);
        $user = User::factory()->create();

        UserRol::query()->create([
            'user_id' => $user->id,
            'role_id' => $rol->id,
        ]);

        return $user;
    }

    private function crearEmprendedor(): Emprendedor
    {
        return Emprendedor::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Cuenta',
            'descripcion' => 'Prueba E-03',
            'estado' => 'activo',
            'meta_monto' => 500,
        ]);
    }

    private function asegurarRolEmprendedor(): void
    {
        DB::table('roles')->updateOrInsert(
            ['nombre' => 'emprendedor'],
            ['nombre' => 'emprendedor', 'created_at' => now(), 'updated_at' => now()],
        );
    }

    private function configurarGmailSmtpDePrueba(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.username' => 'wayna.prueba@gmail.com',
            'mail.mailers.smtp.password' => 'prueba-app-password',
        ]);
    }
}
