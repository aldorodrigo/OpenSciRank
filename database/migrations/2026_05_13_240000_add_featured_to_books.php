<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Sprint 3 #20: addon "Listing Destacado" para libros ya listed.
            // is_featured indica si actualmente el libro debe mostrarse con
            // prioridad y badge; featured_until guarda la fecha de expiración
            // del beneficio (extensible al renovar el addon).
            $table->boolean('is_featured')->default(false)->after('status');
            $table->date('featured_until')->nullable()->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'featured_until']);
        });
    }
};
