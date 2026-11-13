<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderCreatedAdminNotification;
use App\Notifications\OrderPaymentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.komerce.shipping_cost_key', null);
    }

    public function test_customer_can_place_an_order_and_stock_is_reduced(): void
    {
        $this->seed();
        User::query()->where('is_admin', true)->update(['notification_email' => 'admin@mamikhevanshop.id']);
        Notification::fake();

        $product = Product::query()
            ->where('is_active', true)
            ->where('stock', '>=', 3)
            ->firstOrFail();

        $quantity = 2;
        $selectedSize = $product->sizeList()[0] ?? null;
        $stockBefore = $product->stockForSize($selectedSize);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'size' => $selectedSize,
        ])->assertRedirect();

        $this->get(route('checkout.index'))->assertOk();

        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'Ibu Siti',
            'customer_phone' => '081234567890',
            'customer_email' => 'siti@example.com',
            'customer_address' => 'Jl. Melati No. 12, Bandung, Jawa Barat',
            'shipping_province' => 'Jawa Barat',
            'shipping_city' => 'Kota Bekasi',
            'shipping_district' => 'Bekasi Selatan',
            'shipping_subdistrict' => 'Pekayon Jaya',
            'shipping_postal_code' => '17148',
            'shipping_courier' => Order::COURIER_JNE,
            'note' => 'Tolong dibungkus kado.',
            'payment_method' => Order::PAYMENT_COD,
        ]);
        $order = Order::query()->latest('id')->firstOrFail();

        $response->assertRedirect();
        $signedUrl = $response->headers->get('Location');
        $this->assertStringContainsString('signature=', $signedUrl);
        $this->assertNull($order->user_id);

        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame($quantity, $order->items()->sum('quantity'));
        $this->assertSame($product->price * $quantity, $order->subtotal);
        $this->assertSame(20000, $order->shipping_cost);
        $this->assertSame('17148', $order->shipping_postal_code);
        $this->assertNull($order->payment_unique_code);
        $this->assertSame($product->stock - $quantity, $product->fresh()->stock);
        if ($selectedSize !== null) {
            $this->assertSame($stockBefore - $quantity, $product->fresh()->stockForSize($selectedSize));
            foreach (array_slice($product->sizeList(), 1) as $otherSize) {
                $this->assertSame($product->stockForSize($otherSize), $product->fresh()->stockForSize($otherSize));
            }
        }
        $this->assertNull(session('cart'));
        Notification::assertSentOnDemand(OrderPaymentNotification::class, function (OrderPaymentNotification $notification, array $channels, object $notifiable) use ($order) {
            $lines = $notification->toMail($notifiable)->introLines;

            return $notifiable->routes['mail'] === 'siti@example.com'
                && $notification->order->is($order)
                && in_array('Pesanan '.$order->code.' sudah kami terima.', $lines, true)
                && in_array('2 × '.$order->items->first()->productDisplayName().' — '.$order->items->first()->subtotal_formatted, $lines, true)
                && in_array('Ongkos kirim: Rp 20.000', $lines, true)
                && in_array('Total pembayaran COD: '.$order->total_formatted, $lines, true);
        });
        Notification::assertSentOnDemand(OrderCreatedAdminNotification::class, function (OrderCreatedAdminNotification $notification, array $channels, object $notifiable): bool {
            $mail = $notification->toMail($notifiable);

            return $notifiable->routes['mail'] === 'admin@mamikhevanshop.id'
                && $mail->subject === 'Pesanan baru '.$notification->order->code.' dengan pembayaran COD'
                && in_array('Metode: Bayar di Tempat (COD). Pembayaran dilakukan saat pesanan diterima.', $mail->introLines, true);
        });

        $this->get($signedUrl)
            ->assertOk()
            ->assertSee($order->code);
    }

    public function test_signed_in_buyer_order_is_linked_to_their_account(): void
    {
        $this->seed();
        $buyer = User::factory()->create();
        $product = Product::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($buyer)->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'size' => $product->sizeList()[0] ?? null,
        ])->assertRedirect();

        $response = $this->post(route('checkout.store'), [
            'customer_name' => $buyer->name,
            'customer_phone' => '081234567890',
            'customer_email' => $buyer->email,
            'customer_address' => 'Jalan Melati Nomor 12, Bandung',
            'shipping_province' => 'Jawa Barat',
            'shipping_city' => 'Kota Bekasi',
            'shipping_district' => 'Bekasi Selatan',
            'shipping_subdistrict' => 'Pekayon Jaya',
            'shipping_postal_code' => '17148',
            'shipping_courier' => Order::COURIER_JNT,
            'payment_method' => Order::PAYMENT_COD,
        ]);

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame($buyer->id, $order->user_id);
        $response->assertRedirect(route('orders.show', $order));
        $this->get(route('orders.index'))->assertSee($order->code);
    }

    public function test_customer_must_select_an_available_size_and_sees_its_stock(): void
    {
        $this->seed();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $sizes = $product->sizeList();
        $availableSize = $sizes[0];
        $soldOutSize = $sizes[1];
        $sizeStocks = array_fill_keys($sizes, 0);
        $sizeStocks[$availableSize] = 2;
        $product->replaceSizeStocks($sizeStocks, clearUnallocated: true);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($availableSize)
            ->assertSee('Sisa')
            ->assertSee('(0)');

        $this->from(route('products.show', $product))->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect(route('products.show', $product))
            ->assertSessionHas('error');

        $this->from(route('products.show', $product))->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'size' => $soldOutSize,
        ])->assertRedirect(route('products.show', $product))
            ->assertSessionHas('error');

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'size' => $availableSize,
        ])->assertSessionHas('success');

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Ukuran '.$availableSize)
            ->assertSee('Sisa ukuran ini: 2 unit');
    }

    public function test_shipping_is_not_automatically_free_above_a_subtotal_threshold(): void
    {
        $this->seed();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $product->update(['price' => 350000]);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'size' => $product->sizeList()[0] ?? null,
        ]);
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertDontSee('menuju gratis ongkir')
            ->assertDontSee('Tambah Rp');

        $subtotal = $product->price;

        $this->post(route('checkout.store'), [
            'customer_name' => 'Bapak Budi',
            'customer_phone' => '081298765432',
            'customer_email' => 'budi@example.com',
            'customer_address' => 'Jl. Kenanga No. 5, Surabaya, Jawa Timur',
            'shipping_province' => 'Jawa Timur',
            'shipping_city' => 'Surabaya',
            'shipping_district' => 'Tegalsari',
            'shipping_subdistrict' => 'Keputran',
            'shipping_postal_code' => '60265',
            'shipping_courier' => Order::COURIER_JNE,
            'payment_method' => Order::PAYMENT_TRANSFER,
            'payment_destination' => $this->bankDestination(),
        ]);

        $order = Order::query()->latest('id')->firstOrFail();

        $this->assertSame($subtotal, $order->subtotal);
        $this->assertSame(30000, $order->shipping_cost);
        $this->assertGreaterThanOrEqual(1, $order->payment_unique_code);
        $this->assertLessThanOrEqual(199, $order->payment_unique_code);
        $this->assertSame($subtotal + $order->shipping_cost + $order->payment_unique_code, $order->total);
    }

    public function test_product_with_free_shipping_has_no_shipping_charge(): void
    {
        $this->seed();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $product->update(['free_shipping' => true, 'price' => 50000, 'weight_grams' => 1000]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);

        $this->postJson(route('checkout.quote'), [
            'shipping_province' => 'Jawa Barat',
            'shipping_city' => 'Kota Bekasi',
            'shipping_postal_code' => '17148',
        ])->assertOk()
            ->assertJsonPath('options.jne.cost', 0)
            ->assertJsonPath('options.jnt.cost', 0)
            ->assertJsonPath('options.gosend.cost', 0);

        $this->post(route('checkout.store'), [
            'customer_name' => 'Ibu Siti',
            'customer_phone' => '081234567890',
            'customer_email' => 'siti@example.com',
            'customer_address' => 'Jl. Melati No. 12, Bekasi',
            'shipping_province' => 'Jawa Barat',
            'shipping_city' => 'Kota Bekasi',
            'shipping_district' => 'Bekasi Selatan',
            'shipping_subdistrict' => 'Pekayon Jaya',
            'shipping_postal_code' => '17148',
            'shipping_courier' => Order::COURIER_JNE,
            'payment_method' => Order::PAYMENT_TRANSFER,
            'payment_destination' => $this->bankDestination(),
        ])->assertRedirect();

        $this->assertSame(0, Order::query()->latest('id')->firstOrFail()->shipping_cost);
    }

    public function test_mixed_cart_calculates_shipping_from_only_non_free_shipping_products(): void
    {
        $this->seed();
        $products = Product::query()->where('is_active', true)->take(2)->get();
        $this->assertCount(2, $products);
        $freeProduct = $products[0]->forceFill(['free_shipping' => true, 'price' => 50000, 'weight_grams' => 900]);
        $freeProduct->save();
        $chargeableProduct = $products[1]->forceFill(['free_shipping' => false, 'price' => 50000, 'weight_grams' => 1000]);
        $chargeableProduct->save();

        $this->post(route('cart.add'), ['product_id' => $freeProduct->id, 'quantity' => 1, 'size' => $freeProduct->sizeList()[0] ?? null]);
        $this->post(route('cart.add'), ['product_id' => $chargeableProduct->id, 'quantity' => 1, 'size' => $chargeableProduct->sizeList()[0] ?? null]);

        $this->postJson(route('checkout.quote'), [
            'shipping_province' => 'Jawa Barat',
            'shipping_city' => 'Kota Bekasi',
            'shipping_postal_code' => '17148',
        ])->assertOk()
            ->assertJsonPath('weight_grams', 1000)
            ->assertJsonPath('options.jne.cost', 20000);

        $this->post(route('checkout.store'), [
            'customer_name' => 'Ibu Siti',
            'customer_phone' => '081234567890',
            'customer_email' => 'siti@example.com',
            'customer_address' => 'Jl. Melati No. 12, Bekasi',
            'shipping_province' => 'Jawa Barat',
            'shipping_city' => 'Kota Bekasi',
            'shipping_district' => 'Bekasi Selatan',
            'shipping_subdistrict' => 'Pekayon Jaya',
            'shipping_postal_code' => '17148',
            'shipping_courier' => Order::COURIER_JNE,
            'payment_method' => Order::PAYMENT_TRANSFER,
            'payment_destination' => $this->bankDestination(),
        ])->assertRedirect();

        $this->assertSame(20000, Order::query()->latest('id')->firstOrFail()->shipping_cost);
    }

    public function test_shipping_quote_lists_couriers_and_marks_assumed_weight(): void
    {
        $this->seed();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $product->update(['price' => 350000]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);

        $this->postJson(route('checkout.quote'), [
            'shipping_province' => 'Jawa Barat',
            'shipping_city' => 'Kota Bekasi',
            'shipping_postal_code' => '17148',
        ])->assertOk()
            ->assertJsonPath('weight_grams', 500)
            ->assertJsonPath('uses_assumed_weight', true)
            ->assertJsonPath('options.jne.cost', 20000)
            ->assertJsonPath('options.jnt.cost', 18000)
            ->assertJsonPath('options.gosend.cost', 35000);

        $this->postJson(route('checkout.quote'), [
            'shipping_province' => 'Jawa Timur',
            'shipping_city' => 'Surabaya',
            'shipping_postal_code' => '60265',
        ])->assertOk()->assertJsonMissingPath('options.gosend');
    }

    public function test_checkout_recalculates_shipping_and_rejects_unavailable_gosend(): void
    {
        $this->seed();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);
        PaymentSetting::query()->create([
            'bank_accounts' => [
                ['bank' => 'BCA', 'account_name' => 'Mamikhevan', 'account_number' => '1234567890', 'is_active' => true],
            ],
        ]);
        Notification::fake();

        $address = [
            'customer_name' => 'Ibu Siti',
            'customer_phone' => '081234567890',
            'customer_email' => 'siti@example.com',
            'customer_address' => 'Jalan Melati Nomor 12, Surabaya',
            'shipping_province' => 'Jawa Timur',
            'shipping_city' => 'Surabaya',
            'shipping_district' => 'Tegalsari',
            'shipping_subdistrict' => 'Keputran',
            'shipping_postal_code' => '60265',
            'payment_method' => Order::PAYMENT_TRANSFER,
            'payment_destination' => array_key_first(PaymentSetting::availableDestinations()),
        ];

        $existingOrderCount = Order::query()->count();
        $this->post(route('checkout.store'), $address + ['shipping_courier' => Order::COURIER_GOSEND])
            ->assertSessionHasErrors('shipping_courier');
        $this->assertDatabaseCount('orders', $existingOrderCount);

        $response = $this->post(route('checkout.store'), $address + [
            'shipping_courier' => Order::COURIER_JNT,
            'shipping_cost' => 1,
            'total' => 1,
        ]);
        $response->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame('internal_rate', $order->shipping_quote_source);
        $this->assertSame(28000, $order->shipping_cost);
        $this->assertSame($order->subtotal + $order->shipping_cost + $order->payment_unique_code, $order->total);
        Notification::assertSentOnDemand(OrderPaymentNotification::class, function (OrderPaymentNotification $notification, array $channels, object $notifiable) use ($order) {
            $lines = $notification->toMail($notifiable)->introLines;

            return $notifiable->routes['mail'] === 'siti@example.com'
                && in_array('Total yang perlu ditransfer: '.$order->total_formatted, $lines, true)
                && in_array('Rekening BCA: 1234567890 a.n. Mamikhevan', $lines, true);
        });
        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee($order->total_formatted)
            ->assertSee('1234567890');
    }

    public function test_gosend_requires_transfer_and_weight_below_five_kilograms(): void
    {
        $this->seed();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $product->update(['weight_grams' => 6000]);
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);

        $destination = [
            'shipping_province' => 'Jawa Barat',
            'shipping_city' => 'Kota Bekasi',
            'shipping_postal_code' => '17148',
        ];
        $this->postJson(route('checkout.quote'), $destination)
            ->assertOk()
            ->assertJsonPath('uses_assumed_weight', false)
            ->assertJsonMissingPath('options.gosend');

        $product->update(['weight_grams' => 1000]);
        $this->postJson(route('checkout.quote'), $destination)
            ->assertOk()
            ->assertJsonPath('options.gosend.cost', 35000);

        $this->post(route('checkout.store'), $destination + [
            'customer_name' => 'Ibu Siti',
            'customer_phone' => '081234567890',
            'customer_email' => 'siti@example.com',
            'customer_address' => 'Jalan Melati Nomor 12, Bekasi',
            'shipping_district' => 'Bekasi Selatan',
            'shipping_subdistrict' => 'Pekayon Jaya',
            'shipping_courier' => Order::COURIER_GOSEND,
            'payment_method' => Order::PAYMENT_COD,
        ])->assertSessionHasErrors('payment_method');
    }

    public function test_checkout_requires_email_before_creating_an_order(): void
    {
        $this->seed();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);
        Notification::fake();
        $existingOrderCount = Order::query()->count();

        $this->post(route('checkout.store'), [
            'customer_name' => 'Ibu Siti',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jalan Melati Nomor 12, Bekasi',
            'shipping_province' => 'Jawa Barat',
            'shipping_city' => 'Kota Bekasi',
            'shipping_district' => 'Bekasi Selatan',
            'shipping_subdistrict' => 'Pekayon Jaya',
            'shipping_postal_code' => '17148',
            'shipping_courier' => Order::COURIER_JNE,
            'payment_method' => Order::PAYMENT_TRANSFER,
        ])->assertSessionHasErrors(['customer_email' => 'Masukkan email agar kami dapat mengirim konfirmasi pesanan.']);

        $this->assertDatabaseCount('orders', $existingOrderCount);
        Notification::assertNothingSent();
    }

    public function test_queued_order_receipt_keeps_checkout_amount_if_order_changes_later(): void
    {
        $this->seed();
        $order = Order::query()->firstOrFail();
        $order->update([
            'shipping_quote_source' => 'internal_rate',
            'payment_method' => Order::PAYMENT_TRANSFER,
            'total' => 120000,
        ]);
        $notification = new OrderPaymentNotification($order);

        $order->update([
            'total' => 125000,
        ]);

        $lines = $notification->toMail(new \stdClass)->introLines;
        $this->assertContains('Total yang perlu ditransfer: Rp 120.000', $lines);
        $this->assertNotContains('Total yang perlu ditransfer: Rp 125.000', $lines);
    }

    public function test_customer_selects_one_active_bank_and_admin_receives_the_same_payment_destination(): void
    {
        $this->seed();
        User::query()->where('is_admin', true)->update(['notification_email' => 'admin@mamikhevanshop.id']);
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);
        $settings = PaymentSetting::query()->create(['bank_accounts' => [
            ['bank' => 'BCA', 'account_name' => 'Toko', 'account_number' => '111111', 'is_active' => true],
            ['bank' => 'BRI', 'account_name' => 'Toko', 'account_number' => '222222', 'is_active' => true],
            ['bank' => 'Mandiri', 'account_name' => 'Toko', 'account_number' => '333333', 'is_active' => false],
        ]]);
        $destination = array_key_last(PaymentSetting::availableDestinations());
        Notification::fake();

        $this->get(route('checkout.index'))->assertOk()->assertSee('222222')->assertDontSee('333333');
        $response = $this->post(route('checkout.store'), $this->transferPayload() + ['payment_destination' => $destination]);
        $response->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame('222222', $order->payment_destination['account_number']);
        $this->assertSame('BRI', $order->payment_destination['bank']);
        $settings->update(['bank_accounts' => []]);
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('222222')->assertDontSee('111111');

        Notification::assertSentOnDemand(OrderPaymentNotification::class, function (OrderPaymentNotification $notification, array $channels, object $notifiable): bool {
            $lines = $notification->toMail($notifiable)->introLines;

            return $notifiable->routes['mail'] === 'siti@example.com'
                && in_array('Rekening BRI: 222222 a.n. Toko', $lines, true)
                && ! str_contains(implode(' ', $lines), '111111');
        });
        Notification::assertSentOnDemand(OrderCreatedAdminNotification::class, function (OrderCreatedAdminNotification $notification, array $channels, object $notifiable) use ($order): bool {
            $lines = $notification->toMail($notifiable)->introLines;

            return $notifiable->routes['mail'] === 'admin@mamikhevanshop.id'
                && $notification->order->is($order)
                && in_array('Pelanggan diminta transfer ke rekening BRI: 222222 a.n. Toko', $lines, true)
                && in_array('Total yang akan dibayar: '.$order->total_formatted, $lines, true);
        });
    }

    public function test_order_notification_is_sent_only_to_admins_with_a_notification_email(): void
    {
        $this->seed();
        User::query()->where('is_admin', true)->update(['notification_email' => 'pertama@example.com']);
        User::factory()->create(['role' => User::ROLE_MANAGEMENT, 'notification_email' => 'kedua@example.com']);
        User::factory()->create(['role' => User::ROLE_OPERATOR, 'notification_email' => null]);
        User::factory()->create(['is_admin' => false, 'notification_email' => 'pelanggan-lain@example.com']);
        config()->set('shop.store.email', 'toko@example.com');
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);
        Notification::fake();

        $this->post(route('checkout.store'), $this->transferPayload() + [
            'payment_destination' => $this->bankDestination(),
        ])->assertRedirect();

        Notification::assertSentOnDemandTimes(OrderCreatedAdminNotification::class, 2);
        Notification::assertSentOnDemand(OrderCreatedAdminNotification::class, function (OrderCreatedAdminNotification $notification, array $channels, object $notifiable): bool {
            return $notifiable->routes['mail'] === 'pertama@example.com';
        });
        Notification::assertSentOnDemand(OrderCreatedAdminNotification::class, function (OrderCreatedAdminNotification $notification, array $channels, object $notifiable): bool {
            return $notifiable->routes['mail'] === 'kedua@example.com';
        });
        Notification::assertSentOnDemandOnce(OrderPaymentNotification::class);
        Notification::assertCount(3);
    }

    public function test_checkout_requires_an_available_payment_destination_for_transfer(): void
    {
        $this->seed();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);
        $existingOrders = Order::query()->count();
        Notification::fake();

        $this->post(route('checkout.store'), $this->transferPayload())
            ->assertSessionHasErrors(['payment_destination' => 'Pilih satu rekening atau QRIS untuk pembayaran.']);
        $this->post(route('checkout.store'), $this->transferPayload() + ['payment_destination' => 'bank:invalid'])
            ->assertSessionHasErrors(['payment_destination' => 'Rekening atau QRIS yang dipilih sudah tidak tersedia. Pilih lagi.']);

        $this->assertDatabaseCount('orders', $existingOrders);
        Notification::assertNothingSent();
    }

    public function test_qris_selection_is_included_in_customer_and_admin_email(): void
    {
        $this->seed();
        User::query()->where('is_admin', true)->update(['notification_email' => 'admin@mamikhevanshop.id']);
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);
        Storage::fake('public');
        Storage::disk('public')->put('payment/qris.png', 'qris-image');
        PaymentSetting::query()->create(['qris_enabled' => true, 'qris_image' => 'payment/qris.png']);
        Notification::fake();

        $response = $this->post(route('checkout.store'), $this->transferPayload() + ['payment_destination' => 'qris']);
        $response->assertRedirect();
        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame('qris', $order->payment_destination['type']);
        $this->assertSame('QRIS', $order->paymentLabel());
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('Bayar dengan QRIS');
        Notification::assertSentOnDemand(OrderPaymentNotification::class, function (OrderPaymentNotification $notification, array $channels, object $notifiable): bool {
            $message = $notification->toMail($notifiable);

            return in_array('QRIS pembayaran terlampir pada email ini.', $message->introLines, true)
                && count($message->rawAttachments) === 1;
        });
        Notification::assertSentOnDemand(OrderCreatedAdminNotification::class, function (OrderCreatedAdminNotification $notification, array $channels, object $notifiable): bool {
            return in_array('Pelanggan diminta membayar melalui QRIS.', $notification->toMail($notifiable)->introLines, true);
        });

        $admin = User::query()->where('is_admin', true)->firstOrFail();
        $this->actingAs($admin)->put(route('admin.payment-settings.update'), ['remove_qris_image' => 1])
            ->assertRedirect();
        Storage::disk('public')->assertExists('payment/qris.png');
        $this->get(route('orders.show', $order))->assertOk()->assertSee('Bayar dengan QRIS');
    }

    private function transferPayload(): array
    {
        return [
            'customer_name' => 'Ibu Siti',
            'customer_phone' => '081234567890',
            'customer_email' => 'siti@example.com',
            'customer_address' => 'Jalan Melati Nomor 12, Bekasi',
            'shipping_province' => 'Jawa Barat',
            'shipping_city' => 'Kota Bekasi',
            'shipping_district' => 'Bekasi Selatan',
            'shipping_subdistrict' => 'Pekayon Jaya',
            'shipping_postal_code' => '17148',
            'shipping_courier' => Order::COURIER_JNE,
            'payment_method' => Order::PAYMENT_TRANSFER,
        ];
    }

    private function bankDestination(): string
    {
        PaymentSetting::query()->firstOrCreate([], [
            'bank_accounts' => [
                ['bank' => 'BCA', 'account_name' => 'Mamikhevan', 'account_number' => '1234567890', 'is_active' => true],
            ],
        ]);

        return array_key_first(PaymentSetting::availableDestinations());
    }
}
