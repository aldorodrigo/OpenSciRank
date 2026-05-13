<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla minimal de settings clave/valor. Sprint 3.6 #32 la usa para
        // los plazos SLA configurables. Cuando Sprint 4 #9 implemente
        // SettingsResource completo (precios, umbrales, etc.) esta tabla
        // queda como base y se extiende.
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string'); // string|int|bool|json
            $table->string('group', 50)->default('general'); // ej: 'sla'
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
