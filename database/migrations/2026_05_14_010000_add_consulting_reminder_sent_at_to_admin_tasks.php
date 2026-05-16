<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3.6 #32 — recordatorios de consultoría.
 *
 * Marca el momento en que se envió el ConsultingReminder 24h previo a la
 * sesión, para evitar re-envíos en sucesivas corridas del cron diario.
 * Se setea a null cuando se reagenda la sesión, así el nuevo scheduled_for
 * vuelve a entrar al scan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_tasks', function (Blueprint $table) {
            $table->timestamp('consulting_reminder_sent_at')->nullable()->after('scheduled_for');
        });
    }

    public function down(): void
    {
        Schema::table('admin_tasks', function (Blueprint $table) {
            $table->dropColumn('consulting_reminder_sent_at');
        });
    }
};
