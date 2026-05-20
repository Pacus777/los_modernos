<?php

namespace Tests\Feature\Auth;

use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use App\Services\AdminTwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_con_2fa_redirige_al_desafio_tras_login(): void
    {
        $admin = $this->crearAdminConDosFactores();

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))->assertRedirect(route('two-factor.challenge'));
    }

    public function test_codigo_valido_permite_acceder_al_panel_admin(): void
    {
        $admin = $this->crearAdminConDosFactores();
        $codigo = app(AdminTwoFactorService::class)->codigoActualParaSecreto('JBSWY3DPEHPK3PXP');

        $this->actingAs($admin);

        $this->post(route('two-factor.verify'), ['code' => $codigo])
            ->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_puede_activar_2fa_desde_configuracion(): void
    {
        $admin = $this->crearAdmin();
        $servicio = app(AdminTwoFactorService::class);
        $secreto = 'JBSWY3DPEHPK3PXP';
        $codigo = $servicio->codigoActualParaSecreto($secreto);

        $this->actingAs($admin)
            ->withSession(['two_factor_setup_secret' => $secreto])
            ->post(route('admin.two-factor.activar'), ['code' => $codigo])
            ->assertRedirect(route('admin.two-factor.edit'));

        $admin->refresh();

        $this->assertTrue($admin->tieneDosFactoresActivo());
    }

    public function test_cajero_no_usa_desafio_2fa(): void
    {
        $rol = Rol::query()->create(['nombre' => 'cajero']);
        $user = User::factory()->create();
        UserRol::query()->create([
            'user_id' => $user->id,
            'role_id' => $rol->id,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('cajero.efectivo'));
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

    private function crearAdminConDosFactores(): User
    {
        $admin = $this->crearAdmin();
        app(AdminTwoFactorService::class)->activar($admin, 'JBSWY3DPEHPK3PXP');

        return $admin->fresh();
    }
}
