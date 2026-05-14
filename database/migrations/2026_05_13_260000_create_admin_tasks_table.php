<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_tasks', function (Blueprint $table) {
            $table->id();

            // Tipo de tarea (define qué trabajo hay que hacer y a quién auto-cerrar)
            $table->string('type', 50);

            // Renderizado i18n: title_key + title_params se resuelven en runtime
            // contra el locale del usuario que ve la tabla (NO se guarda texto plano).
            $table->string('title_key', 255);
            $table->json('title_params')->nullable();

            // Pago que generó la tarea (nullable porque la task del flujo de
            // listing gratuito de revistas no tiene payment asociado).
            $table->foreignId('payment_id')
                ->nullable()
                ->constrained('payments')
                ->nullOnDelete();

            // Relación polimórfica al recurso (Journal/Book/User para huérfanos)
            $table->nullableMorphs('related');

            // Asignación
            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Estados:
            //   pending     — creada, sin tomar
            //   in_progress — admin clicó "Iniciar"
            //   scheduled   — consultoría con fecha agendada (scheduled_for)
            //   in_session  — consultoría en curso (no requiere timestamp)
            //   completed   — auto por hook (EvaluateJournal/ReviewListing) o manual
            //   cancelled   — admin canceló, o auto al reembolsar el pago
            $table->string('status', 30)->default('pending');

            // Prioridad: high (Express, huérfanos), normal (default), low
            $table->string('priority', 10)->default('normal');

            // Plazos
            $table->timestamp('due_at')->nullable();
            $table->timestamp('scheduled_for')->nullable(); // solo consulting
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->string('cancelled_reason', 500)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Queue principal: vencidas y por orden de prioridad
            $table->index(['status', 'due_at']);
            // "Mis tareas pendientes"
            $table->index(['assigned_to', 'status']);
            // Filtrar por tipo
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_tasks');
    }
};
