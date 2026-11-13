@extends('layouts.storefront')

@section('title', 'Mamikhevanshop — Pakaian Anak Lucu, Nyaman & Terjangkau')

@section('content')
    @if ($hero)
    @php
        $heroImages = $hero->cards ?? [];
        $heroImageClasses = $hero->design === 'single'
            ? ['left-1/2 top-1/2 w-56 -translate-x-1/2 -translate-y-1/2 sm:w-72', 'hidden', 'hidden']
            : ($hero->design === 'stacked'
                ? ['left-1/2 top-0 w-40 -translate-x-1/2 sm:w-48', 'bottom-0 left-0 w-36 rotate-[-5deg] sm:w-44', 'bottom-0 right-0 w-36 rotate-[5deg] sm:w-44']
                : ['left-0 top-8 w-40 rotate-[-6deg] sm:w-48', 'right-0 top-0 w-36 rotate-[7deg] sm:w-44', 'bottom-0 left-1/2 w-40 -translate-x-1/2 rotate-[3deg] sm:w-52']);
    @endphp

    <section class="relative overflow-hidden">
        <div @class(['pointer-events-none absolute -left-24 top-10 h-72 w-72 rounded-full blur-3xl', 'bg-amber-200/40' => $hero->background === 'warm', 'bg-teal-200/40' => $hero->background === 'mint', 'bg-violet-200/40' => $hero->background === 'lavender'])></div>
        <div class="pointer-events-none absolute -right-20 top-40 h-80 w-80 rounded-full bg-amber-200/40 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 left-1/3 h-64 w-64 rounded-full bg-teal-200/30 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-14 px-4 pb-16 pt-14 sm:px-6 lg:grid-cols-2 lg:gap-8 lg:px-8 lg:pb-24 lg:pt-20">
            <div class="text-center lg:text-left">
                <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-white px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-amber-600 shadow-sm">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-amber-400"></span>
                    {{ $hero->eyebrow }}
                </span>

                <h1 class="mt-6 font-display text-4xl font-extrabold leading-[1.1] tracking-tight text-stone-900 text-balance sm:text-5xl lg:text-6xl">
                    {{ $hero->headline }}
                </h1>

                <p class="mx-auto mt-6 max-w-xl text-base leading-8 text-stone-500 sm:text-lg lg:mx-0">
                    {{ $hero->description }}
                </p>

                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row lg:justify-start">
                    <a href="{{ route('products.index') }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-amber-500 px-7 py-3.5 text-sm font-extrabold text-white shadow-soft transition hover:-translate-y-0.5 hover:bg-amber-600 sm:w-auto">
                        Belanja Sekarang
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <a href="#kategori"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-yellow-200 bg-white px-7 py-3.5 text-sm font-extrabold text-stone-700 transition hover:border-amber-200 hover:text-amber-600 sm:w-auto">
                        Lihat Kategori
                    </a>
                    <a href="{{ route('orders.track') }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-7 py-3.5 text-sm font-extrabold text-amber-700 transition hover:bg-amber-100 sm:w-auto">
                        Lacak Pesanan
                    </a>
                </div>

                <div class="mt-9 flex flex-col items-center gap-4 sm:flex-row lg:justify-start">
                    <div class="flex -space-x-3">
                        <span class="grid h-10 w-10 place-items-center rounded-full border-2 border-[#FFFDF4] bg-amber-200 text-xs font-extrabold text-amber-700">RK</span>
                        <span class="grid h-10 w-10 place-items-center rounded-full border-2 border-[#FFFDF4] bg-amber-200 text-xs font-extrabold text-amber-700">BS</span>
                        <span class="grid h-10 w-10 place-items-center rounded-full border-2 border-[#FFFDF4] bg-teal-200 text-xs font-extrabold text-teal-700">SA</span>
                        <span class="grid h-10 w-10 place-items-center rounded-full border-2 border-[#FFFDF4] bg-sky-200 text-xs font-extrabold text-sky-700">+1k</span>
                    </div>
                    <div class="text-center sm:text-left">
                        <div class="flex items-center justify-center gap-1 text-amber-400 sm:justify-start">
                            @for ($i = 0; $i < 5; $i++)
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.539 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.98 10.1c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69l1.519-4.674Z"/></svg>
                            @endfor
                        </div>
                        <p class="mt-1 text-xs font-bold text-stone-500">4,9/5 dari 1.200+ orang tua</p>
                    </div>
                </div>
            </div>

            <div @class(['relative mx-auto h-[420px] w-full max-w-lg sm:h-[480px] lg:h-[540px]', 'rounded-[3rem] bg-gradient-to-br from-amber-100 to-yellow-50' => $hero->background === 'warm', 'rounded-[3rem] bg-gradient-to-br from-teal-100 to-emerald-50' => $hero->background === 'mint', 'rounded-[3rem] bg-gradient-to-br from-violet-100 to-pink-50' => $hero->background === 'lavender'])>
                <div class="absolute inset-8 rounded-[3rem] bg-gradient-to-br from-amber-200 via-yellow-100 to-amber-100 opacity-80 blur-sm"></div>

                @foreach ($heroImages as $index => $image)
                    @php($product = $heroProducts->get($image['product_id'] ?? null))
                    @if ($product)
                        <a href="{{ route('products.show', $product) }}" aria-label="Lihat detail {{ $product->name }}" class="absolute {{ $heroImageClasses[$index] ?? 'hidden' }} overflow-hidden rounded-[2rem] border border-white/80 bg-white p-2 shadow-2xl shadow-amber-200/50 transition duration-500 hover:rotate-0 hover:scale-[1.03]">
                    @else
                    <div class="absolute {{ $heroImageClasses[$index] ?? 'hidden' }} overflow-hidden rounded-[2rem] border border-white/80 bg-white p-2 shadow-2xl shadow-amber-200/50 transition duration-500 hover:rotate-0 hover:scale-[1.03]">
                    @endif
                        <div class="overflow-hidden rounded-[1.6rem]">
                            <img src="{{ $product?->image_url ?? asset($image['image'] ?? 'images/products/dress.svg') }}" alt="{{ $product?->name ?? $image['name'] ?? '' }}" class="aspect-square w-full object-cover">
                        </div>
                        <div class="flex items-center justify-between gap-2 px-2 pb-1 pt-3">
                            <div>
                                <p class="text-[9px] font-extrabold uppercase tracking-wider text-amber-400">{{ $image['label'] }}</p>
                                <p class="font-display text-sm font-bold leading-tight text-stone-800">{{ $product?->name ?? $image['name'] ?? '' }}</p>
                            </div>
                            @if ($product)<p class="text-xs font-extrabold text-amber-600">{{ $product->price_formatted }}</p>@endif
                        </div>
                    @if ($product)
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach

                <div class="absolute left-[34%] top-[8%] z-20 -translate-x-1/2 -translate-y-1/2 animate-float rounded-full border border-amber-100 bg-white/95 px-3 py-2 text-center shadow-xl backdrop-blur-sm sm:px-4">
                    <p class="text-[9px] font-extrabold uppercase tracking-[0.16em] text-amber-500">Belanja nyaman</p>
                    <p class="font-display text-[11px] font-extrabold text-stone-800 sm:text-xs">Online shop terpercaya</p>
                </div>

                <div class="absolute right-2 top-1/2 z-10 animate-float rounded-2xl border border-yellow-100 bg-white px-4 py-3 shadow-xl sm:right-0">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Bahan</p>
                    <p class="font-display text-sm font-bold text-stone-800">Brand premium</p>
                </div>

                <div class="absolute bottom-16 left-0 z-10 animate-float-slow rounded-2xl border border-yellow-100 bg-white px-4 py-3 shadow-xl">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Kualitas</p>
                    <p class="font-display text-sm font-bold text-stone-800">Kualitas terbaik</p>
                </div>
            </div>
        </div>
    </section>
    @endif

    <section id="kategori" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-amber-400">Belanja sesuai kebutuhan</p>
                <h2 class="mt-2 font-display text-3xl font-extrabold text-stone-900 sm:text-4xl">Jelajahi kategori</h2>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-amber-600 hover:text-yellow-700">
                Semua produk
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>

        <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-6">
            @foreach ($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                   class="group rounded-3xl border border-yellow-100 bg-white p-4 text-center shadow-sm transition hover:-translate-y-1 hover:border-amber-200 hover:shadow-lg sm:p-5">
                    <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-yellow-50 to-amber-50 text-3xl transition group-hover:scale-110">
                        {{ $category->icon ?: '🧺' }}
                    </span>
                    <h3 class="mt-3 font-display text-sm font-bold leading-tight text-stone-900 sm:text-base">{{ $category->name }}</h3>
                    <p class="mt-1 text-[11px] font-bold text-stone-400">{{ $category->products_count }} produk</p>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-amber-400">Paling dicari orang tua</p>
                <h2 class="mt-2 font-display text-3xl font-extrabold text-stone-900 sm:text-4xl">Produk favorit</h2>
            </div>
            <a href="{{ route('products.index', ['sort' => 'popular']) }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-amber-600 hover:text-yellow-700">
                Lihat semua
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>

        <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
            @forelse ($featuredProducts as $product)
                <x-store.product-card :product="$product" />
            @empty
                <p class="col-span-full rounded-3xl border border-dashed border-yellow-200 bg-white p-10 text-center text-sm text-stone-500">
                    Belum ada produk favorit. Silakan tambahkan dari panel admin.
                </p>
            @endforelse
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-amber-400">Baru mendarat</p>
                <h2 class="mt-2 font-display text-3xl font-extrabold text-stone-900 sm:text-4xl">Koleksi terbaru</h2>
            </div>
            <a href="{{ route('products.index', ['sort' => 'latest']) }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-amber-600 hover:text-yellow-700">
                Lihat semua
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>

        <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
            @foreach ($newestProducts as $product)
                <x-store.product-card :product="$product" />
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-3 sm:gap-5">
            <div class="rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </span>
                <h3 class="mt-4 font-display text-xl font-bold text-stone-900">Bahan aman untuk kulit</h3>
                <p class="mt-2 text-sm leading-7 text-stone-500">Katun organik dan pewarna ramah anak, sudah lolos uji keamanan bahan.</p>
            </div>
            <div class="rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-50 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                </span>
                <h3 class="mt-4 font-display text-xl font-bold text-stone-900">Dikirim cepat</h3>
                <p class="mt-2 text-sm leading-7 text-stone-500">Pesanan diproses di hari yang sama sebelum jam 15.00 WIB.</p>
            </div>
            <div class="rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-50 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                </span>
                <h3 class="mt-4 font-display text-xl font-bold text-stone-900">Admin responsif</h3>
                <p class="mt-2 text-sm leading-7 text-stone-500">Hubungi admin jika ada kendala dalam pembelian atau ingin mengajukan retur produk.</p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center text-center">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-amber-400">Kata orang tua</p>
            <h2 class="mt-2 font-display text-3xl font-extrabold text-stone-900 sm:text-4xl">Mereka sudah mencoba</h2>
            <a href="{{ config('shop.google_maps.url') }}" target="_blank" rel="noopener noreferrer"
               class="mt-3 inline-flex items-center gap-2 rounded-full border border-yellow-200 bg-white px-4 py-2 text-xs font-extrabold text-stone-600 shadow-sm transition hover:border-amber-300 hover:text-amber-600">
                <x-star-display :rating="config('shop.google_maps.rating')" size="h-4 w-4" />
                <span>{{ number_format((float) config('shop.google_maps.rating'), 1, ',', '.') }} &middot; {{ config('shop.google_maps.review_count') }} ulasan Google Maps</span>
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3 sm:gap-5">
            @forelse ($reviews as $review)
                <figure class="flex flex-col rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <x-star-display :rating="$review->rating" />
                        <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-stone-500">{{ $review->sourceLabel() }}</span>
                    </div>
                    <blockquote class="mt-4 flex-1 text-sm leading-7 text-stone-600">&ldquo;{{ $review->comment ?: 'Pelanggan puas dengan produk kami.' }}&rdquo;</blockquote>
                    <figcaption class="mt-5 flex items-center gap-3">
                        <span class="grid h-10 w-10 flex-none place-items-center rounded-full bg-amber-100 text-xs font-extrabold text-yellow-700">
                            {{ strtoupper(substr($review->reviewer_name, 0, 1)) }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-extrabold text-stone-900">{{ $review->reviewer_name }}</p>
                            <p class="text-xs font-semibold text-stone-400">
                                {{ $review->reviewer_city ?: 'Pelanggan' }}@if ($review->reviewed_at) &middot; {{ $review->reviewed_at->translatedFormat('M Y') }}@endif
                            </p>
                        </div>
                    </figcaption>
                </figure>
            @empty
                <p class="col-span-full rounded-3xl border border-dashed border-yellow-200 bg-white p-8 text-center text-sm font-semibold text-stone-400">Belum ada ulasan. Jadilah yang pertama setelah berbelanja!</p>
            @endforelse
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-6 pt-10 sm:px-6 lg:px-8">
        <div class="rounded-[2.5rem] border border-yellow-100 bg-gradient-to-r from-yellow-50 via-amber-50 to-amber-50 px-6 py-10 text-center sm:px-12">
            <h2 class="font-display text-2xl font-extrabold text-stone-900 sm:text-3xl">Masih bingung pilih ukuran?</h2>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-stone-500">
                Tim kami siap membantu memilihkan ukuran yang pas berdasarkan usia dan berat si kecil. Gratis, tanpa harus beli dulu.
            </p>
            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('contact.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-stone-900 px-6 py-3 text-sm font-extrabold text-white transition hover:bg-stone-700">
                    Tanya lewat form
                </a>
                <a href="https://wa.me/6281234567890?text=Halo%20Mamikhevanshop%2C%20saya%20butuh%20bantuan%20memilih%20ukuran."
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center justify-center gap-2 rounded-full border border-emerald-200 bg-white px-6 py-3 text-sm font-extrabold text-emerald-600 transition hover:border-emerald-300">
                    Chat WhatsApp
                </a>
            </div>
        </div>
    </section>
@endsection
