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

    public function test_sale_section_and_catalog_show_only_active_discounted_products(): void
    {
        [$saleProduct, $regularProduct, $inactiveSaleProduct] = Product::query()->orderBy('id')->take(3)->get()->all();

        $saleProduct->update(['is_active' => true, 'price' => 80000, 'compare_price' => 100000]);
        $regularProduct->update(['is_active' => true, 'price' => 80000, 'compare_price' => 80000]);
        $inactiveSaleProduct->update(['is_active' => false, 'price' => 80000, 'compare_price' => 100000]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Produk sale')
            ->assertSee(route('products.index', ['sale' => '1']))
            ->assertViewHas('saleProducts', fn ($products): bool => $products->contains($saleProduct)
                && ! $products->contains($regularProduct)
                && ! $products->contains($inactiveSaleProduct));

        $this->get(route('products.index', ['sale' => '1']))
            ->assertOk()
            ->assertSee('name="sale" value="1"', false)
            ->assertViewHas('products', fn ($products): bool => $products->contains($saleProduct)
                && ! $products->contains($regularProduct)
                && ! $products->contains($inactiveSaleProduct));
    }

    public function test_home_product_sections_sort_by_recency_and_discount_with_out_of_stock_last(): void
    {
        [$newest, $largestDiscount, $outOfStock, $oldest, $olderInStock] = Product::query()->orderBy('id')->take(5)->get()->all();
        Product::query()->update(['is_active' => false]);

        $newest->refresh()->update(['is_active' => true, 'sizes' => null, 'size_stocks' => null, 'stock' => 5, 'price' => 80000, 'compare_price' => 100000]);
        $largestDiscount->refresh()->update(['is_active' => true, 'sizes' => null, 'size_stocks' => null, 'stock' => 5, 'price' => 80000, 'compare_price' => 120000]);
        $outOfStock->refresh()->update(['is_active' => true, 'sizes' => ['M'], 'size_stocks' => ['M' => 0], 'stock' => 5, 'price' => 80000, 'compare_price' => 200000]);
        $oldest->refresh()->update(['is_active' => true, 'sizes' => null, 'size_stocks' => null, 'stock' => 5, 'price' => 80000, 'compare_price' => null]);
        $olderInStock->refresh()->update(['is_active' => true, 'sizes' => null, 'size_stocks' => null, 'stock' => 5, 'price' => 80000, 'compare_price' => null]);

        foreach ([$newest, $largestDiscount, $outOfStock, $oldest, $olderInStock] as $index => $product) {
            $product->created_at = now()->subDays([1, 2, 0, 3, 4][$index]);
            $product->save();
        }

        $response = $this->get(route('home'))->assertOk();

        $this->assertSame(
            [$newest->id, $largestDiscount->id, $oldest->id, $olderInStock->id],
            $response->viewData('newestProducts')->pluck('id')->values()->all()
        );
        $this->assertSame(
            [$largestDiscount->id, $newest->id, $outOfStock->id],
            $response->viewData('saleProducts')->pluck('id')->values()->all()
        );
    }

    public function test_favorite_products_sort_by_sold_units_excluding_cancelled_orders_and_gifts(): void
    {
        [$mostSold, $fewerSold, $outOfStock] = Product::query()->orderBy('id')->take(3)->get()->all();
        Product::query()->update(['is_active' => false]);
        Order::query()->delete();

        foreach ([$mostSold, $fewerSold, $outOfStock] as $product) {
            $product->refresh()->update(['is_active' => true, 'is_featured' => true, 'sizes' => null, 'size_stocks' => null, 'stock' => $product->is($outOfStock) ? 0 : 5]);
        }

        $addOrder = function (Product $product, int $quantity, string $status, int $giftQuantity = 0): void {
            $order = Order::create([
                'customer_name' => 'Pembeli',
                'customer_phone' => '081234567890',
                'customer_address' => 'Jalan Melati 1',
                'payment_method' => Order::PAYMENT_COD,
                'subtotal' => $product->price * $quantity,
                'total' => $product->price * $quantity,
                'status' => $status,
            ]);
            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'subtotal' => $product->price * $quantity,
            ]);

            if ($giftQuantity > 0) {
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => 0,
                    'quantity' => $giftQuantity,
                    'subtotal' => 0,
                    'is_promo_gift' => true,
                ]);
            }
        };

        $addOrder($mostSold, 3, Order::STATUS_COMPLETED);
        $addOrder($fewerSold, 2, Order::STATUS_COMPLETED, 8);
        $addOrder($fewerSold, 10, Order::STATUS_CANCELLED);
        $addOrder($outOfStock, 10, Order::STATUS_COMPLETED);

        $response = $this->get(route('home'))->assertOk();

        $this->assertSame(
            [$mostSold->id, $fewerSold->id, $outOfStock->id],
            $response->viewData('featuredProducts')->pluck('id')->values()->all()
        );
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
