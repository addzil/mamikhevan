<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->with(['items.product.category'])
            ->get();

        $allItems = $orders->flatMap->items;
        $productRevenue = (int) $allItems->sum('subtotal');
        $totalCost = (int) $allItems->sum(
            fn (OrderItem $item) => (int) ($item->product?->cost_price ?? 0) * (int) $item->quantity
        );
        $totalMargin = $productRevenue - $totalCost;

        $stats = [
            'revenue' => (int) $orders->sum('total'),
            'orders' => Order::query()->count(),
            'pending_orders' => Order::query()->where('status', Order::STATUS_PENDING)->count(),
            'products' => Product::query()->count(),
            'customers' => Order::query()->distinct('customer_phone')->count('customer_phone'),
            'unread_messages' => ContactMessage::query()->where('is_read', false)->count(),
            'margin' => $totalMargin,
            'margin_percent' => $productRevenue > 0 ? round(($totalMargin / $productRevenue) * 100, 1) : 0,
        ];

        $recentOrders = Order::query()->latest()->take(6)->get();

        $lowStockProducts = Product::query()
            ->active()
            ->where('stock', '<=', 5)
            ->with('category')
            ->orderBy('stock')
            ->take(5)
            ->get();

        $topCategories = Category::query()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->orderByDesc('products_count')
            ->take(5)
            ->get();

        $bestSellingCategories = $allItems
            ->groupBy(fn (OrderItem $item) => $item->product?->category?->name ?? 'Tanpa kategori')
            ->map(fn (Collection $items, string $name) => [
                'name' => $name,
                'quantity' => (int) $items->sum('quantity'),
                'revenue' => (int) $items->sum('subtotal'),
                'margin' => (int) $items->sum(
                    fn (OrderItem $item) => $item->subtotal - ((int) ($item->product?->cost_price ?? 0) * (int) $item->quantity)
                ),
            ])
            ->sortByDesc('quantity')
            ->values();

        $monthlySales = $this->monthlySales($orders, 12);
        $yearlySales = $this->yearlySales($orders, 5);
        $forecast = $this->categoryForecast($orders, $bestSellingCategories->take(3));

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
            $date = now()->startOfMonth()->subMonths($i);
            $monthOrders = $orders->filter(
                fn (Order $order) => $order->created_at->format('Y-m') === $date->format('Y-m')
            );
            $items = $monthOrders->flatMap->items;

            $monthProductRevenue = (int) $items->sum('subtotal');
            $monthCost = (int) $items->sum(
                fn (OrderItem $item) => (int) ($item->product?->cost_price ?? 0) * (int) $item->quantity
            );

            $labels[] = $date->locale('id')->translatedFormat('M Y');
            $revenue[] = (int) $monthOrders->sum('total');
            $productRevenue[] = $monthProductRevenue;
            $cost[] = $monthCost;
            $margin[] = $monthProductRevenue - $monthCost;
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
            $year = now()->subYears($i)->year;
            $yearOrders = $orders->filter(fn (Order $order) => $order->created_at->year === $year);

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
            $historyLabels[] = now()->startOfMonth()->subMonths($i)->locale('id')->translatedFormat('M');
        }

        $futureLabels = [];
        for ($i = 1; $i <= $futureMonths; $i++) {
            $futureLabels[] = now()->startOfMonth()->addMonths($i)->locale('id')->translatedFormat('M Y');
        }

        $datasets = [];

        foreach ($topCategories as $category) {
            $history = [];

            for ($i = $historyMonths - 1; $i >= 0; $i--) {
                $date = now()->startOfMonth()->subMonths($i);
                $quantity = $orders
                    ->filter(fn (Order $order) => $order->created_at->format('Y-m') === $date->format('Y-m'))
                    ->flatMap->items
                    ->filter(
                        fn (OrderItem $item) => ($item->product?->category?->name ?? 'Tanpa kategori') === $category['name']
                    )
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
