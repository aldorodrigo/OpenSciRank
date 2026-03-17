<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Move OAI config from oai_sources to journals table directly.
     * Change harvested_articles to reference journal_id instead of oai_source_id.
     */
    public function up(): void
    {
        // 1. Add OAI fields to journals
        Schema::table('journals', function (Blueprint $table) {
            $table->string('oai_base_url')->nullable()->after('url');
            $table->string('oai_set_spec')->nullable()->after('oai_base_url');
            $table->string('oai_metadata_prefix')->default('oai_dc')->after('oai_set_spec');
            $table->timestamp('oai_last_harvested_at')->nullable()->after('oai_metadata_prefix');
        });

        // 2. Add journal_id to harvested_articles and drop oai_source_id
        Schema::table('harvested_articles', function (Blueprint $table) {
            $table->foreignId('journal_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // 3. Migrate data: copy journal_id from oai_sources to harvested_articles
        $oaiSources = \DB::table('oai_sources')->get();
        foreach ($oaiSources as $source) {
            \DB::table('harvested_articles')
                ->where('oai_source_id', $source->id)
                ->update(['journal_id' => $source->journal_id]);

            // Copy OAI config to journal
            \DB::table('journals')
                ->where('id', $source->journal_id)
                ->update([
                    'oai_base_url' => $source->base_url,
                    'oai_set_spec' => $source->set_spec,
                    'oai_metadata_prefix' => $source->metadata_prefix,
                    'oai_last_harvested_at' => $source->last_harvested_at,
                ]);
        }

        // 4. Drop old FK and column
        Schema::table('harvested_articles', function (Blueprint $table) {
            $table->dropForeign(['oai_source_id']);
            $table->dropColumn('oai_source_id');
        });

        // 5. Drop oai_sources table
        Schema::dropIfExists('oai_sources');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate oai_sources
        Schema::create('oai_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained()->cascadeOnDelete();
            $table->string('base_url');
            $table->string('set_spec')->nullable();
            $table->string('metadata_prefix')->default('oai_dc');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_harvested_at')->nullable();
            $table->string('resumption_token')->nullable();
            $table->timestamps();
        });

        // Add oai_source_id back to harvested_articles
        Schema::table('harvested_articles', function (Blueprint $table) {
            $table->foreignId('oai_source_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->dropForeign(['journal_id']);
            $table->dropColumn('journal_id');
        });

        // Remove OAI fields from journals
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn(['oai_base_url', 'oai_set_spec', 'oai_metadata_prefix', 'oai_last_harvested_at']);
        });
    }
};
