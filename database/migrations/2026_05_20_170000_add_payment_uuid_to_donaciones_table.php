<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('donaciones', 'payment_uuid')) {
            return;
        }

        Schema::table('donaciones', function (Blueprint $table) {
            $table->uuid('payment_uuid')->nullable()->unique()->after('referencia_pago');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('donaciones', 'payment_uuid')) {
            return;
        }

        Schema::table('donaciones', function (Blueprint $table) {
            $table->dropUnique(['payment_uuid']);
            $table->dropColumn('payment_uuid');
        });
    }
};
