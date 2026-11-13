<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use ZipArchive;

class AdminFinanceAndStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_report_summarizes_order_status_and_exports_excel(): void
    {
        $this->seed();
        Order::query()->delete();
        $administrator = User::query()->where('role', User::ROLE_ADMINISTRATOR)->firstOrFail();
        $today = now('Asia/Jakarta')->format('Y-m-d');
        $this->createOrder(Order::STATUS_COMPLETED, 120000);
        $this->createOrder(Order::STATUS_PENDING, 45000);
        $this->createOrder(Order::STATUS_CANCELLED, 30000);

        $this->actingAs($administrator)
            ->get(route('admin.finance.index', ['from' => $today, 'to' => $today]))
            ->assertOk()
            ->assertViewHas('summary', fn (array $summary): bool => $summary['completed_count'] === 1
                && $summary['completed_total'] === 120000
                && $summary['active_count'] === 1
                && $summary['active_total'] === 45000
                && $summary['cancelled_count'] === 1
                && $summary['cancelled_total'] === 30000);

        $export = $this->get(route('admin.finance.export', ['from' => $today, 'to' => $today]))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertDownload('laporan-keuangan-'.$today.'-sampai-'.$today.'.xlsx');

        $filePath = $export->baseResponse->getFile()->getPathname();
        $workbook = new ZipArchive;
        $this->assertTrue($workbook->open($filePath) === true);
        $this->assertNotFalse($workbook->getFromName('xl/workbook.xml'));
        $worksheet = $workbook->getFromName('xl/worksheets/sheet1.xml');
        $this->assertIsString($worksheet);
        $this->assertStringContainsString('Laporan Keuangan Mamikhevanshop', $worksheet);
        $this->assertStringContainsString('Pelanggan Laporan', $worksheet);
        $this->assertStringContainsString('<v>120000</v>', $worksheet);
        foreach (['[Content_Types].xml', '_rels/.rels', 'xl/workbook.xml', 'xl/_rels/workbook.xml.rels', 'xl/styles.xml', 'xl/worksheets/sheet1.xml'] as $entry) {
            $this->assertNotFalse(simplexml_load_string($workbook->getFromName($entry)));
        }
        $workbook->close();
        unlink($filePath);
    }

    public function test_operator_can_run_stock_opname_and_sales_count_excludes_cancelled_orders(): void
    {
        $this->seed();
        Order::query()->delete();
        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $product = Product::query()->firstOrFail();
        $product->update(['stock' => 10, 'sizes' => null, 'size_stocks' => null, 'unallocated_stock' => 0]);
        $today = now('Asia/Jakarta')->format('Y-m-d');

        $completed = $this->createOrder(Order::STATUS_COMPLETED, 20000);
        $completed->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 10000,
            'quantity' => 2,
            'subtotal' => 20000,
        ]);
        $cancelled = $this->createOrder(Order::STATUS_CANCELLED, 10000);
        $cancelled->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 10000,
            'quantity' => 1,
            'subtotal' => 10000,
        ]);

        $this->actingAs($operator)
            ->get(route('admin.stock-opname.index', ['from' => $today, 'to' => $today]))
            ->assertOk()
            ->assertViewHas('products', fn ($products): bool => (int) $products->firstWhere('id', $product->id)->ordered_quantity === 2);

        $this->post(route('admin.stock-opname.update', $product), [
            'counted_stock' => 8,
            'note' => 'Dua unit rusak saat pemeriksaan.',
            'from' => $today,
            'to' => $today,
        ])->assertRedirect();

        $this->assertSame(8, $product->fresh()->stock);
        $this->assertDatabaseHas('inventory_adjustments', [
            'product_id' => $product->id,
            'user_id' => $operator->id,
            'system_stock' => 10,
            'counted_stock' => 8,
            'difference' => -2,
            'note' => 'Dua unit rusak saat pemeriksaan.',
        ]);
        $this->assertDatabaseCount('inventory_adjustments', 1);
        $this->get(route('admin.finance.index'))->assertForbidden();
    }

    public function test_manager_can_view_finance_and_opname_pages(): void
    {
        $this->seed();
        $manager = User::factory()->create(['role' => User::ROLE_MANAGEMENT]);

        $this->actingAs($manager)->get(route('admin.finance.index'))->assertOk();
        $this->get(route('admin.stock-opname.index'))->assertOk();
    }

    public function test_stock_opname_allocates_legacy_stock_and_records_adjustment_per_size(): void
    {
        $this->seed();
        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $product = Product::query()->firstOrFail();
        $sizes = $product->sizeList();
        $product->update([
            'stock' => 12,
            'size_stocks' => array_fill_keys($sizes, 0),
            'unallocated_stock' => 12,
        ]);
        $physicalStocks = array_map(fn (int $index): int => $index + 1, array_keys($sizes));

        $payload = [
            'sizes' => array_values($sizes),
            'size_stocks' => $physicalStocks,
            'note' => 'Alokasi hasil hitung fisik.',
        ];

        $this->actingAs($operator)
            ->post(route('admin.stock-opname.update', $product), $payload)
            ->assertSessionHas('success');

        $updatedProduct = $product->fresh();
        $expectedStocks = array_combine($sizes, $physicalStocks);
        $this->assertSame($expectedStocks, $updatedProduct->sizeStockMap());
        $this->assertSame(array_sum($physicalStocks), $updatedProduct->stock);
        $this->assertSame(0, $updatedProduct->unallocated_stock);
        $this->assertDatabaseHas('inventory_adjustments', [
            'product_id' => $product->id,
            'size' => $sizes[0],
            'system_stock' => 0,
            'counted_stock' => 1,
            'difference' => 1,
        ]);
        $this->assertDatabaseCount('inventory_adjustments', count($sizes));
    }

    public function test_product_edit_does_not_allow_stock_changes_outside_stock_opname(): void
    {
        $this->seed();
        $administrator = User::query()->where('role', User::ROLE_ADMINISTRATOR)->firstOrFail();
        $product = Product::query()->firstOrFail();
        $systemStock = $product->stock;

        $this->actingAs($administrator)
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertDontSee('name="stock"', false)
            ->assertSee(route('admin.stock-opname.index'));

        $this->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'price' => $product->price,
            'cost_price' => $product->cost_price,
            'compare_price' => $product->compare_price,
            'stock' => $systemStock + 100,
            'gender' => $product->gender,
            'age_group' => $product->age_group,
            'material' => $product->material,
            'sizes' => $product->sizes,
            'size_guide_type_id' => $product->size_guide_type_id,
            'weight_grams' => $product->weight_grams,
            'category_id' => $product->category_id,
            'is_active' => $product->is_active,
            'is_featured' => $product->is_featured,
            'cod_available' => $product->cod_available,
            'free_shipping' => $product->free_shipping,
        ])->assertRedirect(route('admin.products.index'));

        $this->assertSame($systemStock, $product->fresh()->stock);
        $this->assertDatabaseCount('inventory_adjustments', 0);
    }

    public function test_cancelling_and_reactivating_an_order_adjusts_stock_only_once_and_in_aggregate(): void
    {
        $this->seed();
        $administrator = User::query()->where('role', User::ROLE_ADMINISTRATOR)->firstOrFail();
        $product = Product::query()->firstOrFail();
        $product->update(['stock' => 5, 'sizes' => null, 'size_stocks' => null, 'unallocated_stock' => 0]);
        $order = $this->createOrder(Order::STATUS_PENDING, 20000);
        $order->update(['customer_email' => 'customer@example.com']);
        Notification::fake();
        $order->items()->createMany([
            [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => 10000,
                'quantity' => 2,
                'subtotal' => 20000,
            ],
            [
                'product_id' => $product->id,
                'product_name' => 'Hadiah '.$product->name,
                'price' => 0,
                'quantity' => 1,
                'subtotal' => 0,
                'is_promo_gift' => true,
            ],
        ]);

        $this->actingAs($administrator)
            ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_CANCELLED])
            ->assertSessionHas('success');
        $this->assertSame(8, $product->fresh()->stock);

        $this->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_CANCELLED])
            ->assertSessionHas('success');
        $this->assertSame(8, $product->fresh()->stock);

        $this->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_PENDING])
            ->assertSessionHas('success');
        $this->assertSame(5, $product->fresh()->stock);
        Notification::assertSentOnDemandTimes(OrderCancelledNotification::class, 1);
        Notification::assertSentOnDemand(OrderStatusUpdatedNotification::class, function (OrderStatusUpdatedNotification $notification, array $channels, object $notifiable) use ($order): bool {
            return $notifiable->routes['mail'] === 'customer@example.com'
                && $notification->order->is($order)
                && $notification->status === Order::STATUS_PENDING;
        });

        $this->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_PENDING])
            ->assertSessionHas('success');
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_admin_cannot_complete_an_order_before_shipment_and_customer_receipt(): void
    {
        $this->seed();
        $administrator = User::query()->where('role', User::ROLE_ADMINISTRATOR)->firstOrFail();
        $order = $this->createOrder(Order::STATUS_PENDING, 20000);

        $this->actingAs($administrator)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertDontSee('<option value="completed"', false);

        $this->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_COMPLETED])
            ->assertSessionHas('error');

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
    }

    private function createOrder(string $status, int $total): Order
    {
        return Order::query()->create([
            'customer_name' => 'Pelanggan Laporan',
            'customer_phone' => '081234567890',
            'customer_address' => 'Alamat pengujian laporan',
            'payment_method' => Order::PAYMENT_TRANSFER,
            'subtotal' => $total,
            'total' => $total,
            'status' => $status,
        ]);
    }
}
