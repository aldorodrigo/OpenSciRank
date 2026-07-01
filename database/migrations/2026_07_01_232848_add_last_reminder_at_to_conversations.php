<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Roadmap #35 — recordatorio "mensaje pendiente" al editor:
            // last_reminder_at para el cooldown, reminder_count para el tope total.
            $table->timestamp('last_reminder_at')->nullable()->after('last_message_at');
            $table->unsignedSmallInteger('reminder_count')->default(0)->after('last_reminder_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['last_reminder_at', 'reminder_count']);
        });
    }
};
