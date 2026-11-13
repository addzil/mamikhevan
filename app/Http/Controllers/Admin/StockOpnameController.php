<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockOpnameController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $today = CarbonImmutable::now('Asia/Jakarta')->startOfDay();
        $from = $validated['from'] ?? $today->startOfMonth()->format('Y-m-d');
        $to = $validated['to'] ?? $today->format('Y-m-d');
        $start = CarbonImmutable::parse($from, 'Asia/Jakarta')->startOfDay()->utc();
        $end = CarbonImmutable::parse($to, 'Asia/Jakarta')->endOfDay()->utc();
        $search = trim((string) ($validated['q'] ?? ''));

        $soldItems = fn (Builder $query): Builder => $query->whereHas('order', fn (Builder $orders): Builder => $orders
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->whereBetween('created_at', [$start, $end]));

        $products = Product::query()
            ->with('category')
            ->withSum(['orderItems as ordered_quantity' => $soldItems], 'quantity')
            ->withSum(['orderItems as ordered_value' => $soldItems], 'subtotal')
            ->when($search !== '', fn (Builder $query): Builder => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $lowStockVariants = Product::query()->get(['sizes', 'size_stocks', 'stock'])
            ->sum(fn (Product $product): int => $product->sizeList() === []
                ? (int) ($product->stock <= 5)
                : collect($product->sizeStockMap())->filter(fn (int $stock): bool => $stock <= 5)->count());

        $stats = [
            'products' => Product::query()->count(),
            'stock_units' => (int) Product::query()->sum('stock'),
            'low_stock' => $lowStockVariants,
            'ordered_units' => (int) $soldItems(OrderItem::query())->sum('quantity'),
        ];

        $adjustments = InventoryAdjustment::query()
            ->with('user:id,name')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.stock-opname.index', compact('products', 'adjustments', 'stats', 'search', 'from', 'to'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'counted_stock' => ['required_without:size_stocks', 'nullable', 'integer', 'min:0', 'max:1000000'],
            'sizes' => ['required_with:size_stocks', 'array'],
            'sizes.*' => ['required', 'string', 'max:20'],
            'size_stocks' => ['required_with:sizes', 'array'],
            'size_stocks.*' => ['required', 'integer', 'min:0', 'max:1000000'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $differences = DB::transaction(function () use ($request, $product, $validated): array {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            if ($lockedProduct->sizeList() === []) {
                $systemStock = $lockedProduct->stock;
                $countedStock = (int) $validated['counted_stock'];
                $difference = $countedStock - $systemStock;

                if ($difference !== 0) {
                    $lockedProduct->update(['stock' => $countedStock]);
                    $this->recordAdjustment($request, $lockedProduct, null, $systemStock, $countedStock, $difference, $validated['note'] ?? null);
                }

                return [$difference];
            }

            $currentSizes = $lockedProduct->sizeList();
            $submittedSizes = array_values($validated['sizes'] ?? []);
            $submittedStocks = array_values($validated['size_stocks'] ?? []);
            if ($submittedSizes !== $currentSizes || count($submittedStocks) !== count($currentSizes)) {
                abort(422, 'Daftar ukuran produk berubah. Muat ulang halaman opname lalu coba kembali.');
            }

            $nextStocks = [];
            $differences = [];
            foreach ($currentSizes as $index => $size) {
                $systemStock = (int) ($lockedProduct->size_stocks[$size] ?? 0);
                $countedStock = (int) $submittedStocks[$index];
                $difference = $countedStock - $systemStock;
                $nextStocks[$size] = $countedStock;
                $differences[] = $difference;

                if ($difference !== 0 || $lockedProduct->unallocated_stock > 0) {
                    $this->recordAdjustment($request, $lockedProduct, $size, $systemStock, $countedStock, $difference, $validated['note'] ?? null);
                }
            }

            $lockedProduct->replaceSizeStocks($nextStocks, clearUnallocated: true);

            return $differences;
        });

        if (collect($differences)->every(fn (int $difference): bool => $difference === 0) && $product->unallocated_stock === 0) {
            return back()->with('success', 'Stok fisik sudah sesuai dengan stok sistem.');
        }

        if ($product->sizeList() === []) {
            $difference = $differences[0] ?? 0;
            $change = $difference > 0
                ? 'bertambah '.number_format($difference, 0, ',', '.')
                : 'berkurang '.number_format(abs($difference), 0, ',', '.');

            return back()->with('success', 'Opname tersimpan. Stok '.$product->name.' '.$change.' unit.');
        }

        return back()->with('success', 'Opname stok per ukuran untuk '.$product->name.' berhasil disimpan.');
    }

    private function recordAdjustment(Request $request, Product $product, ?string $size, int $systemStock, int $countedStock, int $difference, ?string $note): void
    {
        InventoryAdjustment::query()->create([
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'product_name' => $product->name,
            'size' => $size,
            'system_stock' => $systemStock,
            'counted_stock' => $countedStock,
            'difference' => $difference,
            'note' => $note,
        ]);
    }
}
