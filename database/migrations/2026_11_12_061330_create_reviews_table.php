<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reviewer_name', 100);
            $table->string('reviewer_city', 100)->nullable();
            $table->unsignedTinyInteger('rating');
            $table->unsignedTinyInteger('product_quality')->nullable();
            $table->unsignedTinyInteger('shipping_speed')->nullable();
            $table->unsignedTinyInteger('service')->nullable();
            $table->text('comment')->nullable();
            $table->string('source', 20)->default('customer');
            $table->boolean('is_visible')->default(true);
            $table->text('admin_reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['is_visible', 'reviewed_at']);
            $table->index(['product_id', 'is_visible']);
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
