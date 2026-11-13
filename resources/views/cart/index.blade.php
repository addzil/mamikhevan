@extends('layouts.storefront')

@section('title', 'Keranjang Belanja — Mamikhevanshop')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <nav class="flex items-center gap-2 text-xs font-bold text-stone-400" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="transition hover:text-amber-600">Beranda</a>
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            <span class="text-amber-600">Keranjang</span>
        </nav>

        <div class="mt-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
            <div>
                <h1 class="font-display text-3xl font-extrabold text-stone-900 sm:text-4xl">Keranjang belanja</h1>
                <p class="mt-2 text-sm text-stone-500">Periksa kembali pilihanmu sebelum checkout.</p>
            </div>
            @if (! empty($items))
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-amber-600 hover:text-yellow-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                    Lanjut belanja
                </a>
            @endif
        </div>

        @if (empty($items))
            <div class="mt-10 rounded-[2.5rem] border border-dashed border-yellow-200 bg-white px-6 py-20 text-center">
                <span class="mx-auto grid h-20 w-20 place-items-center rounded-3xl bg-gradient-to-br from-yellow-100 to-amber-100 text-4xl">🛍️</span>
                <h2 class="mt-5 font-display text-2xl font-extrabold text-stone-900">Keranjang masih kosong</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-stone-500">
                    Yuk jelajahi katalog dan temukan pakaian lucu yang nyaman untuk si kecil.
                </p>
                <a href="{{ route('products.index') }}" class="mt-6 inline-flex rounded-full bg-amber-500 px-7 py-3.5 text-sm font-extrabold text-white shadow-soft transition hover:bg-amber-600">
                    Mulai belanja
                </a>
                <p class="mt-4 text-xs font-semibold text-stone-400">
                    Punya kode giveaway? <a href="{{ route('giveaway.claim') }}" class="font-extrabold text-amber-600 hover:underline">Klaim hadiah gratis</a>
                </p>
            </div>
        @else
            <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_360px] lg:gap-8">
                <div class="space-y-4">
                    @if ($hasFreeShippingProduct)
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-700">
                            Produk bebas ongkir tidak menambah ongkos kirim. Jika ada produk lain, ongkir dihitung dari produk yang tidak bebas ongkir.
                        </div>
                    @endif

                    @foreach ($items as $item)
                        <article class="flex flex-col gap-4 rounded-3xl border border-yellow-100 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:p-5">
                            <a href="{{ route('products.show', $item['product']) }}" class="block h-24 w-24 flex-none overflow-hidden rounded-2xl bg-yellow-50 sm:h-28 sm:w-28">
                                <x-store.product-image :product="$item['product']" />
                            </a>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <a href="{{ route('products.show', $item['product']) }}" class="font-display text-lg font-bold leading-tight text-stone-900 transition hover:text-amber-600">
                                            {{ $item['product']->name }}
                                        </a>
                                        <p class="mt-1 text-xs font-semibold text-stone-400">
                                            {{ $item['product']->category?->name ?? 'Pakaian Anak' }}
                                            @if ($item['size'])
                                                &middot; Ukuran {{ $item['size'] }}
                                            @endif
                                        </p>
                                        <p class="mt-1 text-[11px] font-semibold text-stone-400">Sisa ukuran ini: {{ $item['available_stock'] }} unit</p>
                                        @if ($item['product']->free_shipping)
                                            <span class="mt-2 inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-extrabold text-emerald-700">Bebas ongkir</span>
                                        @endif
                                    </div>
                                    <p class="font-display text-lg font-bold text-stone-900">{{ $item['product']->price_formatted }}</p>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <form action="{{ route('cart.update') }}" method="POST" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="key" value="{{ $item['key'] }}">
                                            <label for="qty-{{ $loop->index }}" class="sr-only">Jumlah {{ $item['product']->name }}</label>
                                            <input id="qty-{{ $loop->index }}" type="number" name="quantity" value="{{ $item['quantity'] }}"
                                                   min="1" max="{{ max(1, $item['available_stock']) }}"
                                                   class="w-16 rounded-xl border-yellow-200 py-2 text-center text-sm font-bold focus:border-amber-300 focus:ring-amber-200">
                                            <button type="submit" class="rounded-xl border border-yellow-200 px-3 py-2 text-xs font-extrabold text-stone-600 transition hover:border-amber-200 hover:text-amber-600">
                                                Update
                                            </button>
                                        </form>
                                        <form action="{{ route('cart.remove') }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="key" value="{{ $item['key'] }}">
                                            <button type="submit" class="rounded-xl p-2 text-stone-400 transition hover:bg-amber-50 hover:text-amber-600" aria-label="Hapus {{ $item['product']->name }}">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            </button>
                                        </form>
                                    </div>

                                    <p class="text-sm font-extrabold text-amber-600">
                                        Rp{{ number_format($item['subtotal'], 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    @endforeach

                    <div class="flex justify-end">
                        <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Kosongkan seluruh keranjang?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-bold text-stone-400 underline decoration-stone-300 underline-offset-4 transition hover:text-amber-600">
                                Kosongkan keranjang
                            </button>
                        </form>
                    </div>
                </div>

                <aside>
                    <div class="sticky top-28 rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                        <h2 class="font-display text-xl font-bold text-stone-900">Ringkasan pesanan</h2>

                        <dl class="mt-5 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <dt class="font-semibold text-stone-500">Subtotal</dt>
                                <dd class="font-extrabold text-stone-800">Rp{{ number_format($subtotal, 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="font-semibold text-stone-500">Ongkos kirim</dt>
                                <dd class="font-extrabold text-stone-800">Dihitung di checkout</dd>
                            </div>
                        </dl>

                        <div class="mt-5 flex items-end justify-between border-t border-dashed border-yellow-200 pt-5">
                            <p class="text-sm font-extrabold text-stone-900">Subtotal</p>
                            <p class="font-display text-2xl font-extrabold text-amber-600">Rp{{ number_format($subtotal, 0, ',', '.') }}</p>
                        </div>

                        <a href="{{ route('checkout.index') }}"
                           class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3.5 text-sm font-extrabold text-white shadow-soft transition hover:bg-amber-600">
                            Lanjut ke checkout
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>

                        <p class="mt-3 text-center text-[11px] font-semibold text-stone-400">
                            Punya kode giveaway? <a href="{{ route('giveaway.claim') }}" class="font-extrabold text-amber-600 hover:underline">Klaim hadiah</a>
                        </p>

                        <p class="mt-4 flex items-center justify-center gap-1.5 text-[11px] font-semibold text-stone-400">
                            <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                            Data pelanggan hanya digunakan untuk memproses pesanan.
                        </p>
                    </div>
                </aside>
            </div>
        @endif
    </section>
@endsection
