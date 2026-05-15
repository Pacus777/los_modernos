<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de puntos físicos.
     *
     * Un punto físico representa una ubicación real:
     * - mesa
     * - mostrador
     * - feria
     * - punto turístico
     */
    public function up(): void
    {
        Schema::create('puntos_fisicos', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Datos básicos del punto físico
            |--------------------------------------------------------------------------
            */

            $table->string('nombre', 120);

            /*
            |--------------------------------------------------------------------------
            | Slug público
            |--------------------------------------------------------------------------
            |
            | Se usará en la ruta pública:
            | /punto/{slug}
            |
            | Ejemplo:
            | /punto/mesa-principal-wayna
            |
            */

            $table->string('slug', 160)->unique();

            $table->text('descripcion')->nullable();

            $table->string('ubicacion', 150)->nullable();

            /*
            |--------------------------------------------------------------------------
            | QR del punto físico
            |--------------------------------------------------------------------------
            |
            | Guarda la ruta relativa del archivo PNG generado en storage.
            |
            | Ejemplo:
            | puntos/qrs/punto-1.png
            |
            */

            $table->string('qr_url')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Estado
            |--------------------------------------------------------------------------
            |
            | activo:
            | El punto se muestra públicamente.
            |
            | inactivo:
            | El QR puede existir, pero la ruta pública ya no muestra el punto.
            |
            */

            $table->string('estado', 20)->default('activo')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('puntos_fisicos');
    }
};