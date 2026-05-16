<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3.7 #40 — participantes de un hilo de conversación.
 *
 * Tracking de:
 *  - quién está en el hilo (editor + admins + evaluadores)
 *  - su rol (para mostrar etiquetas en UI)
 *  - última lectura → badges de no leídos
 *  - mute personal: el usuario puede silenciar emails sin perder acceso
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')
                ->constrained('conversations')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // editor | admin | evaluator | observer
            $table->string('role', 20)->default('admin');

            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('last_read_at')->nullable();
            $table->boolean('email_muted')->default(false);

            $table->timestamps();

            $table->unique(['conversation_id', 'user_id']);
            $table->index(['user_id', 'last_read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_participants');
    }
};
