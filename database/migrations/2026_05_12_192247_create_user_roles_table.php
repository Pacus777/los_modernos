<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();

            // Usuario al que se le asigna un rol.
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Rol asignado al usuario.
            $table->foreignId('role_id')
                ->constrained('roles')
                ->restrictOnDelete();

            // Garantiza que un usuario tenga máximo un rol.
            $table->unique('user_id');

            $table->timestamps();
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};