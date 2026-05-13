<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Si NO es null, el journal está en flujo de renovación pagada esperando
     * re-evaluación. Al completarse la evaluación con score >= 75% se llama
     * renewSeal($pending_renewal_years) y se setea null.
     */
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->unsignedSmallInteger('pending_renewal_years')->nullable()->after('seal_notified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn('pending_renewal_years');
        });
    }
};
