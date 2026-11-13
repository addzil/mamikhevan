<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderShippedNotification;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $status = (string) $request->query('status');

        $orders = Order::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', '%'.$search.'%')
                        ->orWhere('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('customer_phone', 'like', '%'.$search.'%');
                });
            })
            ->when(in_array($status, Order::STATUSES, true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'search', 'status'));
    }

    public function show(Order $order): View
    {
        $order->load('items.product');

        return view('admin.orders.show', compact('order'));
    }

    public function paymentProof(Order $order): BinaryFileResponse
    {
        abort_if(blank($order->payment_proof_path), 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($order->payment_proof_path), 404);

        return response()->file($disk->path($order->payment_proof_path), [
            'Content-Type' => $disk->mimeType($order->payment_proof_path) ?? 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function shippingLabel(Order $order): View
    {
        $order->load('items.product');

        $weightGrams = $order->estimatedWeightGrams();

        return view('admin.orders.shipping-label', compact('order', 'weightGrams'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
        ]);

        $newStatus = $validated['status'];
        $transition = DB::transaction(function () use ($order, $newStatus): array {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($newStatus === $lockedOrder->status) {
                return ['error' => null, 'changed' => false];
            }

            if (! in_array($newStatus, $lockedOrder->adminNextStatuses(), true)) {
                return ['error' => 'Perubahan status tidak sesuai dengan urutan proses pesanan.', 'changed' => false];
            }

            if ($newStatus === Order::STATUS_SHIPPED && blank($lockedOrder->shipping_tracking_number)) {
                return ['error' => 'Simpan nomor resi terlebih dahulu sebelum mengubah status menjadi Dikirim.', 'changed' => false];
            }

            $lockedOrder->load('items');

            if ($newStatus === Order::STATUS_CANCELLED) {
                $this->restoreInventory($lockedOrder);
                $lockedOrder->update([
                    'status' => $newStatus,
                    'shipping_tracking_number' => null,
                ]);
            } elseif ($lockedOrder->status === Order::STATUS_CANCELLED) {
                $inventoryError = $this->reserveInventory($lockedOrder);

                if ($inventoryError !== null) {
                    return ['error' => $inventoryError, 'changed' => false];
                }

                $lockedOrder->update([
                    'status' => $newStatus,
                    'shipping_tracking_number' => null,
                ]);
            } else {
                $lockedOrder->update(['status' => $newStatus]);
            }

            return ['error' => null, 'changed' => true];
        });

        if ($transition['error'] !== null) {
            return back()->with('error', $transition['error']);
        }

        if (! $transition['changed']) {
            return back()->with('success', 'Status pesanan tidak berubah.');
        }

        $this->notifyCustomerOfStatus($order->fresh(['items']), $newStatus);

        return back()->with('success', $newStatus === Order::STATUS_CANCELLED
            ? 'Pesanan dibatalkan dan stok produk dikembalikan.'
            : 'Status pesanan diperbarui.');
    }

    public function updateTrackingNumber(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'tracking_number' => ['required', 'string', 'max:100'],
        ]);

        $trackingNumber = trim($validated['tracking_number']);
        $result = DB::transaction(function () use ($order, $trackingNumber): array {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (! in_array($lockedOrder->status, [Order::STATUS_PROCESSING, Order::STATUS_SHIPPED], true)) {
                return ['error' => 'Nomor resi dapat ditambahkan setelah pesanan berstatus Diproses.', 'changed' => false, 'notify' => false];
            }

            $changed = $lockedOrder->shipping_tracking_number !== $trackingNumber;
            $lockedOrder->update(['shipping_tracking_number' => $trackingNumber]);

            return ['error' => null, 'changed' => $changed, 'notify' => $lockedOrder->status === Order::STATUS_SHIPPED && $changed];
        });

        if ($result['error'] !== null) {
            return back()->with('error', $result['error']);
        }

        if ($result['notify']) {
            $this->notifyCustomerOfStatus($order->fresh(), Order::STATUS_SHIPPED);
        }

        return back()->with('success', $result['notify']
            ? 'Nomor resi diperbarui dan pemberitahuan dikirim ke pelanggan.'
            : 'Nomor resi berhasil disimpan. Status Dikirim sekarang dapat dipilih.');
    }

    private function restoreInventory(Order $order): void
    {
        foreach ($this->orderedQuantitiesByVariant($order) as $productId => $variants) {
            $product = Product::query()->lockForUpdate()->find($productId);

            if ($product !== null) {
                foreach ($variants as $size => $quantity) {
                    $product->changeStockForSize($size === '' ? null : $size, $quantity);
                }
            }
        }
    }

    private function reserveInventory(Order $order): ?string
    {
        $quantities = $this->orderedQuantitiesByVariant($order);
        $products = Product::query()
            ->whereKey(array_keys($quantities))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($quantities as $productId => $variants) {
            $product = $products->get($productId);

            foreach ($variants as $size => $quantity) {
                if ($product !== null && $product->stockForSize($size === '' ? null : $size) < $quantity) {
                    return 'Stok "'.$order->items->firstWhere('product_id', $productId)?->product_name.'" ukuran '.($size !== '' ? $size : 'umum').' tidak cukup untuk mengaktifkan kembali pesanan ini.';
                }
            }
        }

        foreach ($quantities as $productId => $variants) {
            $product = $products->get($productId);

            foreach ($variants as $size => $quantity) {
                $product?->changeStockForSize($size === '' ? null : $size, -$quantity);
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, int>>
     */
    private function orderedQuantitiesByVariant(Order $order): array
    {
        $quantities = [];

        foreach ($order->items as $item) {
            if ($item->product_id === null) {
                continue;
            }

            $size = $item->size ?? '';
            $quantities[$item->product_id][$size] = ($quantities[$item->product_id][$size] ?? 0) + $item->quantity;
        }

        ksort($quantities, SORT_NUMERIC);

        return $quantities;
    }

    private function notifyCustomerOfStatus(Order $order, string $status): void
    {
        if (blank($order->customer_email)) {
            return;
        }

        if ($status === Order::STATUS_SHIPPED) {
            Notification::route('mail', $order->customer_email)
                ->notify(new OrderShippedNotification($order));
        } elseif ($status === Order::STATUS_CANCELLED) {
            Notification::route('mail', $order->customer_email)
                ->notify(new OrderCancelledNotification($order));
        } elseif (in_array($status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING], true)) {
            Notification::route('mail', $order->customer_email)
                ->notify(new OrderStatusUpdatedNotification($order, $status));
        }
    }
}
