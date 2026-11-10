<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_place_an_order_and_stock_is_reduced(): void
    {
        $this->seed();

        $product = Product::query()
            ->where('is_active', true)
            ->where('stock', '>=', 3)
            ->firstOrFail();

        $quantity = 2;

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'size' => $product->sizeList()[0] ?? null,
        ])->assertRedirect();

        $this->get(route('checkout.index'))->assertOk();

        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'Ibu Siti',
            'customer_phone' => '081234567890',
            'customer_email' => 'siti@example.com',
            'customer_address' => 'Jl. Melati No. 12, Bandung, Jawa Barat',
            'note' => 'Tolong dibungkus kado.',
            'payment_method' => Order::PAYMENT_COD,
        ]);

        $order = Order::query()->latest('id')->firstOrFail();

        $response->assertRedirect(route('orders.show', $order));

        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame($quantity, $order->items()->sum('quantity'));
        $this->assertSame($product->price * $quantity, $order->subtotal);
        $this->assertSame($product->stock - $quantity, $product->fresh()->stock);
        $this->assertNull(session('cart'));

        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($order->code);
    }

    public function test_shipping_is_free_above_the_minimum(): void
    {
        $this->seed();

        $product = Product::query()
            ->where('is_active', true)
            ->where('price', '>=', CartService::FREE_SHIPPING_MIN)
            ->first();

        if (! $product) {
            $product = Product::query()->where('is_active', true)->firstOrFail();
        }

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $subtotal = $product->price;

        $this->post(route('checkout.store'), [
            'customer_name' => 'Bapak Budi',
            'customer_phone' => '081298765432',
            'customer_address' => 'Jl. Kenanga No. 5, Surabaya, Jawa Timur',
            'payment_method' => Order::PAYMENT_TRANSFER,
        ]);

        $order = Order::query()->latest('id')->firstOrFail();

        $this->assertSame($subtotal, $order->subtotal);
        $this->assertSame($subtotal >= CartService::FREE_SHIPPING_MIN ? 0 : CartService::SHIPPING_COST, $order->shipping_cost);
    }
}
