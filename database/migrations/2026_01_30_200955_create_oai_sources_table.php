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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oai_sources');
    }
};
