<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Notifications\OrderCompletedAdminNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_can_confirm_receipt_and_review_a_product(): void
    {
        $owner = User::factory()->create();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $order = $this->createOrder($owner, Order::STATUS_SHIPPED);
        $item = $this->createItem($order, $product);
        User::query()->whereIn('role', User::ADMIN_ROLES)->update(['notification_email' => 'admin@example.com']);
        Notification::fake();

        $this->actingAs($owner)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Konfirmasi barang diterima');

        $this->actingAs($owner)
            ->post(route('orders.confirm-receipt', $order))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->delivered_at);
        Notification::assertSentOnDemand(OrderCompletedAdminNotification::class, function (OrderCompletedAdminNotification $notification, array $channels, object $notifiable) use ($order, $product): bool {
            $lines = $notification->toMail($notifiable)->introLines;

            return $notifiable->routes['mail'] === 'admin@example.com'
                && $notification->order->is($order)
                && in_array('Pelanggan: Siti Pembeli (081234567890)', $lines, true)
                && in_array('Metode pembayaran: Bayar di Tempat (COD)', $lines, true)
                && str_contains(implode(' ', $lines), $product->name);
        });

        $this->post(route('orders.confirm-receipt', $order))->assertSessionHas('info');
        Notification::assertSentOnDemandTimes(OrderCompletedAdminNotification::class, 1);

        $this->actingAs($owner)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Beri penilaian');

        $this->actingAs($owner)
            ->get(route('orders.reviews.create', $order))
            ->assertOk()
            ->assertSee($product->name);

        $this->actingAs($owner)
            ->post(route('orders.reviews.store', $order), [
                'reviews' => [
                    $item->id => [
                        'rating' => 5,
                        'product_quality' => 5,
                        'shipping_speed' => 4,
                        'service' => 5,
                        'comment' => 'Bahan adem dan jahitan rapi.',
                    ],
                ],
            ])
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'product_id' => $product->id,
            'user_id' => $owner->id,
            'rating' => 5,
            'product_quality' => 5,
            'shipping_speed' => 4,
            'service' => 5,
            'source' => Review::SOURCE_CUSTOMER,
            'is_visible' => true,
        ]);

        $this->actingAs($owner)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Terima kasih atas penilaianmu!');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Bahan adem dan jahitan rapi.')
            ->assertSee('1 ulasan');
    }

    public function test_receipt_confirmation_is_only_available_for_shipped_orders(): void
    {
        $owner = User::factory()->create();
        $order = $this->createOrder($owner, Order::STATUS_PROCESSING);

        $this->actingAs($owner)
            ->post(route('orders.confirm-receipt', $order))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(Order::STATUS_PROCESSING, $order->fresh()->status);
        $this->assertNull($order->fresh()->delivered_at);
    }

    public function test_guests_without_order_access_cannot_confirm_or_review(): void
    {
        $order = $this->createOrder(null, Order::STATUS_SHIPPED);

        $this->post(route('orders.confirm-receipt', $order))->assertNotFound();
        $this->get(route('orders.reviews.create', $order))->assertNotFound();
    }

    public function test_review_requires_a_completed_order_and_prevents_duplicates(): void
    {
        $owner = User::factory()->create();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $shippedOrder = $this->createOrder($owner, Order::STATUS_SHIPPED);
        $this->createItem($shippedOrder, $product);

        $this->actingAs($owner)
            ->get(route('orders.reviews.create', $shippedOrder))
            ->assertRedirect(route('orders.show', $shippedOrder))
            ->assertSessionHas('error');

        $completedOrder = $this->createOrder($owner, Order::STATUS_COMPLETED);
        $item = $this->createItem($completedOrder, $product);
        $payload = [
            'reviews' => [
                $item->id => [
                    'rating' => 4,
                    'product_quality' => 4,
                    'shipping_speed' => 5,
                    'service' => 4,
                ],
            ],
        ];

        $this->actingAs($owner)
            ->post(route('orders.reviews.store', $completedOrder), $payload)
            ->assertSessionHas('success');

        $this->actingAs($owner)
            ->post(route('orders.reviews.store', $completedOrder), $payload)
            ->assertRedirect(route('orders.show', $completedOrder))
            ->assertSessionHas('info');

        $this->assertSame(1, Review::query()->where('order_id', $completedOrder->id)->count());
    }

    public function test_review_validation_requires_rating_and_aspects(): void
    {
        $owner = User::factory()->create();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $order = $this->createOrder($owner, Order::STATUS_COMPLETED);
        $item = $this->createItem($order, $product);

        $this->actingAs($owner)
            ->post(route('orders.reviews.store', $order), [
                'reviews' => [
                    $item->id => [
                        'rating' => 0,
                        'product_quality' => null,
                        'shipping_speed' => null,
                        'service' => null,
                    ],
                ],
            ])
            ->assertSessionHasErrors([
                'reviews.'.$item->id.'.rating',
                'reviews.'.$item->id.'.product_quality',
                'reviews.'.$item->id.'.shipping_speed',
                'reviews.'.$item->id.'.service',
            ]);

        $this->assertDatabaseCount('reviews', 8);
    }

    public function test_promo_gift_items_are_not_reviewable(): void
    {
        $owner = User::factory()->create();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $order = $this->createOrder($owner, Order::STATUS_COMPLETED);
        $item = $this->createItem($order, $product);
        $this->createItem($order, $product, isPromoGift: true);

        $this->actingAs($owner)
            ->post(route('orders.reviews.store', $order), [
                'reviews' => [
                    $item->id => [
                        'rating' => 5,
                        'product_quality' => 5,
                        'shipping_speed' => 5,
                        'service' => 5,
                    ],
                ],
            ])
            ->assertSessionHas('success');

        $this->assertSame(1, Review::query()->where('order_id', $order->id)->count());
    }

    public function test_admin_can_manage_reviews(): void
    {
        $admin = User::query()->where('is_admin', true)->firstOrFail();
        $product = Product::query()->firstOrFail();

        $this->actingAs($admin)->get(route('admin.reviews.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reviews.create'))->assertOk();

        $this->actingAs($admin)
            ->post(route('admin.reviews.store'), [
                'reviewer_name' => 'Ibu Ani',
                'reviewer_city' => 'Bekasi',
                'product_id' => $product->id,
                'rating' => 4,
                'product_quality' => 4,
                'shipping_speed' => 5,
                'service' => 4,
                'comment' => 'Bagus dan cepat.',
                'source' => Review::SOURCE_MANUAL,
                'is_visible' => '1',
                'reviewed_at' => now()->toDateString(),
                'admin_reply' => 'Terima kasih!',
            ])
            ->assertRedirect(route('admin.reviews.index'))
            ->assertSessionHas('success');

        $review = Review::query()->where('reviewer_name', 'Ibu Ani')->firstOrFail();
        $this->assertTrue($review->is_visible);
        $this->assertNotNull($review->replied_at);

        $this->actingAs($admin)->get(route('admin.reviews.edit', $review))->assertOk();

        $this->actingAs($admin)
            ->put(route('admin.reviews.update', $review), [
                'reviewer_name' => 'Ibu Ani',
                'reviewer_city' => 'Bekasi',
                'product_id' => $product->id,
                'rating' => 3,
                'comment' => 'Cukup baik.',
                'source' => Review::SOURCE_MANUAL,
                'is_visible' => '0',
                'reviewed_at' => now()->toDateString(),
                'admin_reply' => '',
            ])
            ->assertRedirect(route('admin.reviews.index'));

        $review->refresh();
        $this->assertSame(3, $review->rating);
        $this->assertFalse($review->is_visible);
        $this->assertNull($review->replied_at);

        $this->actingAs($admin)
            ->patch(route('admin.reviews.visibility', $review))
            ->assertRedirect();
        $this->assertTrue($review->fresh()->is_visible);

        $this->actingAs($admin)
            ->delete(route('admin.reviews.destroy', $review))
            ->assertRedirect();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_non_admin_cannot_manage_reviews(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.reviews.index'))
            ->assertForbidden();
    }

    public function test_home_and_product_pages_show_only_visible_reviews(): void
    {
        $product = Product::query()->where('is_active', true)->firstOrFail();

        Review::create([
            'product_id' => $product->id,
            'reviewer_name' => 'Pelanggan Terlihat',
            'reviewer_city' => 'Bekasi',
            'rating' => 4,
            'product_quality' => 4,
            'shipping_speed' => 5,
            'service' => 4,
            'comment' => 'Ulasan produk terlihat.',
            'source' => Review::SOURCE_CUSTOMER,
            'is_visible' => true,
            'reviewed_at' => now(),
        ]);

        Review::create([
            'product_id' => $product->id,
            'reviewer_name' => 'Pelanggan Tersembunyi',
            'rating' => 1,
            'comment' => 'Ulasan produk tersembunyi.',
            'source' => Review::SOURCE_MANUAL,
            'is_visible' => false,
            'reviewed_at' => now(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('ulasan Google Maps')
            ->assertSee('Pelanggan Google Maps');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Ulasan produk terlihat.')
            ->assertDontSee('Ulasan produk tersembunyi.');
    }

    private function createOrder(?User $user, string $status): Order
    {
        return Order::create([
            'user_id' => $user?->id,
            'customer_name' => 'Siti Pembeli',
            'customer_phone' => '081234567890',
            'customer_email' => 'siti@example.com',
            'customer_address' => 'Jalan Melati Nomor 12, Bandung',
            'shipping_city' => 'Bekasi',
            'payment_method' => Order::PAYMENT_COD,
            'subtotal' => 100000,
            'shipping_cost' => 0,
            'total' => 100000,
            'status' => $status,
        ]);
    }

    private function createItem(Order $order, Product $product, bool $isPromoGift = false): OrderItem
    {
        return $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'size' => 'M',
            'price' => 50000,
            'quantity' => 1,
            'subtotal' => 50000,
            'is_promo_gift' => $isPromoGift,
        ]);
    }
}
