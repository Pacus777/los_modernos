<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de visitantes.
     *
     * En el MVP el turista no necesita registrarse.
     * Por eso los datos son opcionales y sirven para asociar una donación
     * a una visita o sesión cuando sea necesario.
     */
    public function up(): void
    {
        Schema::create('visitantes', function (Blueprint $table) {
            $table->id();

            $table->string('codigo')->unique();
            $table->string('idioma', 5)->default('es');
            $table->string('session_id')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitantes');
    }
};