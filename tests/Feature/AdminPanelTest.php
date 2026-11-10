<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\SizeGuideType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
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
        $this->actingAs($this->admin)->get(route('admin.products.create'))->assertOk();
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

    public function test_message_pages_render_and_mark_as_read(): void
    {
        $message = ContactMessage::query()->where('is_read', false)->firstOrFail();

        $this->actingAs($this->admin)->get(route('admin.messages.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.messages.show', $message))->assertOk();

        $this->assertTrue($message->fresh()->is_read);
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
        $this->assertCount(1, $product->images);
        $this->assertCount(2, $product->galleryImageUrls());
    }
}
