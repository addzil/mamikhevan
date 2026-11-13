<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('event_name', 120)->nullable();
            $table->string('type', 30);
            $table->string('target_type', 20)->default('all');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('discount_kind', 20)->nullable();
            $table->unsignedInteger('discount_value')->default(0);
            $table->unsignedInteger('maximum_discount')->nullable();
            $table->unsignedInteger('minimum_subtotal')->default(0);
            $table->unsignedSmallInteger('buy_quantity')->nullable();
            $table->foreignId('gift_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('gift_size', 20)->nullable();
            $table->unsignedSmallInteger('gift_quantity')->nullable();
            $table->unsignedInteger('gift_unit_price')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
