<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->json('size_stocks')->nullable()->after('sizes');
            $table->unsignedInteger('unallocated_stock')->default(0)->after('stock');
        });

        DB::table('products')
            ->select(['id', 'sizes', 'stock'])
            ->orderBy('id')
            ->chunkById(500, function ($products): void {
                foreach ($products as $product) {
                    $sizes = is_array($product->sizes)
                        ? $product->sizes
                        : json_decode((string) $product->sizes, true);
                    $sizes = is_array($sizes) ? array_values(array_filter($sizes, 'is_string')) : [];

                    if ($sizes === []) {
                        continue;
                    }

                    $sizeStocks = array_fill_keys($sizes, 0);

                    DB::table('products')
                        ->where('id', $product->id)
                        ->update([
                            'size_stocks' => json_encode($sizeStocks, JSON_THROW_ON_ERROR),
                            'unallocated_stock' => (int) $product->stock,
                        ]);
                }
            });

        Schema::table('inventory_adjustments', function (Blueprint $table): void {
            $table->string('size', 20)->nullable()->after('product_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table): void {
            $table->dropColumn('size');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['size_stocks', 'unallocated_stock']);
        });
    }
};
