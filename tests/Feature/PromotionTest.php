<?php

namespace Tests\Feature;

use App\Http\Controllers\GiveawayController;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use App\Notifications\OrderPaymentNotification;
use App\Services\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PromotionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.komerce.shipping_cost_key', null);
    }

    public function test_only_admin_can_create_a_scheduled_promotion_in_wib(): void
    {
        $payload = [
            'name' => 'Diskon Hari Sale', 'event_name' => '12.12',
            'type' => Promotion::TYPE_PRICE, 'target_type' => Promotion::TARGET_ALL,
            'discount_kind' => Promotion::KIND_PERCENT, 'discount_value' => 10,
            'starts_at' => '2026-12-12T00:00', 'ends_at' => '2026-12-13T00:00', 'is_active' => 1,
        ];

        $this->post(route('admin.promotions.store'), $payload)->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->post(route('admin.promotions.store'), $payload)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]))
            ->post(route('admin.promotions.store'), $payload)->assertRedirect(route('admin.promotions.index'));

        $promotion = Promotion::query()->sole();
        $this->assertSame('2026-12-11 17:00:00', $promotion->starts_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('12.12', $promotion->event_name);
        $this->get(route('admin.promotions.index'))->assertOk()->assertSee('Diskon Hari Sale');
        $this->get(route('admin.promotions.create'))->assertOk();
        $this->get(route('admin.promotions.edit', $promotion))->assertOk();
    }

    public function test_admin_rejects_invalid_percentage_and_gift_size(): void
    {
        $this->seed();
        $admin = User::query()->where('is_admin', true)->firstOrFail();
        $gift = Product::query()->whereNotNull('sizes')->firstOrFail();
        $base = ['name' => 'Promo', 'target_type' => Promotion::TARGET_ALL,
            'starts_at' => '2026-12-12T00:00', 'ends_at' => '2026-12-13T00:00'];

        $this->actingAs($admin)->post(route('admin.promotions.store'), $base + [
            'type' => Promotion::TYPE_PRICE, 'discount_kind' => Promotion::KIND_PERCENT, 'discount_value' => 101,
        ])->assertSessionHasErrors('discount_value');
        $this->post(route('admin.promotions.store'), $base + [
            'type' => Promotion::TYPE_BUY_GET, 'buy_quantity' => 2,
            'gift_product_id' => $gift->id, 'gift_size' => 'INVALID',
            'gift_quantity' => 1, 'gift_unit_price' => 50000,
        ])->assertSessionHasErrors('gift_size');

        $this->assertDatabaseCount('promotions', 0);
    }

    public function test_admin_can_edit_and_delete_a_promotion(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
        $promotion = Promotion::factory()->create();
        $payload = [
            'name' => 'Subsidi ongkir', 'type' => Promotion::TYPE_SHIPPING_SUBSIDY,
            'target_type' => Promotion::TARGET_ALL, 'discount_value' => 12000,
            'starts_at' => '2026-12-12T00:00', 'ends_at' => '2026-12-13T00:00',
            'is_active' => 1,
        ];

        $this->actingAs($admin)->put(route('admin.promotions.update', $promotion), $payload)
            ->assertRedirect(route('admin.promotions.index'));
        $this->assertSame('Subsidi ongkir', $promotion->fresh()->name);
        $this->assertSame(12000, $promotion->fresh()->discount_value);

        $this->delete(route('admin.promotions.destroy', $promotion))
            ->assertRedirect(route('admin.promotions.index'));
        $this->assertDatabaseCount('promotions', 0);
    }

    public function test_best_eligible_promotion_respects_target_schedule_and_minimum(): void
    {
        $this->seed();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $product->update(['price' => 100000]);
        $this->setStock($product, 20);
        $items = [['product' => $product->fresh(), 'quantity' => 2]];

        Promotion::factory()->create(['name' => 'Expired', 'discount_value' => 90, 'ends_at' => now()->subDay()]);
        Promotion::factory()->create(['name' => 'Inactive', 'discount_value' => 99, 'is_active' => false]);
        Promotion::factory()->create(['name' => 'Minimum', 'discount_value' => 80, 'minimum_subtotal' => 250000]);
        Promotion::factory()->create(['name' => 'Other product', 'discount_value' => 70, 'target_type' => Promotion::TARGET_PRODUCT, 'product_id' => Product::query()->whereKeyNot($product->id)->firstOrFail()->id]);
        Promotion::factory()->create(['name' => 'Category', 'discount_value' => 10, 'target_type' => Promotion::TARGET_CATEGORY, 'category_id' => $product->category_id]);
        Promotion::factory()->create(['name' => 'Shipping', 'type' => Promotion::TYPE_SHIPPING_SUBSIDY, 'discount_kind' => Promotion::KIND_FIXED, 'discount_value' => 25000]);

        $result = app(PromotionService::class)->bestFor($items, 30000);

        $this->assertSame('Shipping', $result['promotion']->name);
        $this->assertSame(25000, $result['shipping_discount']);
        $this->assertSame(0, $result['product_discount']);
    }

    public function test_checkout_applies_best_promotion_to_quote_order_and_email(): void
    {
        $this->seed();
        Notification::fake();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $product->update(['price' => 100000]);
        $this->setStock($product, 20);
        Promotion::factory()->create(['name' => 'Potongan produk', 'discount_kind' => Promotion::KIND_FIXED, 'discount_value' => 30000]);
        Promotion::factory()->create(['name' => 'Potongan ongkir', 'type' => Promotion::TYPE_SHIPPING_DISCOUNT, 'discount_kind' => Promotion::KIND_PERCENT, 'discount_value' => 50]);
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);

        $this->postJson(route('checkout.quote'), [
            'shipping_province' => 'Jawa Barat', 'shipping_city' => 'Kota Bekasi', 'shipping_postal_code' => '17148',
        ])->assertOk()->assertJsonPath('options.jne.payable_total', 90000)
            ->assertJsonPath('options.jne.promotion.name', 'Potongan produk');
        $response = $this->post(route('checkout.store'), $this->checkoutPayload());

        $response->assertRedirect();
        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame('Potongan produk', $order->promotion_name);
        $this->assertSame(30000, $order->promotion_discount);
        $this->assertSame(90000, $order->total);
        $this->assertSame(19, $product->fresh()->stock);
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('Diskon produk');
        Notification::assertSentOnDemand(OrderPaymentNotification::class, function (OrderPaymentNotification $notification, array $channels, object $notifiable): bool {
            return in_array('Diskon produk: -Rp 30.000', $notification->toMail($notifiable)->introLines, true);
        });
    }

    public function test_buy_get_adds_separate_free_item_and_reserves_its_stock(): void
    {
        $this->seed();
        Notification::fake();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $gift = Product::query()->whereKeyNot($product->id)->where('is_active', true)->firstOrFail();
        $product->update(['price' => 50000]);
        $this->setStock($product, 20);
        $this->setStock($gift, 1);
        Promotion::factory()->create([
            'name' => 'Beli dua gratis satu', 'type' => Promotion::TYPE_BUY_GET,
            'discount_kind' => null, 'discount_value' => 0, 'buy_quantity' => 2,
            'gift_product_id' => $gift->id, 'gift_size' => $gift->sizeList()[0] ?? null,
            'gift_quantity' => 1, 'gift_unit_price' => 40000,
        ]);
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 2, 'size' => $product->sizeList()[0] ?? null]);

        $response = $this->post(route('checkout.store'), $this->checkoutPayload());

        $response->assertRedirect();
        $order = Order::query()->latest('id')->firstOrFail();
        $giftItem = $order->items()->where('is_promo_gift', true)->sole();
        $this->assertSame($gift->id, $giftItem->product_id);
        $this->assertSame(0, $giftItem->price);
        $this->assertSame(0, $giftItem->subtotal);
        $this->assertSame(40000, $giftItem->original_price);
        $this->assertSame(0, $gift->fresh()->stock);
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('Hadiah promo');

        $this->actingAs(User::query()->where('is_admin', true)->firstOrFail())
            ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_CANCELLED])
            ->assertRedirect();
        $this->assertSame(1, $gift->fresh()->stock);
        $this->assertSame(20, $product->fresh()->stock);
    }

    public function test_voucher_code_discounts_checkout_and_is_saved_on_order(): void
    {
        $this->seed();
        Notification::fake();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $product->update(['price' => 100000]);
        $this->setStock($product, 20);
        Promotion::factory()->create([
            'name' => 'Voucher hemat', 'voucher_code' => 'HEMAT10',
            'discount_kind' => Promotion::KIND_PERCENT, 'discount_value' => 10,
        ]);
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);

        $shipping = ['shipping_province' => 'Jawa Barat', 'shipping_city' => 'Kota Bekasi', 'shipping_postal_code' => '17148'];

        $this->postJson(route('checkout.quote'), $shipping)->assertOk()
            ->assertJsonPath('options.jne.promotion', null)
            ->assertJsonPath('voucher_error', null);

        $this->postJson(route('checkout.quote'), $shipping + ['voucher_code' => 'hemat10'])->assertOk()
            ->assertJsonPath('options.jne.promotion.voucher_code', 'HEMAT10')
            ->assertJsonPath('options.jne.promotion.product_discount', 10000)
            ->assertJsonPath('options.jne.payable_total', 110000)
            ->assertJsonPath('voucher_error', null);

        $this->postJson(route('checkout.quote'), $shipping + ['voucher_code' => 'TIDAKADA'])->assertOk()
            ->assertJsonPath('voucher_error', 'Kode voucher tidak ditemukan atau sudah tidak berlaku.');

        $this->post(route('checkout.store'), $this->checkoutPayload() + ['voucher_code' => 'HEMAT10'])->assertRedirect();
        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame('HEMAT10', $order->voucher_code);
        $this->assertSame(10000, $order->promotion_discount);
        $this->assertSame(110000, $order->total);
    }

    public function test_checkout_rejects_unknown_voucher_code(): void
    {
        $this->seed();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $product->update(['price' => 100000]);
        $this->setStock($product, 20);
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);
        $orderCount = Order::query()->count();

        $this->post(route('checkout.store'), $this->checkoutPayload() + ['voucher_code' => 'TIDAKADA'])
            ->assertSessionHasErrors('voucher_code');
        $this->assertSame($orderCount, Order::query()->count());
    }

    public function test_quantity_tier_discount_uses_highest_reached_tier(): void
    {
        $this->seed();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $product->update(['price' => 50000]);
        $this->setStock($product, 20);
        Promotion::factory()->create([
            'name' => 'Beli banyak',
            'type' => Promotion::TYPE_QUANTITY_TIER,
            'discount_kind' => null,
            'discount_value' => 0,
            'tiers' => [
                ['min_quantity' => 2, 'discount_kind' => Promotion::KIND_PERCENT, 'discount_value' => 5, 'maximum_discount' => null],
                ['min_quantity' => 4, 'discount_kind' => Promotion::KIND_PERCENT, 'discount_value' => 10, 'maximum_discount' => null],
            ],
        ]);

        $service = app(PromotionService::class);

        $three = $service->bestFor([['product' => $product->fresh(), 'quantity' => 3]], 20000);
        $this->assertSame(7500, $three['product_discount']);
        $this->assertSame(2, $three['tier_min_quantity']);

        $five = $service->bestFor([['product' => $product->fresh(), 'quantity' => 5]], 20000);
        $this->assertSame(25000, $five['product_discount']);
        $this->assertSame(4, $five['tier_min_quantity']);

        $this->assertNull($service->bestFor([['product' => $product->fresh(), 'quantity' => 1]], 20000));
    }

    public function test_admin_can_create_voucher_and_quantity_tier_promotion(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
        $payload = [
            'name' => 'Voucher bertingkat', 'voucher_code' => 'tier10',
            'type' => Promotion::TYPE_QUANTITY_TIER, 'target_type' => Promotion::TARGET_ALL,
            'tiers' => [
                ['min_quantity' => 4, 'discount_kind' => Promotion::KIND_PERCENT, 'discount_value' => 10],
                ['min_quantity' => 2, 'discount_kind' => Promotion::KIND_FIXED, 'discount_value' => 5000],
            ],
            'starts_at' => '2026-12-12T00:00', 'ends_at' => '2026-12-13T00:00', 'is_active' => 1,
        ];

        $this->actingAs($admin)->post(route('admin.promotions.store'), $payload)
            ->assertRedirect(route('admin.promotions.index'));

        $promotion = Promotion::query()->sole();
        $this->assertSame('TIER10', $promotion->voucher_code);
        $this->assertSame([2, 4], collect($promotion->tiers)->pluck('min_quantity')->all());
        $this->assertSame(5000, $promotion->tiers[0]['discount_value']);

        $this->get(route('admin.promotions.edit', $promotion))->assertOk()->assertSee('Voucher bertingkat');
    }

    public function test_admin_rejects_duplicate_quantity_tiers(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

        $this->actingAs($admin)->post(route('admin.promotions.store'), [
            'name' => 'Duplikat', 'type' => Promotion::TYPE_QUANTITY_TIER, 'target_type' => Promotion::TARGET_ALL,
            'tiers' => [
                ['min_quantity' => 2, 'discount_kind' => Promotion::KIND_PERCENT, 'discount_value' => 5],
                ['min_quantity' => 2, 'discount_kind' => Promotion::KIND_PERCENT, 'discount_value' => 10],
            ],
            'starts_at' => '2026-12-12T00:00', 'ends_at' => '2026-12-13T00:00',
        ])->assertSessionHasErrors('tiers');

        $this->assertDatabaseCount('promotions', 0);
    }

    public function test_giveaway_claim_creates_a_free_order_and_only_charges_shipping(): void
    {
        $this->seed();
        Notification::fake();
        $gift = Product::query()->where('is_active', true)->firstOrFail();
        $this->setStock($gift, 3);
        Promotion::factory()->create([
            'name' => 'Treasure gratis', 'type' => Promotion::TYPE_GIVEAWAY,
            'voucher_code' => 'TREASURE24', 'discount_kind' => null, 'discount_value' => 0,
            'gift_product_id' => $gift->id, 'gift_size' => $gift->sizeList()[0] ?? null,
            'gift_quantity' => 1, 'gift_unit_price' => 75000,
        ]);

        $this->post(route('giveaway.store'), ['code' => 'treasure24'])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHas(GiveawayController::SESSION_KEY, 'TREASURE24');

        $this->get(route('checkout.index'))->assertOk()->assertSee('Klaim hadiah aktif');

        $this->post(route('checkout.store'), $this->checkoutPayload())->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame(0, $order->subtotal);
        $this->assertSame(20000, $order->shipping_cost);
        $this->assertSame(20000, $order->total);
        $this->assertSame('Treasure gratis', $order->promotion_name);
        $this->assertSame('TREASURE24', $order->voucher_code);

        $giftItem = $order->items()->sole();
        $this->assertTrue($giftItem->is_promo_gift);
        $this->assertSame(0, $giftItem->price);
        $this->assertSame(0, $giftItem->subtotal);
        $this->assertSame(75000, $giftItem->original_price);
        $this->assertSame(2, $gift->fresh()->stock);
        $this->assertNull(session(GiveawayController::SESSION_KEY));
    }

    public function test_giveaway_claim_rejects_unknown_code_and_missing_gift_stock(): void
    {
        $this->seed();
        $gift = Product::query()->where('is_active', true)->firstOrFail();
        $this->setStock($gift, 0);
        Promotion::factory()->create([
            'name' => 'Habis', 'type' => Promotion::TYPE_GIVEAWAY, 'voucher_code' => 'HABIS',
            'discount_kind' => null, 'discount_value' => 0,
            'gift_product_id' => $gift->id, 'gift_quantity' => 1, 'gift_unit_price' => 50000,
        ]);

        $this->from(route('giveaway.claim'))
            ->post(route('giveaway.store'), ['code' => 'TIDAKADA'])
            ->assertRedirect(route('giveaway.claim'))
            ->assertSessionHasErrors('code');

        $this->post(route('giveaway.store'), ['code' => 'HABIS'])
            ->assertSessionHasErrors('code');

        $this->assertNull(session(GiveawayController::SESSION_KEY));
    }

    public function test_admin_giveaway_requires_voucher_and_gift_details(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

        $this->actingAs($admin)->post(route('admin.promotions.store'), [
            'name' => 'Giveaway', 'type' => Promotion::TYPE_GIVEAWAY, 'target_type' => Promotion::TARGET_ALL,
            'starts_at' => '2026-12-12T00:00', 'ends_at' => '2026-12-13T00:00',
        ])->assertSessionHasErrors(['voucher_code', 'gift_product_id', 'gift_quantity', 'gift_unit_price']);

        $this->assertDatabaseCount('promotions', 0);
    }

    /** @return array<string, string> */
    private function checkoutPayload(): array
    {
        return [
            'customer_name' => 'Ibu Siti', 'customer_phone' => '081234567890',
            'customer_email' => 'siti@example.com', 'customer_address' => 'Jalan Melati Nomor 12, Bekasi',
            'shipping_province' => 'Jawa Barat', 'shipping_city' => 'Kota Bekasi',
            'shipping_district' => 'Bekasi Selatan', 'shipping_subdistrict' => 'Pekayon Jaya',
            'shipping_postal_code' => '17148', 'shipping_courier' => Order::COURIER_JNE,
            'payment_method' => Order::PAYMENT_COD,
        ];
    }

    private function setStock(Product $product, int $stock): void
    {
        if ($product->sizeList() === []) {
            $product->update(['stock' => $stock]);

            return;
        }

        $stocks = array_fill_keys($product->sizeList(), 0);
        $stocks[$product->sizeList()[0]] = $stock;
        $product->replaceSizeStocks($stocks, clearUnallocated: true);
    }
}
