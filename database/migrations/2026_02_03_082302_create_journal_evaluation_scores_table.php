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
        Schema::create('journal_evaluation_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('criteria_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('users');
            $table->boolean('is_met')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['journal_id', 'criteria_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_evaluation_scores');
    }
};
