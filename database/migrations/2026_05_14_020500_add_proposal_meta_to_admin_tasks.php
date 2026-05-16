<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3.7 #39 + #42 — campos adicionales en admin_tasks para el flujo
 * de propuestas de consultoría y la política de cancelación.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_tasks', function (Blueprint $table) {
            // Cantidad de rondas de propuesta enviadas (cap 3, después escala
            // a super_admin con notificación).
            $table->unsignedSmallInteger('proposal_count_sent')->default(0)->after('consulting_reminder_sent_at');

            // Veces que el editor reagendó esta consultoría — gating de la
            // política de cancelación 24-48h (máximo 1 vez en esa ventana).
            $table->unsignedSmallInteger('reschedule_count')->default(0)->after('proposal_count_sent');

            // URL de Zoom/Meet que el evaluador pega manualmente para la
            // sesión. Se incluye en el .ics, en el panel editor y en el
            // reminder 24h.
            $table->string('consulting_meet_url', 500)->nullable()->after('reschedule_count');

            // Resumen visible al cliente al completar la consultoría.
            // Distinto de `notes` que es 100% interno (admin only).
            $table->text('client_visible_notes')->nullable()->after('notes');
        });

        // Permitir el nuevo status `proposal_sent` — columna ya es string(30),
        // no hace falta alter. La constraint vive en código (constants).
    }

    public function down(): void
    {
        Schema::table('admin_tasks', function (Blueprint $table) {
            $table->dropColumn([
                'proposal_count_sent',
                'reschedule_count',
                'consulting_meet_url',
                'client_visible_notes',
            ]);
        });
    }
};
