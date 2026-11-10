<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(): View|RedirectResponse
    {
        $items = $this->cart->items();

        if (empty($items)) {
            return redirect()->route('products.index')
                ->with('error', 'Keranjang masih kosong. Yuk pilih pakaian anak dulu!');
        }

        $subtotal = $this->cart->subtotal();
        $shipping = $this->cart->shipping();
        $total = $this->cart->total();

        return view('checkout.index', compact('items', 'subtotal', 'shipping', 'total'));
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $items = $this->cart->items();

        if (empty($items)) {
            return redirect()->route('products.index')
                ->with('error', 'Keranjang masih kosong.');
        }

        $order = DB::transaction(function () use ($items, $request) {
            $subtotal = 0;
            $preparedItems = [];

            foreach ($items as $item) {
                $product = Product::query()->lockForUpdate()->find($item['product']->id);

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'Produk "'.$item['product']->name.'" sudah tidak tersedia.',
                    ]);
                }

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'cart' => 'Stok "'.$product->name.'" tinggal '.$product->stock.' buah. Silakan perbarui jumlah.',
                    ]);
                }

                $lineTotal = $product->price * $item['quantity'];
                $subtotal += $lineTotal;

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'size' => $item['size'],
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $lineTotal,
                ];

                $product->decrement('stock', $item['quantity']);
            }

            $shipping = $subtotal >= CartService::FREE_SHIPPING_MIN ? 0 : CartService::SHIPPING_COST;

            $order = Order::create([
                'customer_name' => $request->validated('customer_name'),
                'customer_phone' => $request->validated('customer_phone'),
                'customer_email' => $request->validated('customer_email'),
                'customer_address' => $request->validated('customer_address'),
                'note' => $request->validated('note'),
                'payment_method' => $request->validated('payment_method'),
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'total' => $subtotal + $shipping,
                'status' => Order::STATUS_PENDING,
            ]);

            $order->items()->createMany($preparedItems);

            return $order;
        });

        $this->cart->clear();

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pesanan berhasil dibuat! Simpan kode pesanan untuk melacak status.');
    }
}
