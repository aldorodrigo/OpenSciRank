<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_posts', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->after('content');
            $table->string('category')->nullable()->after('type');
            $table->string('emoji', 10)->nullable()->after('category');
            $table->boolean('is_featured')->default(false)->after('emoji');
            $table->string('read_time', 20)->nullable()->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('cms_posts', function (Blueprint $table) {
            $table->dropColumn(['excerpt', 'category', 'emoji', 'is_featured', 'read_time']);
        });
    }
};
