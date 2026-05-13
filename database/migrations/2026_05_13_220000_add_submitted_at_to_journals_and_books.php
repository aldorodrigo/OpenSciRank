<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            // Marca el momento en que el journal pasa a estado submitted.
            // Permite calcular SLA (días entre submitted_at y evaluated_at) y
            // distinguir entregas tardías en evaluaciones y renovaciones.
            $table->timestamp('submitted_at')->nullable()->after('status');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->timestamp('submitted_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn('submitted_at');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('submitted_at');
        });
    }
};
