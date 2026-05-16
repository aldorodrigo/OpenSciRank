<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3.7 #39 — propuestas de fecha de consultoría.
 *
 * El evaluador propone 1-3 fechas candidatas; el editor acepta una. Cuando
 * se acepta una, las demás del mismo task pasan a 'superseded' por trigger
 * en código. Si nadie acepta antes de expires_at, el cron las marca
 * 'expired' y notifica al editor + evaluador.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulting_proposals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('admin_task_id')
                ->constrained('admin_tasks')
                ->cascadeOnDelete();

            $table->foreignId('proposed_by_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('proposed_slot');

            // active | accepted | rejected | expired | superseded
            $table->string('status', 20)->default('active');

            $table->timestamp('expires_at');

            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('accepted_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Notas opcionales del evaluador para el editor (ej. "horario UTC-3")
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['admin_task_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulting_proposals');
    }
};
