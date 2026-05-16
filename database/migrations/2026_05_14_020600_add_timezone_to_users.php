<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3.7 #41 — zona horaria explícita del usuario.
 *
 * String IANA (ej. `America/Argentina/Buenos_Aires`). Nullable porque
 * para usuarios anteriores al deploy todavía no la pidió. UI: prompt
 * bloqueante al primer login post-deploy.
 *
 * Fallback en código: si timezone es null → config('app.timezone').
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('timezone', 64)->nullable()->after('locale');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
};
