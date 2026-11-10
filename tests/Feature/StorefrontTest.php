<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_home_page_renders(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Mamikhevanshop');
    }

    public function test_catalog_page_renders(): void
    {
        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Katalog');
    }

    public function test_product_detail_page_renders(): void
    {
        $product = Product::query()->where('is_active', true)->firstOrFail();

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_static_and_cart_pages_render(): void
    {
        $this->get(route('about'))->assertOk();
        $this->get(route('contact.index'))->assertOk();
        $this->get(route('cart.index'))->assertOk();
        $this->get(route('checkout.index'))->assertRedirect(route('products.index'));
    }

    public function test_order_tracking_page_renders(): void
    {
        $order = Order::query()->firstOrFail();

        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($order->code);
    }
}
