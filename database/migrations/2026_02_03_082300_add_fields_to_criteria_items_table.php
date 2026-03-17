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
        Schema::table('criteria_items', function (Blueprint $table) {
            $table->string('code')->after('id')->nullable(); // Código único: 1.1, 2.3, etc.
            $table->string('category_name')->after('category_id')->nullable(); // Nombre de categoría
            $table->boolean('is_core')->default(false)->after('weight'); // Indicador excluyente
            $table->string('type')->default('core')->after('is_core'); // core, advanced, excellence
            $table->integer('order')->default(0)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('criteria_items', function (Blueprint $table) {
            $table->dropColumn(['code', 'category_name', 'is_core', 'type', 'order']);
        });
    }
};
