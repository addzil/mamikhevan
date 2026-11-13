<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\SizeGuideType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
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
            ->assertSee('Mamikhevanshop')
            ->assertDontSee('Paket hemat');
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

    public function test_setelan_size_guide_appears_only_after_measurements_are_added(): void
    {
        $product = Product::query()->where('slug', 'setelan-rainbow')->firstOrFail();
        $guide = SizeGuideType::query()->where('slug', 'setelan')->firstOrFail();
        $product->update(['size_guide_type_id' => $guide->id]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertDontSee('Pengukuran dalam');

        $guide->entries()->where('label', '1T')->update(['chest_cm' => '50']);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Pengukuran dalam');
    }

    public function test_static_and_cart_pages_render(): void
    {
        $this->get(route('about'))->assertOk();
        $this->get(route('shopping-guide'))
            ->assertOk()
            ->assertSee('email konfirmasi')
            ->assertSee('unggah bukti pembayaran')
            ->assertSee('metode/rekening pilihan');
        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('Pertanyaan umum')
            ->assertSee('Bagaimana mengirim bukti pembayaran?')
            ->assertSee('Lacak Pesanan');
        $this->get(route('contact.index'))->assertOk();
        $this->get(route('cart.index'))->assertOk();
        $this->get(route('checkout.index'))->assertRedirect(route('products.index'));
    }

    public function test_order_tracking_page_renders(): void
    {
        $order = Order::query()->firstOrFail();

        $this->get(URL::signedRoute('orders.show', ['order' => $order], absolute: false))
            ->assertOk()
            ->assertSee($order->code);
        $this->get(route('orders.track'))->assertOk();
    }
}
