<?php

namespace Tests\Feature\Emprendedor;

use App\Enums\AuditAction;
use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.username' => 'wayna.prueba@gmail.com',
            'mail.mailers.smtp.password' => 'prueba-app-password',
        ]);
    }

    public function test_crear_cuenta_marca_must_change_password(): void
    {
        Mail::fake();

        $admin = $this->crearAdmin();
        $emprendedor = $this->crearEmprendedor();
        $this->asegurarRolEmprendedor();

        $this->actingAs($admin)->post(route('admin.emprendedores.cuenta.store', $emprendedor), [
            'email' => 'nuevo@wayna.com',
        ]);

        $user = $emprendedor->fresh()->user;
        $this->assertNotNull($user);
        $this->assertTrue($user->must_change_password);
    }

    public function test_emprendedor_con_flag_redirige_al_cambio_obligatorio(): void
    {
        $user = $this->crearUsuarioEmprendedor(['must_change_password' => true]);

        $this->actingAs($user)
            ->get(route('emprendedor.dashboard'))
            ->assertRedirect(route('emprendedor.password.force'));
    }

    public function test_emprendedor_puede_cambiar_password_y_acceder_al_panel(): void
    {
        $user = $this->crearUsuarioEmprendedor(['must_change_password' => true]);

        $this->actingAs($user)
            ->post(route('emprendedor.password.force.store'), [
                'password' => 'NuevaClaveSegura1!',
                'password_confirmation' => 'NuevaClaveSegura1!',
            ])
            ->assertRedirect(route('emprendedor.dashboard'));

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('NuevaClaveSegura1!', $user->password));

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::EmprendedorPasswordChanged->value,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('emprendedor.dashboard'))
            ->assertOk();
    }

    public function test_login_emprendedor_con_flag_va_a_cambio_password(): void
    {
        $passwordPlano = 'Temporal123!';
        $user = $this->crearUsuarioEmprendedor([
            'must_change_password' => true,
            'password' => $passwordPlano,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => $passwordPlano,
        ])->assertRedirect(route('emprendedor.password.force'));
    }

    public function test_reenviar_credenciales_vuelve_a_marcar_flag(): void
    {
        Mail::fake();

        $admin = $this->crearAdmin();
        $user = $this->crearUsuarioEmprendedor(['must_change_password' => false]);
        $emprendedor = $user->emprendedor;
        $this->asegurarRolEmprendedor();

        $this->actingAs($admin)->post(route('admin.emprendedores.cuenta.reenviar', $emprendedor));

        $this->assertTrue($user->fresh()->must_change_password);
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
            'apellidos' => 'E04',
            'descripcion' => 'Descripción de prueba con más de diez caracteres.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'activo',
            'meta_monto' => 500,
            'fotografia' => 'fotografias/test.jpg',
        ]);
    }

    private function asegurarRolEmprendedor(): void
    {
        DB::table('roles')->updateOrInsert(
            ['nombre' => 'emprendedor'],
            ['nombre' => 'emprendedor', 'created_at' => now(), 'updated_at' => now()],
        );
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function crearUsuarioEmprendedor(array $atributos = []): User
    {
        $this->asegurarRolEmprendedor();
        $rolId = Rol::query()->where('nombre', 'emprendedor')->value('id');

        $user = User::factory()->create($atributos);
        UserRol::query()->create([
            'user_id' => $user->id,
            'role_id' => $rolId,
        ]);

        Emprendedor::query()->create([
            'user_id' => $user->id,
            'nombre' => 'Ana',
            'apellidos' => 'Torres',
            'descripcion' => 'Descripción de prueba con más de diez caracteres.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'activo',
            'meta_monto' => 500,
            'fotografia' => 'fotografias/test.jpg',
        ]);

        return $user->fresh();
    }
}
