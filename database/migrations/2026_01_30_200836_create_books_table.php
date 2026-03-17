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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('isbn_print')->nullable();
            $table->string('isbn_online')->nullable();
            $table->string('publisher')->nullable();
            $table->string('url')->nullable();
            $table->date('publication_date')->nullable();
            $table->string('status')->default('draft');
            $table->decimal('current_score', 5, 2)->nullable();
            $table->string('current_level')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
