<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3.7 #40 — hilos de conversación polimórficos.
 *
 * Un hilo puede estar anclado a:
 *  - Journal/Book — consulta sobre el recurso
 *  - AdminTask — consulta sobre una tarea (típicamente consultoría)
 *  - null — consulta general (soporte)
 *
 * Cuando el subject se soft-deletea, el hilo no cascadea: pasa a status
 * 'archived' automáticamente (handled en el modelo/observer).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            $table->string('subject', 200);

            // Subject polimórfico opcional. Si ambos null → consulta general.
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->index(['subject_type', 'subject_id']);

            $table->foreignId('started_by_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // open | closed | archived
            $table->string('status', 20)->default('open');

            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'last_message_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
