<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Esta tabla almacena los datos principales de los emprendedores
     * registrados en el sistema WAYNA.
     */
    public function up(): void
    {
        Schema::create('emprendedores', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Datos públicos del emprendedor
            |--------------------------------------------------------------------------
            |
            | Estos datos serán usados en el panel administrativo y en el perfil
            | público que verá el turista al escanear el QR.
            |
            */

            $table->string('nombre', 100);

            $table->string('apellidos', 120);

            $table->text('descripcion')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Fotografía
            |--------------------------------------------------------------------------
            |
            | No guardamos la imagen como binario en la base de datos.
            | Solo guardamos la ruta del archivo dentro de storage.
            |
            | Ejemplo:
            | emprendedores/fotos/juan-perez.jpg
            |
            */

            $table->string('fotografia')->nullable();

            /*
            |--------------------------------------------------------------------------
            | URL del código QR
            |--------------------------------------------------------------------------
            |
            | Este campo se llenará después desde QrCodeService.
            | Por ahora queda nullable porque el QR todavía no se genera
            | en esta tarea.
            |
            */

            $table->string('qr_url')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Estado del emprendedor
            |--------------------------------------------------------------------------
            |
            | Controla si el emprendedor aparece visible para los turistas.
            |
            | Estados iniciales recomendados:
            | - activo
            | - inactivo
            |
            */

            $table->string('estado', 20)->default('activo')->index();

            /*
            |--------------------------------------------------------------------------
            | Meta económica
            |--------------------------------------------------------------------------
            |
            | Representa el monto objetivo de apoyo económico.
            | Usamos decimal porque es dinero y no conviene usar float.
            |
            */

            $table->decimal('meta_monto', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('emprendedores');
    }
};