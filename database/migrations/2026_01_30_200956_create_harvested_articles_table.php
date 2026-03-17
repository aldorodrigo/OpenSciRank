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
        Schema::create('harvested_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oai_source_id')->constrained()->cascadeOnDelete();
            $table->string('identifier')->unique();
            $table->text('title');
            $table->text('authors')->nullable();
            $table->date('date')->nullable();
            $table->string('url')->nullable();
            $table->string('pdf_url')->nullable();
            $table->string('language', 10)->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvested_articles');
    }
};
