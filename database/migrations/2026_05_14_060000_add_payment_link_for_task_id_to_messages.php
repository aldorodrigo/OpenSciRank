<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3.7 #44 — mensaje con link de pago embebido como botón.
 *
 * Cuando el admin marca "Adjuntar link de pago" + "Enviar mensaje automático"
 * en el modal "Crear tarea desde mensaje", el mensaje resultante tiene este
 * campo seteado al ID de la task. La view renderiza un botón nativo "Pagar X"
 * que apunta a la signed URL siempre vigente (se regenera al render), en
 * lugar de un markdown link que no es clickeable en texto plano.
 *
 * nullOnDelete: si la task se borra, el mensaje queda pero pierde el botón.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('payment_link_for_task_id')
                ->nullable()
                ->after('body')
                ->constrained('admin_tasks')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_link_for_task_id');
        });
    }
};
