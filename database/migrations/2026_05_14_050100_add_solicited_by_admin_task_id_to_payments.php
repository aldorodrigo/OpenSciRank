<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3.7 #44 — pagos solicitados por una admin_task.
 *
 * Cuando el admin crea una task con producto seleccionado, se genera
 * un signed checkout URL. Cuando el editor paga, el webhook setea este
 * campo así sabemos qué task originó el pago.
 *
 * Distinto del Payment::admin_task_id (no existe — usamos AdminTask::
 * payment_id para "pago que originó la task", que es el flujo inverso).
 *
 * nullOnDelete: si la task se borra, el Payment sobrevive (es revenue).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('solicited_by_admin_task_id')
                ->nullable()
                ->after('payable_id')
                ->constrained('admin_tasks')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('solicited_by_admin_task_id');
        });
    }
};
