<?php

namespace Tests\Feature;

use App\Mail\ContactReplyMail;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\HomeBannerSetting;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\SizeGuideType;
use App\Models\User;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderPaymentNotification;
use App\Notifications\OrderShippedNotification;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = User::query()->where('is_admin', true)->firstOrFail();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_users_cannot_access_the_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_dashboard_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_category_pages_render(): void
    {
        $category = Category::query()->firstOrFail();

        $this->actingAs($this->admin)->get(route('admin.categories.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.categories.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.categories.edit', $category))->assertOk();
    }

    public function test_product_pages_render(): void
    {
        $product = Product::query()->firstOrFail();

        $this->actingAs($this->admin)->get(route('admin.products.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('Setelan')
            ->assertSee('Bebas ongkir untuk produk ini')
            ->assertViewHas('sizeGuideTypes', fn ($types) => $types->contains('slug', 'setelan'));
        $this->actingAs($this->admin)->get(route('admin.products.edit', $product))->assertOk();
    }

    public function test_size_guide_pages_render(): void
    {
        $sizeGuide = SizeGuideType::query()->firstOrFail();

        $this->actingAs($this->admin)->get(route('admin.size-guides.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.size-guides.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.size-guides.edit', $sizeGuide))->assertOk();
    }

    public function test_order_pages_render(): void
    {
        $order = Order::query()->firstOrFail();

        $this->actingAs($this->admin)->get(route('admin.orders.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.orders.show', $order))->assertOk();
    }

    public function test_admin_can_print_shipping_label_with_customer_courier_and_tracking_details(): void
    {
        $order = Order::query()->firstOrFail();
        $order->update([
            'customer_name' => 'Sari Ayu',
            'customer_phone' => '081234567803',
            'customer_address' => 'Jl. Pahlawan No. 45, Kepanjen',
            'shipping_courier' => Order::COURIER_JNE,
            'shipping_service' => 'REG',
            'shipping_quote_source' => 'komerce',
            'shipping_tracking_number' => 'JNE123456789',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.orders.shipping-label', $order))
            ->assertOk()
            ->assertSee('Label pengiriman')
            ->assertSee('Sari Ayu')
            ->assertSee('081234567803')
            ->assertSee('JNE123456789')
            ->assertSee('JNE REG')
            ->assertSee(config('shop.store.address'))
            ->assertSee('Cetak resi')
            ->assertViewHas('weightGrams', fn (int $weightGrams) => $weightGrams > 0);
    }

    public function test_non_admin_users_cannot_print_shipping_labels(): void
    {
        $order = Order::query()->firstOrFail();
        $user = User::factory()->create(['is_admin' => false]);

        $this->get(route('admin.orders.shipping-label', $order))
            ->assertRedirect(route('admin.login'));

        $this->actingAs($user)
            ->get(route('admin.orders.shipping-label', $order))
            ->assertForbidden();
    }

    public function test_admin_can_advance_an_order_without_changing_checkout_amount(): void
    {
        $order = Order::query()->firstOrFail();
        $order->update([
            'status' => Order::STATUS_PENDING,
            'customer_email' => 'buyer@example.com',
            'shipping_quote_source' => 'internal_estimate',
            'payment_method' => Order::PAYMENT_TRANSFER,
            'payment_unique_code' => 123,
            'subtotal' => 100000,
            'shipping_cost' => 20000,
            'total' => 120123,
        ]);
        Notification::fake();

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_CONFIRMED])
            ->assertSessionHas('success');
        $this->assertSame(Order::STATUS_CONFIRMED, $order->fresh()->status);
        $this->assertSame(120123, $order->fresh()->total);
        $this->assertSame(20000, $order->fresh()->shipping_cost);
        Notification::assertSentOnDemand(OrderStatusUpdatedNotification::class, function (OrderStatusUpdatedNotification $notification, array $channels, object $notifiable) use ($order): bool {
            return $notifiable->routes['mail'] === 'buyer@example.com'
                && $notification->order->is($order)
                && in_array('Admin telah mengonfirmasi pesanan '.$order->code.'. Pesanan akan segera diproses.', $notification->toMail($notifiable)->introLines, true);
        });

        $this->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_PROCESSING])
            ->assertSessionHas('success');
        Notification::assertSentOnDemandTimes(OrderStatusUpdatedNotification::class, 2);

        $this->patch('/admin/orders/'.$order->id.'/shipping', ['shipping_cost' => 30000])
            ->assertNotFound();
        $this->assertSame(120123, $order->fresh()->total);
    }

    public function test_customer_receives_tracking_number_when_admin_marks_order_shipped(): void
    {
        $order = Order::query()->firstOrFail();
        $order->update([
            'status' => Order::STATUS_PROCESSING,
            'shipping_quote_source' => 'internal_rate',
            'shipping_tracking_number' => null,
            'customer_email' => 'buyer@example.com',
        ]);
        Notification::fake();

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_SHIPPED])
            ->assertSessionHas('error');
        Notification::assertNothingSent();

        $this->patch(route('admin.orders.tracking', $order), ['tracking_number' => 'JNE123456'])
            ->assertSessionHas('success');
        $this->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_SHIPPED])
            ->assertSessionHas('success');

        Notification::assertSentOnDemand(OrderShippedNotification::class, function (OrderShippedNotification $notification, array $channels, object $notifiable) use ($order) {
            return $notifiable->routes['mail'] === 'buyer@example.com'
                && $notification->order->is($order)
                && in_array('Nomor resi: JNE123456', $notification->toMail($notifiable)->introLines, true);
        });
    }

    public function test_customer_receives_email_when_admin_cancels_order(): void
    {
        $order = Order::query()->firstOrFail();
        $item = $order->items()->firstOrFail();
        $order->update([
            'status' => Order::STATUS_PENDING,
            'customer_email' => 'buyer@example.com',
            'payment_method' => Order::PAYMENT_TRANSFER,
            'payment_destination' => ['type' => 'bank', 'bank' => 'BRI', 'account_number' => '123456', 'account_name' => 'Toko'],
        ]);
        Notification::fake();

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_CANCELLED])
            ->assertSessionHas('success');

        Notification::assertSentOnDemand(OrderCancelledNotification::class, function (OrderCancelledNotification $notification, array $channels, object $notifiable) use ($order, $item): bool {
            $lines = $notification->toMail($notifiable)->introLines;

            return $notifiable->routes['mail'] === 'buyer@example.com'
                && $notification->order->is($order)
                && in_array('Pesanan '.$order->code.' telah dibatalkan oleh admin.', $lines, true)
                && in_array('Metode pembayaran: Transfer Bank', $lines, true)
                && str_contains(implode(' ', $lines), $item->product_name)
                && in_array('Jika kamu sudah melakukan transfer, silakan hubungi admin untuk bantuan pengembalian dana.', $lines, true);
        });

        $this->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_CANCELLED]);
        Notification::assertSentOnDemandTimes(OrderCancelledNotification::class, 1);
    }

    public function test_customer_is_notified_when_tracking_number_changes_after_shipping(): void
    {
        $order = Order::query()->firstOrFail();
        $order->update([
            'status' => Order::STATUS_SHIPPED,
            'shipping_tracking_number' => 'JNE-OLD',
            'customer_email' => 'buyer@example.com',
        ]);
        Notification::fake();

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.tracking', $order), ['tracking_number' => 'JNE-NEW'])
            ->assertSessionHas('success');

        Notification::assertSentOnDemand(OrderShippedNotification::class, function (OrderShippedNotification $notification, array $channels, object $notifiable) use ($order): bool {
            return $notifiable->routes['mail'] === 'buyer@example.com'
                && $notification->order->is($order)
                && in_array('Nomor resi: JNE-NEW', $notification->toMail($notifiable)->introLines, true);
        });
    }

    public function test_admin_cannot_cancel_an_order_after_it_has_shipped(): void
    {
        $order = Order::query()->firstOrFail();
        $order->update(['status' => Order::STATUS_SHIPPED]);

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_CANCELLED])
            ->assertSessionHas('error');

        $this->assertSame(Order::STATUS_SHIPPED, $order->fresh()->status);
    }

    public function test_cod_order_email_shows_checkout_amount_without_transfer_request(): void
    {
        $order = Order::query()->firstOrFail();
        $order->update([
            'status' => Order::STATUS_PENDING,
            'shipping_quote_source' => 'internal_estimate',
            'payment_method' => Order::PAYMENT_COD,
            'payment_unique_code' => null,
            'subtotal' => 70000,
            'total' => 95000,
            'customer_email' => 'buyer@example.com',
        ]);
        Storage::fake('public');
        Storage::disk('public')->put('payment/qris.png', 'qris-image');
        PaymentSetting::query()->create([
            'bank_accounts' => [
                ['bank' => 'BCA', 'account_name' => 'Mamikhevan', 'account_number' => '1234567890', 'is_active' => true],
            ],
            'qris_enabled' => true,
            'qris_image' => 'payment/qris.png',
        ]);
        $message = (new OrderPaymentNotification($order))->toMail(new \stdClass);
        $lines = $message->introLines;

        $this->assertContains('Total pembayaran COD: Rp 95.000', $lines);
        $this->assertNotContains('Total yang perlu ditransfer: Rp 95.000', $lines);
        $this->assertFalse(str_contains(implode(' ', $lines), 'Rekening BCA:'));
        $this->assertSame([], $message->rawAttachments);
    }

    public function test_message_pages_render_and_mark_as_read(): void
    {
        $message = ContactMessage::query()->where('is_read', false)->firstOrFail();

        $this->actingAs($this->admin)->get(route('admin.messages.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.messages.show', $message))->assertOk();

        $this->assertTrue($message->fresh()->is_read);
    }

    public function test_admin_can_reply_to_a_contact_message_via_email(): void
    {
        Mail::fake();
        $message = ContactMessage::query()->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.messages.reply', $message), [
                'reply_subject' => 'Re: '.$message->subject,
                'reply_body' => 'Terima kasih sudah menghubungi kami.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(ContactReplyMail::class, function (ContactReplyMail $mail) use ($message): bool {
            return $mail->hasTo($message->email)
                && $mail->hasFrom(config('mail.from.address'))
                && $mail->replySubject === 'Re: '.$message->subject
                && $mail->replyBody === 'Terima kasih sudah menghubungi kami.';
        });

        $this->assertNotNull($message->fresh()->replied_at);
    }

    public function test_non_admin_cannot_reply_to_a_contact_message(): void
    {
        Mail::fake();
        $message = ContactMessage::query()->firstOrFail();

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->post(route('admin.messages.reply', $message), [
                'reply_subject' => 'Re: '.$message->subject,
                'reply_body' => 'Halo kak.',
            ])
            ->assertForbidden();

        Mail::assertNothingSent();
        $this->assertNull($message->fresh()->replied_at);
    }

    public function test_reply_requires_a_subject_and_body(): void
    {
        Mail::fake();
        $message = ContactMessage::query()->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.messages.reply', $message), [])
            ->assertSessionHasErrors(['reply_subject', 'reply_body']);

        Mail::assertNothingSent();
        $this->assertNull($message->fresh()->replied_at);
    }

    public function test_contact_reply_mail_contains_the_reply_and_original_message(): void
    {
        $message = ContactMessage::query()->firstOrFail();

        (new ContactReplyMail($message, 'Re: '.$message->subject, "Halo kak,\nTerima kasih sudah menghubungi kami."))
            ->assertFrom(config('mail.from.address'))
            ->assertHasReplyTo(config('mail.from.address'))
            ->assertHasSubject('Re: '.$message->subject)
            ->assertSeeInHtml('Terima kasih sudah menghubungi kami.')
            ->assertSeeInHtml($message->subject)
            ->assertSeeInHtml($message->message);
    }

    public function test_admin_can_create_a_product_with_multiple_images(): void
    {
        Storage::fake('public');

        $image = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQDJ/pLvAAAAAElFTkSuQmCC'
        );

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), [
                'name' => 'Produk Galeri',
                'description' => 'Deskripsi produk galeri yang cukup panjang untuk lolos validasi.',
                'price' => 50000,
                'weight_grams' => 750,
                'stock' => 10,
                'gender' => 'uniseks',
                'images' => [
                    UploadedFile::fake()->createWithContent('utama.png', $image),
                    UploadedFile::fake()->createWithContent('tambahan.png', $image),
                ],
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Produk Galeri')->firstOrFail();

        $this->assertNotNull($product->image);
        $this->assertSame(750, $product->weight_grams);
        $this->assertCount(1, $product->images);
        $this->assertCount(2, $product->galleryImageUrls());
    }

    public function test_admin_can_adjust_product_image_fit_without_changing_the_uploaded_file(): void
    {
        $product = Product::query()->create([
            'name' => 'Produk Foto Tinggi',
            'description' => 'Foto produk ini perlu tampil utuh dalam bingkai persegi.',
            'price' => 50000,
            'stock' => 1,
            'gender' => 'uniseks',
            'image' => 'images/products/dress.svg',
            'is_active' => true,
        ]);

        $this->assertSame('contain', $product->refresh()->image_fit);
        $this->get(route('products.show', $product))->assertOk()->assertSee('object-contain transition duration-500', false);

        $this->actingAs($this->admin)
            ->patch(route('admin.products.update', $product), [
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'gender' => $product->gender,
                'image_fit' => 'cover',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertSame('cover', $product->refresh()->image_fit);
        $this->assertSame('images/products/dress.svg', $product->image);
        $this->get(route('products.show', $product))->assertOk()->assertSee('object-cover hover:scale-[1.02]', false);
    }

    public function test_admin_cannot_save_an_unknown_product_image_fit(): void
    {
        $product = Product::query()->create([
            'name' => 'Produk Foto Invalid',
            'description' => 'Produk untuk memeriksa pilihan tampilan foto yang tidak dikenal.',
            'price' => 50000,
            'stock' => 1,
            'gender' => 'uniseks',
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.products.update', $product), [
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'gender' => $product->gender,
                'image_fit' => 'stretch',
            ])
            ->assertSessionHasErrors('image_fit');

        $this->assertSame('contain', $product->refresh()->image_fit);
    }

    public function test_admin_can_save_a_banner_with_only_one_product(): void
    {
        $product = Product::query()->active()->firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.home-banner.update'), [
                'eyebrow' => 'Pilihan baru',
                'headline' => 'Pilihan terbaik untuk anak',
                'description' => 'Lihat koleksi terbaru.',
                'design' => 'collage',
                'background' => 'warm',
                'is_active' => '1',
                'cards' => [
                    ['product_id' => '', 'label' => ''],
                    ['product_id' => '', 'label' => ''],
                    ['product_id' => $product->id, 'label' => 'Pilihan khusus'],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame([['label' => 'Pilihan khusus', 'product_id' => $product->id]], HomeBannerSetting::current()->cards);
        $this->get(route('home'))->assertSee('Pilihan khusus');
        $this->get(route('admin.home-banner.edit'))->assertSee('cards[2][product_id]', false);
    }

    public function test_admin_must_select_at_least_one_banner_product(): void
    {
        $banner = HomeBannerSetting::current();
        $originalCards = $banner->cards;

        $this->actingAs($this->admin)
            ->put(route('admin.home-banner.update'), [
                'eyebrow' => 'Pilihan baru',
                'headline' => 'Pilihan terbaik untuk anak',
                'design' => 'collage',
                'background' => 'warm',
                'is_active' => '1',
                'cards' => [
                    ['product_id' => '', 'label' => ''],
                    ['product_id' => '', 'label' => ''],
                    ['product_id' => '', 'label' => ''],
                ],
            ])
            ->assertSessionHasErrors('cards');

        $this->assertSame($originalCards, $banner->refresh()->cards);
    }
}
