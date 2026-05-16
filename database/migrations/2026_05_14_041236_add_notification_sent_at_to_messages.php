<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sprint 3.7 #40 — campo para el batching de notificaciones (cooldown 5 min).
     * NULL = pendiente de notificar; NOT NULL = ya enviado el email.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->timestamp('notification_sent_at')
                ->nullable()
                ->after('edited_at')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('notification_sent_at');
        });
    }
};
