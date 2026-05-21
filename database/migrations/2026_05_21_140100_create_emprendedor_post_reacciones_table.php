<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * S4-01: reacciones de turistas (visitante) o usuarios autenticados a publicaciones.
     */
    public function up(): void
    {
        Schema::create('emprendedor_post_reacciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('emprendedor_post_id')
                ->constrained('emprendedor_posts')
                ->cascadeOnDelete();

            $table->morphs('actor');

            $table->string('tipo', 20)->default('me_gusta')->index();

            $table->timestamps();

            $table->unique(
                ['emprendedor_post_id', 'actor_type', 'actor_id'],
                'emprendedor_post_reacciones_actor_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emprendedor_post_reacciones');
    }
};
