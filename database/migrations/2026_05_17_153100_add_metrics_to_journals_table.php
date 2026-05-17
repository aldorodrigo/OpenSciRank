<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->string('openalex_venue_id', 40)->nullable()->after('issn_online');
            $table->unsignedInteger('h_index')->nullable()->after('current_score');
            $table->unsignedBigInteger('total_citations')->nullable()->after('h_index');
            $table->decimal('mean_citedness_2y', 8, 3)->nullable()->after('total_citations');
            $table->string('metrics_source', 20)->nullable()->after('mean_citedness_2y');
            $table->timestamp('metrics_updated_at')->nullable()->after('metrics_source');
            $table->text('metrics_notes')->nullable()->after('metrics_updated_at');

            $table->index(['status', 'h_index'], 'journals_status_hindex_idx');
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropIndex('journals_status_hindex_idx');
            $table->dropColumn([
                'openalex_venue_id',
                'h_index',
                'total_citations',
                'mean_citedness_2y',
                'metrics_source',
                'metrics_updated_at',
                'metrics_notes',
            ]);
        });
    }
};
