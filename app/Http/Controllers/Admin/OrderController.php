<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
        ]);

        $newStatus = $validated['status'];
        $oldStatus = $order->status;

        if ($newStatus === $oldStatus) {
            return back()->with('success', 'Status pesanan tidak berubah.');
        }

        if ($newStatus === Order::STATUS_CANCELLED && $oldStatus !== Order::STATUS_CANCELLED) {
            DB::transaction(function () use ($order) {
                $order->load('items');

                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $item->product()->increment('stock', $item->quantity);
                    }
                }

                $order->update(['status' => Order::STATUS_CANCELLED]);
            });

            return back()->with('success', 'Pesanan dibatalkan dan stok produk dikembalikan.');
        }

        if ($oldStatus === Order::STATUS_CANCELLED && $newStatus !== Order::STATUS_CANCELLED) {
            $order->load('items');

            foreach ($order->items as $item) {
                $product = $item->product;

                if ($product && $product->stock < $item->quantity) {
                    return back()->with('error', 'Stok "'.$item->product_name.'" tidak cukup untuk mengaktifkan kembali pesanan ini.');
                }
            }

            DB::transaction(function () use ($order, $newStatus) {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $item->product()->decrement('stock', $item->quantity);
                    }
                }

                $order->update(['status' => $newStatus]);
            });

            return back()->with('success', 'Status pesanan diperbarui.');
        }

        $order->update(['status' => $newStatus]);

        return back()->with('success', 'Status pesanan diperbarui.');
    }
}
