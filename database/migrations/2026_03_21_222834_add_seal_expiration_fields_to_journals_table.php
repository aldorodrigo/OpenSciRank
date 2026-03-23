<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->date('seal_awarded_at')->nullable()->after('evaluated_at');
            $table->date('seal_expires_at')->nullable()->after('seal_awarded_at');
            $table->string('seal_status')->nullable()->after('seal_expires_at'); // active, expiring_soon, expired
        });

        // Add slug to products for programmatic identification
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn(['seal_awarded_at', 'seal_expires_at', 'seal_status']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
