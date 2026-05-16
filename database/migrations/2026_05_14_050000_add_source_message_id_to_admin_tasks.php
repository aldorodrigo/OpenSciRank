<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3.7 #44 — referencia al mensaje origen.
 *
 * Cuando un admin crea una task desde un mensaje (action "Crear tarea"
 * del MessageThread), guardamos el message_id para mostrar el contexto
 * original en la vista de la task y en el infolist.
 *
 * nullOnDelete: si el mensaje se borra, la task sobrevive con
 * source_message_id=null (tiene valor propio independiente).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_tasks', function (Blueprint $table) {
            $table->foreignId('source_message_id')
                ->nullable()
                ->after('related_id')
                ->constrained('messages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('admin_tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_message_id');
        });
    }
};
