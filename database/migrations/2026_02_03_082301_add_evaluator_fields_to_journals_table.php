<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->foreignId('assigned_evaluator_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->text('evaluation_notes')->nullable()->after('current_level');
            $table->timestamp('evaluated_at')->nullable()->after('evaluation_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropForeign(['assigned_evaluator_id']);
            $table->dropColumn(['assigned_evaluator_id', 'evaluation_notes', 'evaluated_at']);
        });
    }
};
