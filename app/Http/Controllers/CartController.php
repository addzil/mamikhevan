<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ActivityLogger;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function index(): View
    {
        $items = $this->cart->items();
        $subtotal = $this->cart->subtotal();
        $hasFreeShippingProduct = collect($items)->contains(fn (array $item): bool => $item['product']->free_shipping);

        return view('cart.index', compact('items', 'subtotal', 'hasFreeShippingProduct'));
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'size' => ['nullable', 'string', 'max:15'],
        ]);

        $product = Product::query()->active()->find($validated['product_id']);

        if (! $product) {
            return back()->with('error', 'Produk tidak ditemukan atau sudah tidak dijual.');
        }

        $size = $validated['size'] ?? null;

        if ($product->sizeList() !== [] && $size === null) {
            return back()->with('error', 'Pilih ukuran produk sebelum menambahkan ke keranjang.');
        }

        if ($size !== null && ! in_array($size, $product->sizeList(), true)) {
            return back()->with('error', 'Ukuran yang dipilih tidak tersedia.');
        }

        if ($product->stockForSize($size) < 1) {
            return back()->with('error', $size !== null
                ? 'Stok ukuran '.$size.' sedang habis.'
                : 'Maaf, stok produk ini sedang habis.');
        }

        $this->cart->add($product, $validated['quantity'], $size);
        $this->activityLogger->record(
            'store.cart.item_added',
            'Produk ditambahkan ke keranjang.',
            $product,
            ['quantity' => $validated['quantity'], 'size' => $size],
        );

        return back()->with('success', $product->name.' ditambahkan ke keranjang.');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:60'],
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $item = $this->cart->items()[$validated['key']] ?? null;

        if ((int) $validated['quantity'] === 0) {
            $this->cart->remove($validated['key']);
            $this->activityLogger->record(
                'store.cart.item_removed',
                'Produk dihapus dari keranjang.',
                $item['product'] ?? null,
                ['quantity' => $item['quantity'] ?? null],
            );
        } else {
            $this->cart->update($validated['key'], (int) $validated['quantity']);
            $this->activityLogger->record(
                'store.cart.item_updated',
                'Jumlah produk pada keranjang diperbarui.',
                $item['product'] ?? null,
                ['before_quantity' => $item['quantity'] ?? null, 'after_quantity' => (int) $validated['quantity']],
            );
        }

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:60'],
        ]);

        $item = $this->cart->items()[$validated['key']] ?? null;
        $this->cart->remove($validated['key']);
        $this->activityLogger->record(
            'store.cart.item_removed',
            'Produk dihapus dari keranjang.',
            $item['product'] ?? null,
            ['quantity' => $item['quantity'] ?? null],
        );

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    public function clear(): RedirectResponse
    {
        $itemCount = count($this->cart->items());
        $this->cart->clear();

        if ($itemCount > 0) {
            $this->activityLogger->record(
                'store.cart.cleared',
                'Keranjang dikosongkan.',
                properties: ['item_count' => $itemCount],
            );
        }

        return back()->with('success', 'Keranjang dikosongkan.');
    }
}
