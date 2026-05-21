<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * S4-06: seguir por email sin cuenta (confirmación + baja con token_unsub).
     */
    public function up(): void
    {
        Schema::table('emprendedor_seguidores', function (Blueprint $table) {
            $table->string('email')->nullable()->after('visitante_id');
            $table->string('token_confirm', 64)->nullable()->unique()->after('email');
            $table->string('token_unsub', 64)->nullable()->unique()->after('token_confirm');
            $table->timestamp('confirmado_en')->nullable()->after('token_unsub');

            $table->unique(
                ['emprendedor_id', 'email'],
                'emprendedor_seguidores_emprendedor_email_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('emprendedor_seguidores', function (Blueprint $table) {
            $table->dropUnique('emprendedor_seguidores_emprendedor_email_unique');
            $table->dropUnique(['token_confirm']);
            $table->dropUnique(['token_unsub']);
            $table->dropColumn([
                'email',
                'token_confirm',
                'token_unsub',
                'confirmado_en',
            ]);
        });
    }
};
