<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Notifications\PaymentProofUploadedAdminNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class OrderAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_details_require_owner_or_guest_proof(): void
    {
        $owner = User::factory()->create();
        $otherBuyer = User::factory()->create();
        $order = $this->createOrder($owner);

        $this->get(route('orders.show', $order))->assertNotFound();
        $this->actingAs($otherBuyer)->get(route('orders.show', $order))->assertNotFound();
        $this->actingAs($owner)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('081234567890')
            ->assertHeader('Referrer-Policy', 'no-referrer');
    }

    public function test_guest_can_open_signed_order_link_but_not_modified_link(): void
    {
        $order = $this->createOrder();
        $signedUrl = URL::signedRoute('orders.show', ['order' => $order], absolute: false);

        $this->get($signedUrl)->assertOk()->assertSee($order->code);
        $this->flushSession();
        $this->get($signedUrl.'&changed=1')->assertNotFound();
        $this->get(route('orders.show', $order))->assertNotFound();
    }

    public function test_guest_can_recover_order_access_with_code_and_phone(): void
    {
        $order = $this->createOrder();
        $compactCode = strtolower(str_replace('-', '', $order->code));

        $this->from(route('orders.track'))->post(route('orders.lookup'), [
            'code' => $compactCode,
            'country_code' => '62',
            'phone' => '81299999999',
        ])->assertSessionHasErrors('code');

        $this->get(route('orders.show', $order))->assertNotFound();

        $this->post(route('orders.lookup'), [
            'code' => $compactCode,
            'country_code' => '62',
            'phone' => '81234567890',
        ])->assertRedirect(route('orders.show', $order));

        $this->get(route('orders.show', $order))->assertOk();
    }

    public function test_tracking_keeps_supporting_existing_codes_and_leading_zero(): void
    {
        $order = $this->createOrder();
        $order->update(['code' => 'KK-DEMO-0001']);

        $this->get(route('orders.track'))
            ->assertOk()
            ->assertSee('Indonesia (+62)')
            ->assertSee('Ketik huruf dan angka saja');

        $this->post(route('orders.lookup'), [
            'code' => 'KKDEMO0001',
            'country_code' => '62',
            'phone' => '081234567890',
        ])->assertRedirect(route('orders.show', $order));

        $this->post(route('orders.lookup'), [
            'code' => 'KK-DEMO-0001',
            'phone' => '081234567890',
        ])->assertRedirect(route('orders.show', $order));
    }

    public function test_tracking_checks_the_selected_country_code_and_phone_format(): void
    {
        $order = $this->createOrder();
        $compactCode = str_replace('-', '', $order->code);

        $this->post(route('orders.lookup'), [
            'code' => $compactCode,
            'country_code' => '60',
            'phone' => '81234567890',
        ])->assertSessionHasErrors('code');

        $this->post(route('orders.lookup'), [
            'code' => $compactCode,
            'country_code' => '999',
            'phone' => '81234567890',
        ])->assertSessionHasErrors(['country_code' => 'Pilih kode negara yang tersedia.']);

        $this->post(route('orders.lookup'), [
            'code' => 'KK!'.$compactCode,
            'country_code' => '62',
            'phone' => '812-3456',
        ])->assertSessionHasErrors([
            'code' => 'Masukkan kode pesanan menggunakan huruf dan angka saja.',
            'phone' => 'Nomor telepon harus berisi 7–15 angka tanpa kode negara.',
        ]);
    }

    public function test_tracking_supports_a_stored_international_phone(): void
    {
        $order = $this->createOrder();
        $order->update(['customer_phone' => '+60123456789']);

        $this->post(route('orders.lookup'), [
            'code' => str_replace('-', '', $order->code),
            'country_code' => '60',
            'phone' => '0123456789',
        ])->assertRedirect(route('orders.show', $order));
    }

    public function test_buyer_order_list_contains_only_their_orders(): void
    {
        $owner = User::factory()->create();
        $ownOrder = $this->createOrder($owner);
        $otherOrder = $this->createOrder(User::factory()->create());

        $this->get(route('orders.index'))->assertRedirect(route('login'));
        $this->actingAs($owner)->get(route('orders.index'))
            ->assertOk()
            ->assertSee($ownOrder->code)
            ->assertDontSee($otherOrder->code);
    }

    public function test_customer_can_upload_payment_proof_and_only_admin_can_view_it(): void
    {
        Storage::fake('local');
        $buyer = User::factory()->create();
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR, 'notification_email' => 'admin@example.com']);
        User::factory()->create(['role' => User::ROLE_MANAGEMENT, 'notification_email' => 'management@example.com']);
        $order = $this->createOrder($buyer);
        $order->update([
            'payment_method' => Order::PAYMENT_TRANSFER,
            'payment_destination' => ['type' => 'bank', 'bank' => 'BRI', 'account_number' => '123456', 'account_name' => 'Toko'],
        ]);
        $order->items()->create([
            'product_name' => 'Dress Anak',
            'price' => 50000,
            'quantity' => 2,
            'subtotal' => 100000,
        ]);
        $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQDJ/pLvAAAAAElFTkSuQmCC');
        Notification::fake();

        $this->actingAs($buyer)
            ->post(route('orders.payment-proof.store', $order), [
                'payment_proof' => UploadedFile::fake()->createWithContent('bukti.png', $image),
            ])
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertNotNull($order->payment_proof_path);
        $this->assertNotNull($order->payment_proof_uploaded_at);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        Storage::disk('local')->assertExists($order->payment_proof_path);

        Notification::assertSentOnDemandTimes(PaymentProofUploadedAdminNotification::class, 2);
        Notification::assertSentOnDemand(PaymentProofUploadedAdminNotification::class, function (PaymentProofUploadedAdminNotification $notification, array $channels, object $notifiable) use ($order): bool {
            $lines = $notification->toMail($notifiable)->introLines;

            return $notifiable->routes['mail'] === 'admin@example.com'
                && $notification->order->is($order)
                && in_array('Metode pembayaran: Transfer Bank BRI 123456 a.n. Toko', $lines, true)
                && in_array('2 x Dress Anak — Rp 100.000', $lines, true);
        });
        Notification::assertSentOnDemand(PaymentProofUploadedAdminNotification::class, function (PaymentProofUploadedAdminNotification $notification, array $channels, object $notifiable): bool {
            return $notifiable->routes['mail'] === 'management@example.com';
        });

        $this->actingAs($buyer)->get(route('admin.orders.payment-proof', $order))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.orders.payment-proof', $order))->assertOk();
        $this->actingAs($buyer)->get(route('orders.show', $order))->assertOk()->assertSee('menunggu pemeriksaan admin');
    }

    public function test_payment_proof_is_only_allowed_for_pending_transfer_orders(): void
    {
        Storage::fake('local');
        $buyer = User::factory()->create();
        $codOrder = $this->createOrder($buyer);
        $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQDJ/pLvAAAAAElFTkSuQmCC');

        $this->actingAs($buyer)
            ->post(route('orders.payment-proof.store', $codOrder), [
                'payment_proof' => UploadedFile::fake()->createWithContent('bukti.png', $image),
            ])
            ->assertSessionHasErrors('payment_proof');

        $transferOrder = $this->createOrder($buyer);
        $transferOrder->update(['payment_method' => Order::PAYMENT_TRANSFER, 'status' => Order::STATUS_CONFIRMED]);

        $this->post(route('orders.payment-proof.store', $transferOrder), [
            'payment_proof' => UploadedFile::fake()->createWithContent('bukti.png', $image),
        ])->assertSessionHasErrors('payment_proof');
    }

    private function createOrder(?User $user = null): Order
    {
        return Order::create([
            'user_id' => $user?->id,
            'customer_name' => 'Siti Pembeli',
            'customer_phone' => '081234567890',
            'customer_email' => 'siti@example.com',
            'customer_address' => 'Jalan Melati Nomor 12, Bandung',
            'payment_method' => Order::PAYMENT_COD,
            'subtotal' => 100000,
            'shipping_cost' => 0,
            'total' => 100000,
            'status' => Order::STATUS_PENDING,
        ]);
    }
}
