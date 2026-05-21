<?php

namespace Tests\Feature\Admin;

use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSessionExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_cierra_sesion_admin_tras_inactividad(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->withSession(['admin_last_activity' => now()->subMinutes(31)->timestamp])
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_extiende_sesion_admin_al_tocar(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->withSession(['admin_last_activity' => now()->subMinutes(25)->timestamp])
            ->post(route('admin.session.touch'))
            ->assertRedirect();

        $this->get(route('admin.dashboard'))->assertOk();
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
}
