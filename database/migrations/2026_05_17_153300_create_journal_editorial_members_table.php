<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_editorial_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('role', 30); // editor_in_chief | associate | board | guest
            $table->string('orcid', 19)->nullable();
            $table->string('email')->nullable();
            $table->string('affiliation')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['journal_id', 'display_order'], 'jem_journal_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_editorial_members');
    }
};
