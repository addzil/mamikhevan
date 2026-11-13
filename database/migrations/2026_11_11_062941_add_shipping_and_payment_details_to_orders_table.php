<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_province', 60)->nullable();
            $table->string('shipping_city', 100)->nullable();
            $table->string('shipping_district', 100)->nullable();
            $table->string('shipping_subdistrict', 100)->nullable();
            $table->string('shipping_postal_code', 5)->nullable();
            $table->string('shipping_courier', 20)->nullable();
            $table->string('shipping_service', 30)->nullable();
            $table->string('shipping_quote_source', 20)->nullable();
            $table->unsignedSmallInteger('payment_unique_code')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_province',
                'shipping_city',
                'shipping_district',
                'shipping_subdistrict',
                'shipping_postal_code',
                'shipping_courier',
                'shipping_service',
                'shipping_quote_source',
                'payment_unique_code',
            ]);
        });
    }
};
