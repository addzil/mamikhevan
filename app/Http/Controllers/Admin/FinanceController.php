<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\FinanceReportXlsxExporter;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        [$filters, $start, $end] = $this->validatedPeriod($request);
        $periodOrders = $this->ordersForPeriod($filters, $start, $end);
        $summary = $this->summaryFor($periodOrders);
        $completed = (clone $periodOrders)->where('status', Order::STATUS_COMPLETED);

        $payments = [];

        foreach ((clone $completed)->cursor() as $order) {
            $label = $order->paymentLabel();
            $payments[$label] ??= ['label' => $label, 'count' => 0, 'total' => 0];
            $payments[$label]['count']++;
            $payments[$label]['total'] += $order->total;
        }

        $paymentBreakdown = collect(array_values($payments));

        $orders = (clone $periodOrders)
            ->withCount('items')
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.finance.index', compact('filters', 'summary', 'paymentBreakdown', 'orders'));
    }

    public function export(Request $request, FinanceReportXlsxExporter $exporter): BinaryFileResponse
    {
        [$filters, $start, $end] = $this->validatedPeriod($request);
        $orders = $this->ordersForPeriod($filters, $start, $end)->withCount('items')->orderBy('created_at');
        $summary = $this->summaryFor($orders);
        $filePath = $exporter->create($filters, [
            'statuses' => $summary['status_rows'],
            'reconciliation' => $summary['reconciliation_rows'],
        ], $orders->cursor());

        return response()->download(
            $filePath,
            'laporan-keuangan-'.$filters['from'].'-sampai-'.$filters['to'].'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    /**
     * @return array{0: array{from: string, to: string, status: string}, 1: CarbonImmutable, 2: CarbonImmutable}
     */
    private function validatedPeriod(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'status' => ['nullable', Rule::in(Order::STATUSES)],
        ]);

        $today = CarbonImmutable::now('Asia/Jakarta')->startOfDay();
        $from = $validated['from'] ?? $today->startOfMonth()->format('Y-m-d');
        $to = $validated['to'] ?? $today->format('Y-m-d');
        $filters = [
            'from' => $from,
            'to' => $to,
            'status' => $validated['status'] ?? '',
        ];

        return [
            $filters,
            CarbonImmutable::parse($from, 'Asia/Jakarta')->startOfDay()->utc(),
            CarbonImmutable::parse($to, 'Asia/Jakarta')->endOfDay()->utc(),
        ];
    }

    /**
     * @param  array{from: string, to: string, status: string}  $filters
     */
    private function ordersForPeriod(array $filters, CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        return Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->when($filters['status'] !== '', fn (Builder $query): Builder => $query->where('status', $filters['status']));
    }

    /**
     * @return array{
     *     completed_count: int,
     *     completed_total: int,
     *     active_count: int,
     *     active_total: int,
     *     cancelled_count: int,
     *     cancelled_total: int,
     *     completed_discounts: int,
     *     completed_product_sales: int,
     *     completed_shipping_net: int,
     *     completed_unique_codes: int,
     *     status_rows: list<array{0: string, 1: int, 2: int}>,
     *     reconciliation_rows: list<array{0: string, 1: int}>
     * }
     */
    private function summaryFor(Builder $periodOrders): array
    {
        $completed = (clone $periodOrders)->where('status', Order::STATUS_COMPLETED);
        $active = (clone $periodOrders)->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED]);
        $cancelled = (clone $periodOrders)->where('status', Order::STATUS_CANCELLED);
        $completedCount = (int) (clone $completed)->count();
        $completedTotal = (int) (clone $completed)->sum('total');
        $completedProductSales = (int) (clone $completed)->sum('subtotal')
            - (int) (clone $completed)->sum('promotion_discount');
        $completedShippingNet = (int) (clone $completed)->sum('shipping_cost')
            - (int) (clone $completed)->sum('shipping_discount');
        $completedUniqueCodes = (int) (clone $completed)->sum('payment_unique_code');
        $activeCount = (int) (clone $active)->count();
        $activeTotal = (int) (clone $active)->sum('total');
        $cancelledCount = (int) (clone $cancelled)->count();
        $cancelledTotal = (int) (clone $cancelled)->sum('total');
        $completedDiscounts = (int) (clone $completed)->sum('promotion_discount')
            + (int) (clone $completed)->sum('shipping_discount');

        return [
            'completed_count' => $completedCount,
            'completed_total' => $completedTotal,
            'active_count' => $activeCount,
            'active_total' => $activeTotal,
            'cancelled_count' => $cancelledCount,
            'cancelled_total' => $cancelledTotal,
            'completed_discounts' => $completedDiscounts,
            'completed_product_sales' => $completedProductSales,
            'completed_shipping_net' => $completedShippingNet,
            'completed_unique_codes' => $completedUniqueCodes,
            'status_rows' => [
                ['Pesanan selesai', $completedCount, $completedTotal],
                ['Dalam proses', $activeCount, $activeTotal],
                ['Dibatalkan', $cancelledCount, $cancelledTotal],
            ],
            'reconciliation_rows' => [
                ['Penjualan produk bersih', $completedProductSales],
                ['Ongkir bersih', $completedShippingNet],
                ['Kode unik transfer', $completedUniqueCodes],
                ['Total transaksi selesai', $completedTotal],
            ],
        ];
    }
}
