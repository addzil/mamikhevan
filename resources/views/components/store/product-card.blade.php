@props(['product'])

<article class="group flex h-full flex-col overflow-hidden rounded-[1.75rem] border border-yellow-100 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-amber-200 hover:shadow-xl">
    <a href="{{ route('products.show', $product) }}" class="relative block aspect-square overflow-hidden bg-yellow-50">
        <x-store.product-image :product="$product" class="transition duration-500 {{ $product->image_fit === 'cover' ? 'group-hover:scale-105' : '' }}" />

        <div class="absolute left-3 top-3 flex flex-col items-start gap-1.5">
            @if ($product->hasDiscount())
                <span class="rounded-full bg-amber-500 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide text-white shadow-sm">
                    -{{ $product->discountPercent() }}%
                </span>
            @endif
            @if ($product->is_featured)
                <span class="rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide text-yellow-700 shadow-sm backdrop-blur">
                    Favorit
                </span>
            @endif
        </div>

        @if (! $product->isInStock())
            <div class="absolute inset-0 grid place-items-center bg-stone-900/40 backdrop-blur-[2px]">
                <span class="rounded-full bg-white px-4 py-2 text-sm font-bold text-stone-700">Stok habis</span>
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-4">
        <div class="flex items-center justify-between gap-2 text-[11px] font-bold uppercase tracking-wider">
            <span class="truncate text-amber-600">{{ $product->category?->name ?? 'Pakaian Anak' }}</span>
            @if ($product->age_group)
                <span class="flex-none text-stone-400">{{ $product->age_group }}</span>
            @endif
        </div>

        <h3 class="mt-2 font-display text-lg font-bold leading-tight text-stone-900">
            <a href="{{ route('products.show', $product) }}" class="transition hover:text-amber-600">{{ $product->name }}</a>
        </h3>

        <p class="mt-1 text-xs font-semibold text-stone-400">
            {{ $product->genderLabel() }} &middot; Stok {{ $product->stock }}
        </p>

        <div class="mt-auto flex items-end justify-between gap-2 pt-4">
            <div>
                <p class="font-display text-xl font-bold text-stone-900">{{ $product->price_formatted }}</p>
                @if ($product->hasDiscount())
                    <p class="text-xs font-semibold text-stone-400 line-through">{{ $product->compare_price_formatted }}</p>
                @endif
            </div>

            @if ($product->isInStock())
                @if (count($product->sizeList()))
                    <a href="{{ route('products.show', $product) }}"
                       class="grid h-10 w-10 flex-none place-items-center rounded-full bg-amber-500 text-white shadow-sm transition hover:bg-amber-600"
                       aria-label="Pilih ukuran {{ $product->name }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                @else
                    <form action="{{ route('cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit"
                                class="grid h-10 w-10 flex-none place-items-center rounded-full bg-amber-500 text-white shadow-sm transition hover:bg-amber-600"
                                aria-label="Tambahkan {{ $product->name }} ke keranjang">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        </button>
                    </form>
                @endif
            @else
                <span class="grid h-10 w-10 flex-none place-items-center rounded-full bg-stone-100 text-stone-300" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </span>
            @endif
        </div>
    </div>
</article>
