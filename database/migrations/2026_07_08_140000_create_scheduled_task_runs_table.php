<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * #59 — registro de la última corrida de cada tarea programada (cron health).
 * Poblado por los listeners de ScheduledTaskFinished/Failed; leído por el
 * CronHealthWidget del panel de colas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_task_runs', function (Blueprint $table) {
            $table->id();
            $table->string('command')->unique();
            $table->timestamp('last_ran_at')->nullable();
            $table->unsignedInteger('runtime_ms')->nullable();
            $table->string('status', 20)->nullable(); // ok | failed
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_task_runs');
    }
};
