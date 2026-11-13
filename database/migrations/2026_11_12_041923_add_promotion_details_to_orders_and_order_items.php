<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('promotion_id')->nullable()->constrained()->nullOnDelete();
            $table->string('promotion_name', 160)->nullable();
            $table->unsignedInteger('promotion_discount')->default(0);
            $table->unsignedInteger('shipping_discount')->default(0);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('is_promo_gift')->default(false);
            $table->unsignedInteger('original_price')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['is_promo_gift', 'original_price']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotion_id');
            $table->dropColumn(['promotion_name', 'promotion_discount', 'shipping_discount']);
        });
    }
};
