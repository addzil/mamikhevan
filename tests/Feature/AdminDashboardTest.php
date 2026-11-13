<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_uses_active_orders_discounts_and_order_time_cost_snapshots(): void
    {
        $this->seed();
        Order::query()->delete();

        $admin = User::query()->where('is_admin', true)->firstOrFail();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $originalCategoryName = 'Kategori saat pesanan dibuat';
        $currentCategory = Category::query()->create([
            'name' => 'Kategori Baru',
            'slug' => 'kategori-baru',
            'is_active' => true,
        ]);

        $order = Order::query()->create([
            'customer_name' => 'Pelanggan Audit',
            'customer_phone' => '081234567890',
            'customer_address' => 'Alamat untuk pengujian dashboard',
            'payment_method' => Order::PAYMENT_TRANSFER,
            'subtotal' => 10000,
            'shipping_cost' => 5000,
            'promotion_discount' => 1000,
            'shipping_discount' => 1000,
            'total' => 13000,
            'status' => Order::STATUS_PENDING,
        ]);
        $order->items()->createMany([
            [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'category_name' => $originalCategoryName,
                'price' => 5000,
                'quantity' => 2,
                'subtotal' => 10000,
                'cost_price' => 2500,
            ],
            [
                'product_id' => $product->id,
                'product_name' => 'Hadiah promo',
                'category_name' => 'Kategori hadiah',
                'price' => 0,
                'quantity' => 1,
                'subtotal' => 0,
                'cost_price' => 1000,
                'is_promo_gift' => true,
            ],
        ]);

        $product->update(['cost_price' => 9000, 'category_id' => $currentCategory->id]);

        Order::query()->create([
            'customer_name' => 'Pesanan Batal',
            'customer_phone' => '081234567899',
            'customer_address' => 'Alamat pesanan batal',
            'payment_method' => Order::PAYMENT_TRANSFER,
            'subtotal' => 50000,
            'total' => 50000,
            'status' => Order::STATUS_CANCELLED,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', function (array $stats): bool {
                return $stats['revenue'] === 13000
                    && $stats['orders'] === 1
                    && $stats['customers'] === 1
                    && $stats['margin'] === 3000
                    && $stats['margin_percent'] === 33.3
                    && $stats['products'] === Product::query()->active()->count();
            })
            ->assertViewHas('bestSellingCategories', fn ($categories): bool => $categories->contains(
                fn (array $category): bool => $category['name'] === $originalCategoryName
                    && $category['quantity'] === 2
                    && $category['revenue'] === 9000
                    && $category['margin'] === 4000
            ));

        $charts = $response->viewData('charts');
        $this->assertSame(9000, $charts['margin']['revenue'][11]);
        $this->assertSame(6000, $charts['margin']['cost'][11]);
        $this->assertSame(3000, $charts['margin']['margin'][11]);
    }
}
