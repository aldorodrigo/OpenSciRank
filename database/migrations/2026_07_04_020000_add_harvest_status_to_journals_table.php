<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            // Estado de la cosecha OAI (async): idle | queued | running | failed
            $table->string('oai_harvest_status', 20)->nullable()->after('oai_last_harvested_at');
            $table->unsignedInteger('oai_last_harvest_count')->nullable()->after('oai_harvest_status');
            $table->text('oai_last_harvest_error')->nullable()->after('oai_last_harvest_count');
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn([
                'oai_harvest_status',
                'oai_last_harvest_count',
                'oai_last_harvest_error',
            ]);
        });
    }
};
