<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * S4-01: publicaciones del emprendedor (texto, imagen o video) para el feed turista.
     */
    public function up(): void
    {
        Schema::create('emprendedor_posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('emprendedor_id')
                ->constrained('emprendedores')
                ->cascadeOnDelete();

            $table->string('tipo', 20)->default('texto')->index();

            $table->text('contenido')->nullable();

            /**
             * Ruta relativa en disco public (ej. emprendedores/posts/1/foto.webp).
             */
            $table->string('media_path')->nullable();

            /**
             * URL externa para video embebido (YouTube, TikTok, etc.) sin subir archivo.
             */
            $table->string('enlace_externo', 500)->nullable();

            $table->string('estado', 20)->default('borrador')->index();

            $table->timestamp('publicado_en')->nullable()->index();

            $table->unsignedSmallInteger('orden')->default(0);

            $table->timestamps();

            $table->index(['emprendedor_id', 'estado', 'publicado_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emprendedor_posts');
    }
};
