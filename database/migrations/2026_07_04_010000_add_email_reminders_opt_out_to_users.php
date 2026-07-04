<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Baja global de correos de recordatorio/marketing (Fase 5 auditoría de
 * correos). El enlace de baja usa rutas firmadas (URL::signedRoute), así que
 * no hace falta columna de token: la firma valida la autenticidad.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_reminders_opted_out')->default(false)->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_reminders_opted_out');
        });
    }
};
