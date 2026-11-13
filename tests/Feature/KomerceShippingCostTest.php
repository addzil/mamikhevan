<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Notifications\OrderPaymentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KomerceShippingCostTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_uses_verified_komerce_destination_and_recalculates_rate_on_order(): void
    {
        config()->set('services.komerce.shipping_cost_key', 'test-key');
        config()->set('shipping.komerce_origin_id', 12345);
        $this->seed();

        $product = Product::query()->where('is_active', true)->firstOrFail();
        $product->update(['price' => 50000, 'weight_grams' => 750]);
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);
        Storage::fake('public');
        Storage::disk('public')->put('payment/qris.png', 'qris-image');
        PaymentSetting::query()->create([
            'bank_accounts' => [
                ['bank' => 'BCA', 'account_name' => 'Mamikhevan', 'account_number' => '1234567890', 'is_active' => true],
                ['bank' => 'BRI', 'account_name' => 'Nonaktif', 'account_number' => '9999999999', 'is_active' => false],
            ],
            'qris_enabled' => true,
            'qris_image' => 'payment/qris.png',
        ]);
        Notification::fake();

        Http::preventStrayRequests();
        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response([
                'data' => [[
                    'id' => 67890,
                    'label' => 'Pekayon Jaya, Bekasi Selatan, Kota Bekasi, Jawa Barat 17148',
                    'province_name' => 'Jawa Barat',
                    'city_name' => 'Kota Bekasi',
                    'district_name' => 'Bekasi Selatan',
                    'subdistrict_name' => 'Pekayon Jaya',
                    'zip_code' => '17148',
                ]],
            ]),
            'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => Http::response([
                'data' => [
                    ['code' => 'jne', 'service' => 'REG', 'cost' => 20000, 'etd' => '1-2 days'],
                    ['code' => 'jnt', 'service' => 'EZ', 'cost' => 18000, 'etd' => '1-2 days'],
                ],
            ]),
        ]);

        $this->get(route('checkout.index'))->assertOk()->assertSee('Cari wilayah tujuan');
        $this->getJson(route('checkout.destinations', ['search' => 'Pekayon']))
            ->assertOk()
            ->assertJsonPath('destinations.0.id', 67890);

        $destination = [
            'shipping_province' => 'Jawa Barat',
            'shipping_city' => 'Kota Bekasi',
            'shipping_district' => 'Bekasi Selatan',
            'shipping_subdistrict' => 'Pekayon Jaya',
            'shipping_postal_code' => '17148',
            'shipping_destination_id' => 67890,
        ];

        $this->postJson(route('checkout.quote'), $destination)
            ->assertOk()
            ->assertJsonPath('weight_grams', 750)
            ->assertJsonPath('uses_assumed_weight', false)
            ->assertJsonPath('options.jne.cost', 20000)
            ->assertJsonPath('options.jne.source', 'komerce')
            ->assertJsonPath('options.jnt.cost', 18000);

        $this->post(route('checkout.store'), $destination + [
            'customer_name' => 'Ibu Siti',
            'customer_phone' => '081234567890',
            'customer_email' => 'siti@example.com',
            'customer_address' => 'Jalan Melati Nomor 12, Bekasi',
            'shipping_courier' => Order::COURIER_JNE,
            'payment_method' => Order::PAYMENT_TRANSFER,
            'payment_destination' => array_key_first(PaymentSetting::availableDestinations()),
            'shipping_cost' => 1,
        ])->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame('komerce', $order->shipping_quote_source);
        $this->assertSame('REG', $order->shipping_service);
        $this->assertSame(20000, $order->shipping_cost);
        $this->assertSame(50000 + 20000 + $order->payment_unique_code, $order->total);
        Notification::assertSentOnDemand(OrderPaymentNotification::class, function (OrderPaymentNotification $notification, array $channels, object $notifiable) use ($order) {
            $message = $notification->toMail($notifiable);
            $lines = $message->introLines;

            return $notifiable->routes['mail'] === 'siti@example.com'
                && $notification->order->is($order)
                && in_array('Total yang perlu ditransfer: '.$order->total_formatted, $lines, true)
                && in_array('Rekening BCA: 1234567890 a.n. Mamikhevan', $lines, true)
                && ! str_contains(implode(' ', $lines), '9999999999')
                && ! in_array('QRIS pembayaran terlampir pada email ini.', $lines, true)
                && count($message->rawAttachments) === 0;
        });

        Http::assertSent(fn ($request) => $request->hasHeader('key', 'test-key')
            && $request->url() === 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost'
            && (int) $request['origin'] === 12345
            && (int) $request['destination'] === 67890
            && (int) $request['weight'] === 750);
    }

    public function test_changed_destination_is_rejected_before_rate_request(): void
    {
        config()->set('services.komerce.shipping_cost_key', 'test-key');
        config()->set('shipping.komerce_origin_id', 12345);
        $this->seed();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);

        Http::preventStrayRequests();
        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response([
                'data' => [[
                    'id' => 67890,
                    'label' => 'Pekayon Jaya',
                    'province_name' => 'Jawa Barat',
                    'city_name' => 'Kota Bekasi',
                    'district_name' => 'Bekasi Selatan',
                    'subdistrict_name' => 'Pekayon Jaya',
                    'zip_code' => '17148',
                ]],
            ]),
        ]);

        $this->getJson(route('checkout.destinations', ['search' => 'Pekayon']))->assertOk();
        $this->postJson(route('checkout.quote'), [
            'shipping_destination_id' => 67890,
            'shipping_province' => 'Jawa Timur',
            'shipping_city' => 'Surabaya',
            'shipping_postal_code' => '17148',
        ])->assertUnprocessable()->assertJsonValidationErrors('shipping_destination_id');

        PaymentSetting::query()->create(['bank_accounts' => [
            ['bank' => 'BCA', 'account_name' => 'Toko', 'account_number' => '1234567890', 'is_active' => true],
        ]]);
        $existingOrders = Order::query()->count();
        $this->post(route('checkout.store'), [
            'shipping_destination_id' => 67890,
            'shipping_province' => 'Jawa Barat',
            'shipping_city' => 'Kota Bekasi',
            'shipping_district' => 'Bekasi Selatan',
            'shipping_subdistrict' => 'Kelurahan Lain',
            'shipping_postal_code' => '17148',
            'customer_name' => 'Ibu Siti',
            'customer_phone' => '081234567890',
            'customer_email' => 'siti@example.com',
            'customer_address' => 'Jalan Melati Nomor 12, Bekasi',
            'shipping_courier' => Order::COURIER_JNE,
            'payment_method' => Order::PAYMENT_TRANSFER,
            'payment_destination' => array_key_first(PaymentSetting::availableDestinations()),
        ])->assertSessionHasErrors('shipping_destination_id');
        $this->assertDatabaseCount('orders', $existingOrders);

        Http::assertSentCount(1);
    }

    public function test_komerce_rate_failure_is_shown_without_creating_an_order(): void
    {
        config()->set('services.komerce.shipping_cost_key', 'test-key');
        config()->set('shipping.komerce_origin_id', 12345);
        $this->seed();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);
        $existingOrders = Order::query()->count();

        Http::preventStrayRequests();
        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => Http::response(['data' => null], 503),
        ]);

        $this->withSession(['komerce_destinations' => [67890 => [
            'id' => 67890,
            'province_name' => 'Jawa Barat',
            'city_name' => 'Kota Bekasi',
            'district_name' => 'Bekasi Selatan',
            'subdistrict_name' => 'Pekayon Jaya',
            'zip_code' => '17148',
        ]]])->postJson(route('checkout.quote'), [
            'shipping_destination_id' => 67890,
            'shipping_province' => 'Jawa Barat',
            'shipping_city' => 'Kota Bekasi',
            'shipping_postal_code' => '17148',
        ])->assertServiceUnavailable();

        $this->assertDatabaseCount('orders', $existingOrders);
    }

    public function test_shipping_costs_are_not_automatically_waived_by_a_subtotal_threshold(): void
    {
        config()->set('services.komerce.shipping_cost_key', 'test-key');
        config()->set('shipping.komerce_origin_id', 12345);
        $this->seed();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $product->update(['price' => 300000, 'weight_grams' => 500]);
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'size' => $product->sizeList()[0] ?? null]);

        Http::preventStrayRequests();
        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => Http::response([
                'data' => [
                    ['code' => 'jne', 'service' => 'REG', 'cost' => 20000, 'etd' => '1-2 days'],
                    ['code' => 'jnt', 'service' => 'EZ', 'cost' => 18000, 'etd' => '1-2 days'],
                ],
            ]),
        ]);

        $this->withSession(['komerce_destinations' => [67890 => [
            'id' => 67890,
            'province_name' => 'Jawa Barat',
            'city_name' => 'Kota Bekasi',
            'district_name' => 'Bekasi Selatan',
            'subdistrict_name' => 'Pekayon Jaya',
            'zip_code' => '17148',
        ]]])->postJson(route('checkout.quote'), [
            'shipping_destination_id' => 67890,
            'shipping_province' => 'Jawa Barat',
            'shipping_city' => 'Kota Bekasi',
            'shipping_postal_code' => '17148',
        ])->assertOk()
            ->assertJsonPath('options.jne.cost', 20000)
            ->assertJsonPath('options.jnt.cost', 18000)
            ->assertJsonPath('options.gosend.cost', 35000)
            ->assertJsonPath('options.gosend.source', 'internal_rate');
    }
}
