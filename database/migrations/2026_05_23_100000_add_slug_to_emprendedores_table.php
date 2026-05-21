<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('emprendedores', function (Blueprint $table) {
            $table->string('slug', 180)->nullable()->unique()->after('user_id');
        });

        // Poblar slugs para registros existentes de forma segura
        $emprendedores = DB::table('emprendedores')->get();

        foreach ($emprendedores as $emp) {
            $base = Str::slug($emp->nombre . ' ' . $emp->apellidos);
            if (empty($base)) {
                $base = 'emprendedor';
            }

            $slug = $base;
            $contador = 1;

            while (DB::table('emprendedores')->where('slug', $slug)->exists()) {
                $contador++;
                $slug = "{$base}-{$contador}";
            }

            DB::table('emprendedores')->where('id', $emp->id)->update(['slug' => $slug]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emprendedores', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
