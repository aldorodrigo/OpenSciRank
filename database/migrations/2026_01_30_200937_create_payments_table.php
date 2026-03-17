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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('product_id')->nullable()->constrained();
            $table->foreignId('coupon_id')->nullable()->constrained();
            $table->string('provider');
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 8, 2);
            $table->string('currency')->default('USD');
            $table->string('status')->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
