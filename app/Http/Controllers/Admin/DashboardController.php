<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasRole(User::ROLE_OPERATOR)) {
            return redirect()->route('admin.products.index');
        }

        $activeOrders = Order::query()->where('status', '!=', Order::STATUS_CANCELLED);
        $sales = [
            'product_revenue' => 0,
            'cost' => 0,
            'margin' => 0,
            'categories' => collect(),
        ];

        (clone $activeOrders)->with(['items.product.category'])->chunkById(200, function (Collection $orderBatch) use (&$sales): void {
            $batchSales = $this->salesBreakdown($orderBatch);
            $sales['product_revenue'] += $batchSales['product_revenue'];
            $sales['cost'] += $batchSales['cost'];

            foreach ($batchSales['categories'] as $category) {
                $current = $sales['categories']->get($category['name'], [
                    'name' => $category['name'],
                    'quantity' => 0,
                    'revenue' => 0,
                    'margin' => 0,
                ]);
                $current['quantity'] += $category['quantity'];
                $current['revenue'] += $category['revenue'];
                $current['margin'] += $category['margin'];
                $sales['categories']->put($category['name'], $current);
            }
        });

        $sales['margin'] = $sales['product_revenue'] - $sales['cost'];
        $productRevenue = $sales['product_revenue'];
        $totalCost = $sales['cost'];
        $totalMargin = $sales['margin'];

        $stats = [
            'revenue' => (int) (clone $activeOrders)->sum('total'),
            'orders' => (int) (clone $activeOrders)->count(),
            'pending_orders' => Order::query()->where('status', Order::STATUS_PENDING)->count(),
            'products' => Product::query()->active()->count(),
            'customers' => Order::query()->where('status', '!=', Order::STATUS_CANCELLED)->distinct('customer_phone')->count('customer_phone'),
            'unread_messages' => ContactMessage::query()->where('is_read', false)->count(),
            'margin' => $totalMargin,
            'margin_percent' => $productRevenue > 0 ? round(($totalMargin / $productRevenue) * 100, 1) : 0,
            'product_revenue' => $productRevenue,
            'cost' => $totalCost,
        ];

        $recentOrders = Order::query()->latest()->take(6)->get();

        $lowStockProducts = Product::query()
            ->active()
            ->with('category')
            ->get()
            ->map(function (Product $product): Product {
                $product->setAttribute('low_stock_sizes', collect($product->sizeStockMap())
                    ->filter(fn (int $stock): bool => $stock <= 5));

                return $product;
            })
            ->filter(fn (Product $product): bool => $product->sizeList() === []
                ? $product->stock <= 5
                : $product->low_stock_sizes->isNotEmpty())
            ->sortBy(fn (Product $product): int => $product->sizeList() === []
                ? $product->stock
                : (int) $product->low_stock_sizes->min())
            ->take(5)
            ->values();

        $topCategories = Category::query()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->orderByDesc('products_count')
            ->take(5)
            ->get();

        $bestSellingCategories = $sales['categories']
            ->sortByDesc('quantity')
            ->values();

        $chartRelations = ['items.product.category'];
        $monthRangeStart = now(Promotion::TIMEZONE)->startOfMonth()->subMonths(11)->utc();
        $yearRangeStart = now(Promotion::TIMEZONE)->startOfYear()->subYears(4)->utc();
        $forecastRangeStart = now(Promotion::TIMEZONE)->startOfMonth()->subMonths(5)->utc();
        $monthlyOrders = (clone $activeOrders)
            ->where('created_at', '>=', $monthRangeStart)
            ->with($chartRelations)
            ->get();
        $yearlyOrders = (clone $activeOrders)
            ->where('created_at', '>=', $yearRangeStart)
            ->with($chartRelations)
            ->get();
        $forecastOrders = (clone $activeOrders)
            ->where('created_at', '>=', $forecastRangeStart)
            ->with($chartRelations)
            ->get();

        $monthlySales = $this->monthlySales($monthlyOrders, 12);
        $yearlySales = $this->yearlySales($yearlyOrders, 5);
        $forecast = $this->categoryForecast($forecastOrders, $bestSellingCategories->take(3));

        $charts = [
            'monthly' => $monthlySales,
            'yearly' => $yearlySales,
            'categories' => [
                'labels' => $bestSellingCategories->take(6)->pluck('name')->all(),
                'quantity' => $bestSellingCategories->take(6)->pluck('quantity')->all(),
                'revenue' => $bestSellingCategories->take(6)->pluck('revenue')->all(),
            ],
            'margin' => [
                'labels' => $monthlySales['labels'],
                'revenue' => $monthlySales['product_revenue'],
                'cost' => $monthlySales['cost'],
                'margin' => $monthlySales['margin'],
            ],
            'forecast' => $forecast,
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recentOrders',
            'lowStockProducts',
            'topCategories',
            'bestSellingCategories',
            'charts'
        ));
    }

    private function monthlySales(Collection $orders, int $months): array
    {
        $labels = [];
        $revenue = [];
        $productRevenue = [];
        $cost = [];
        $margin = [];
        $orderCounts = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now(Promotion::TIMEZONE)->startOfMonth()->subMonths($i);
            $monthOrders = $orders->filter(
                fn (Order $order) => $order->created_at->timezone(Promotion::TIMEZONE)->format('Y-m') === $date->format('Y-m')
            );
            $monthSales = $this->salesBreakdown($monthOrders);

            $labels[] = $date->locale('id')->translatedFormat('M Y');
            $revenue[] = (int) $monthOrders->sum('total');
            $productRevenue[] = $monthSales['product_revenue'];
            $cost[] = $monthSales['cost'];
            $margin[] = $monthSales['margin'];
            $orderCounts[] = $monthOrders->count();
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'product_revenue' => $productRevenue,
            'cost' => $cost,
            'margin' => $margin,
            'orders' => $orderCounts,
        ];
    }

    private function yearlySales(Collection $orders, int $years): array
    {
        $labels = [];
        $revenue = [];
        $orderCounts = [];

        for ($i = $years - 1; $i >= 0; $i--) {
            $year = now(Promotion::TIMEZONE)->subYears($i)->year;
            $yearOrders = $orders->filter(fn (Order $order) => $order->created_at->timezone(Promotion::TIMEZONE)->year === $year);

            $labels[] = (string) $year;
            $revenue[] = (int) $yearOrders->sum('total');
            $orderCounts[] = $yearOrders->count();
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'orders' => $orderCounts,
        ];
    }

    private function categoryForecast(Collection $orders, Collection $topCategories, int $historyMonths = 6, int $futureMonths = 3): array
    {
        $historyLabels = [];
        for ($i = $historyMonths - 1; $i >= 0; $i--) {
            $historyLabels[] = now(Promotion::TIMEZONE)->startOfMonth()->subMonths($i)->locale('id')->translatedFormat('M');
        }

        $futureLabels = [];
        for ($i = 1; $i <= $futureMonths; $i++) {
            $futureLabels[] = now(Promotion::TIMEZONE)->startOfMonth()->addMonths($i)->locale('id')->translatedFormat('M Y');
        }

        $datasets = [];

        foreach ($topCategories as $category) {
            $history = [];

            for ($i = $historyMonths - 1; $i >= 0; $i--) {
                $date = now(Promotion::TIMEZONE)->startOfMonth()->subMonths($i);
                $quantity = $orders
                    ->filter(fn (Order $order) => $order->created_at->timezone(Promotion::TIMEZONE)->format('Y-m') === $date->format('Y-m'))
                    ->flatMap->items
                    ->filter(fn (OrderItem $item) => ! $item->is_promo_gift
                        && ($item->category_name ?? $item->product?->category?->name ?? 'Tanpa kategori') === $category['name'])
                    ->sum('quantity');

                $history[] = (int) $quantity;
            }

            $forecast = $this->projectNextValues($history, $futureMonths);

            $datasets[] = [
                'label' => $category['name'],
                'data' => array_merge($history, $forecast),
                'historyCount' => $historyMonths,
            ];
        }

        return [
            'labels' => array_merge($historyLabels, $futureLabels),
            'datasets' => $datasets,
        ];
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return array{product_revenue: int, cost: int, margin: int, categories: Collection<int, array{name: string, quantity: int, revenue: int, margin: int}>}
     */
    private function salesBreakdown(Collection $orders): array
    {
        $productRevenue = 0;
        $totalCost = 0;
        $categories = collect();

        foreach ($orders as $order) {
            $items = $order->items;
            $saleItems = $items->filter(fn (OrderItem $item): bool => ! $item->is_promo_gift)->values();
            $grossProductSubtotal = (int) $saleItems->sum('subtotal');
            $discountToAllocate = min((int) $order->promotion_discount, $grossProductSubtotal);
            $discountRemaining = $discountToAllocate;
            $discountedRevenue = 0;
            $lastSaleItem = $saleItems->last();

            foreach ($items as $item) {
                $unitCost = (int) ($item->cost_price ?? $item->product?->cost_price ?? 0);
                $itemCost = $unitCost * (int) $item->quantity;
                $totalCost += $itemCost;

                $itemRevenue = 0;

                if (! $item->is_promo_gift && $grossProductSubtotal > 0) {
                    $isLastSaleItem = $lastSaleItem?->is($item) ?? false;
                    $allocatedDiscount = $isLastSaleItem
                        ? $discountRemaining
                        : intdiv($discountToAllocate * (int) $item->subtotal, $grossProductSubtotal);
                    $discountRemaining -= $allocatedDiscount;
                    $itemRevenue = max(0, (int) $item->subtotal - $allocatedDiscount);
                    $discountedRevenue += $itemRevenue;
                }

                $name = $item->category_name ?? $item->product?->category?->name ?? 'Tanpa kategori';
                $category = $categories->get($name, [
                    'name' => $name,
                    'quantity' => 0,
                    'revenue' => 0,
                    'margin' => 0,
                ]);
                $category['quantity'] += $item->is_promo_gift ? 0 : (int) $item->quantity;
                $category['revenue'] += $itemRevenue;
                $category['margin'] += $itemRevenue - $itemCost;
                $categories->put($name, $category);
            }

            $productRevenue += $discountedRevenue;
        }

        return [
            'product_revenue' => $productRevenue,
            'cost' => $totalCost,
            'margin' => $productRevenue - $totalCost,
            'categories' => $categories->values(),
        ];
    }

    private function projectNextValues(array $history, int $futureMonths): array
    {
        $count = count($history);

        if ($count < 2) {
            return array_fill(0, $futureMonths, max(0, (int) ($history[0] ?? 0)));
        }

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($history as $x => $y) {
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        $denominator = ($count * $sumX2) - ($sumX * $sumX);

        if ($denominator === 0) {
            $average = (int) round($sumY / $count);

            return array_fill(0, $futureMonths, max(0, $average));
        }

        $slope = (($count * $sumXY) - ($sumX * $sumY)) / $denominator;
        $intercept = ($sumY - ($slope * $sumX)) / $count;

        $forecast = [];

        for ($i = 1; $i <= $futureMonths; $i++) {
            $x = $count - 1 + $i;
            $forecast[] = max(0, (int) round(($slope * $x) + $intercept));
        }

        return $forecast;
    }
}
