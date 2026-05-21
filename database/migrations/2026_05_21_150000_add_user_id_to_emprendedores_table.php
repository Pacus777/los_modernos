<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * E-03: bases creadas antes de user_id en create_emprendedores necesitan esta columna.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('emprendedores', 'user_id')) {
            return;
        }

        Schema::table('emprendedores', function (Blueprint $table) {
            $table
                ->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('emprendedores', 'user_id')) {
            return;
        }

        Schema::table('emprendedores', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
