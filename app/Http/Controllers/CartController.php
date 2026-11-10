<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(): View
    {
        $items = $this->cart->items();
        $subtotal = $this->cart->subtotal();
        $shipping = $this->cart->shipping();
        $total = $this->cart->total();

        return view('cart.index', compact('items', 'subtotal', 'shipping', 'total'));
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

        if (! $product->isInStock()) {
            return back()->with('error', 'Maaf, stok produk ini sedang habis.');
        }

        $size = $validated['size'] ?? null;

        if ($size !== null && ! in_array($size, $product->sizeList(), true)) {
            return back()->with('error', 'Ukuran yang dipilih tidak tersedia.');
        }

        $this->cart->add($product, $validated['quantity'], $size);

        return back()->with('success', $product->name.' ditambahkan ke keranjang.');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:60'],
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        if ((int) $validated['quantity'] === 0) {
            $this->cart->remove($validated['key']);
        } else {
            $this->cart->update($validated['key'], (int) $validated['quantity']);
        }

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:60'],
        ]);

        $this->cart->remove($validated['key']);

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    public function clear(): RedirectResponse
    {
        $this->cart->clear();

        return back()->with('success', 'Keranjang dikosongkan.');
    }
}
