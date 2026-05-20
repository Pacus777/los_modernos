<?php

namespace Database\Seeders;

use App\Models\Emprendedor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EmprendedorAccountSeeder extends Seeder
{
    public function run(): void
    {
        $rolEmprendedorId = $this->obtenerOCrearRolEmprendedor();

        if (! $rolEmprendedorId) {
            return;
        }

        Emprendedor::query()
            ->orderBy('id')
            ->each(function (Emprendedor $emprendedor) use ($rolEmprendedorId): void {
                if ($emprendedor->user_id && User::query()->whereKey($emprendedor->user_id)->exists()) {
                    $this->asignarRol($emprendedor->user_id, $rolEmprendedorId);
                    return;
                }

                $nombreCompleto = $emprendedor->nombreCompleto();
                $slug = Str::slug($nombreCompleto ?: "emprendedor-{$emprendedor->id}");

                $user = User::query()->firstOrCreate(
                    ['email' => "emprendedor-{$emprendedor->id}-{$slug}@wayna.local"],
                    [
                        'name' => $nombreCompleto ?: "Emprendedor {$emprendedor->id}",
                        'password' => Hash::make('wayna12345'),
                        'email_verified_at' => now(),
                    ],
                );

                $this->asignarRol($user->id, $rolEmprendedorId);

                $emprendedor->forceFill([
                    'user_id' => $user->id,
                ])->save();
            });
    }

    private function obtenerOCrearRolEmprendedor(): ?int
    {
        $payload = [
            'nombre' => 'emprendedor',
        ];

        if (Schema::hasColumn('roles', 'created_at')) {
            $payload['created_at'] = now();
        }

        if (Schema::hasColumn('roles', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('roles')->updateOrInsert(
            ['nombre' => 'emprendedor'],
            $payload,
        );

        return DB::table('roles')
            ->where('nombre', 'emprendedor')
            ->value('id');
    }

    private function asignarRol(int $userId, int $roleId): void
    {
        $payload = [
            'role_id' => $roleId,
        ];

        if (Schema::hasColumn('user_roles', 'created_at')) {
            $payload['created_at'] = now();
        }

        if (Schema::hasColumn('user_roles', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('user_roles')->updateOrInsert(
            ['user_id' => $userId],
            $payload,
        );
    }
}