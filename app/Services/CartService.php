<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function contents(): array
    {
        return session()->get(self::SESSION_KEY, []);
    }

    /**
     * @return array<string, array{key: string, product: Product, quantity: int, size: ?string, subtotal: int, available_stock: int}>
     */
    public function items(): array
    {
        $contents = $this->contents();

        if (empty($contents)) {
            return [];
        }

        /** @var Collection<int, Product> $products */
        $products = Product::query()
            ->active()
            ->with('category')
            ->whereIn('id', collect($contents)->pluck('product_id')->unique()->all())
            ->get()
            ->keyBy('id');

        $items = [];

        foreach ($contents as $key => $row) {
            $product = $products->get($row['product_id'] ?? null);

            if (! $product) {
                continue;
            }

            $quantity = max(1, (int) ($row['quantity'] ?? 1));
            $size = $row['size'] ?? null;

            $items[$key] = [
                'key' => $key,
                'product' => $product,
                'quantity' => $quantity,
                'size' => $size,
                'subtotal' => $product->price * $quantity,
                'available_stock' => $product->stockForSize($size),
            ];
        }

        return $items;
    }

    public function count(): int
    {
        return (int) collect($this->contents())->sum(fn ($row) => (int) ($row['quantity'] ?? 0));
    }

    public function subtotal(): int
    {
        return (int) collect($this->items())->sum('subtotal');
    }

    public function add(Product $product, int $quantity, ?string $size = null): void
    {
        $size = $size ?: null;
        $availableStock = $product->stockForSize($size);

        if ($availableStock < 1) {
            return;
        }

        $quantity = max(1, min($quantity, $availableStock));
        $key = $this->key($product, $size);

        $cart = $this->contents();
        $current = (int) ($cart[$key]['quantity'] ?? 0);

        $cart[$key] = [
            'product_id' => $product->id,
            'quantity' => min($current + $quantity, $availableStock),
            'size' => $size,
        ];

        session()->put(self::SESSION_KEY, $cart);
    }

    public function update(string $key, int $quantity): void
    {
        $cart = $this->contents();

        if (! isset($cart[$key])) {
            return;
        }

        $product = Product::query()->active()->find($cart[$key]['product_id']);

        if (! $product) {
            unset($cart[$key]);
            session()->put(self::SESSION_KEY, $cart);

            return;
        }

        $availableStock = $product->stockForSize($cart[$key]['size'] ?? null);

        if ($availableStock < 1) {
            unset($cart[$key]);
            session()->put(self::SESSION_KEY, $cart);

            return;
        }

        $cart[$key]['quantity'] = max(1, min($quantity, $availableStock));
        session()->put(self::SESSION_KEY, $cart);
    }

    public function remove(string $key): void
    {
        $cart = $this->contents();
        unset($cart[$key]);
        session()->put(self::SESSION_KEY, $cart);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    private function key(Product $product, ?string $size): string
    {
        return $product->id.'|'.($size ?: '-');
    }
}
