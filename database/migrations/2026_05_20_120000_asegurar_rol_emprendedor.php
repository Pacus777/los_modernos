<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * E-03: el rol emprendedor debe existir en todos los entornos (no solo tras db:seed).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('roles')->updateOrInsert(
            ['nombre' => 'emprendedor'],
            [
                'nombre' => 'emprendedor',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );
    }

    public function down(): void
    {
        DB::table('user_roles')
            ->whereIn('role_id', function ($query) {
                $query->select('id')
                    ->from('roles')
                    ->where('nombre', 'emprendedor');
            })
            ->delete();

        DB::table('roles')->where('nombre', 'emprendedor')->delete();
    }
};
