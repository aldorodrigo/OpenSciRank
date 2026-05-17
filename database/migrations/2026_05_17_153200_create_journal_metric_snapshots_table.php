<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_metric_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained()->cascadeOnDelete();
            $table->string('source', 20); // openalex | crossref | scholar_manual | orcid
            $table->unsignedInteger('h_index')->nullable();
            $table->unsignedBigInteger('total_citations')->nullable();
            $table->decimal('mean_citedness_2y', 8, 3)->nullable();
            $table->json('raw_payload')->nullable();
            $table->foreignId('captured_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('captured_at');
            $table->text('notes')->nullable();
            $table->boolean('failed')->default(false);
            $table->string('error_message')->nullable();
            $table->timestamps();

            $table->index(['journal_id', 'source', 'captured_at'], 'jms_journal_source_captured_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_metric_snapshots');
    }
};
