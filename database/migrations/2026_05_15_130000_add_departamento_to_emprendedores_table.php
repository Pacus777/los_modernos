<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emprendedores', function (Blueprint $table) {
            $table->string('departamento', 30)
                ->default('la_paz')
                ->after('tipo_emprendimiento')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('emprendedores', function (Blueprint $table) {
            $table->dropColumn('departamento');
        });
    }
};
