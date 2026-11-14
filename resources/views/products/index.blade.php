@extends('layouts.storefront')

@php
    $pageTitle = $activeCategory?->name ?? ($saleOnly ? 'Produk sale' : 'Katalog Pakaian Anak');
    $queryWithoutCategory = request()->except('category', 'page');
    $hasFilters = $search !== '' || $activeCategory || $gender || $maxPrice > 0 || $saleOnly;
@endphp

@section('title', $pageTitle.' — Mamikhevanshop')
@section('meta_description', 'Jelajahi katalog pakaian anak Mamikhevanshop: atasan, bawahan, dress, jaket, sepatu, dan aksesori dengan bahan lembut dan harga bersahabat.')

@section('content')
    <section class="relative overflow-hidden border-b border-yellow-100 bg-gradient-to-b from-yellow-50/80 to-[#FFFDF4]">
        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-amber-200/40 blur-3xl"></div>
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <nav class="flex items-center gap-2 text-xs font-bold text-stone-400" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="transition hover:text-amber-600">Beranda</a>
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                <a href="{{ route('products.index') }}" class="transition hover:text-amber-600">Katalog</a>
                @if ($activeCategory)
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    <span class="text-amber-600">{{ $activeCategory->name }}</span>
                @endif
            </nav>

            <div class="mt-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <h1 class="font-display text-3xl font-extrabold tracking-tight text-stone-900 sm:text-4xl">{{ $pageTitle }}</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-stone-500">
                        {{ $activeCategory?->description ?: 'Pilih pakaian yang nyaman untuk si kecil. Semua produk sudah melewati pengecekan kualitas sebelum dikirim.' }}
                    </p>
                </div>
                <p class="inline-flex w-fit items-center gap-2 rounded-full border border-yellow-100 bg-white px-4 py-2 text-xs font-extrabold text-stone-500">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    {{ $products->total() }} produk ditemukan
                </p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-[270px_1fr] lg:gap-8">
            <details class="group mb-6 rounded-3xl border border-yellow-100 bg-white p-5 shadow-sm lg:hidden">
                <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-extrabold text-stone-800">
                    <span class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                        Filter &amp; urutkan
                    </span>
                    <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </summary>

                <form action="{{ route('products.index') }}" method="GET" class="mt-5 space-y-5">
                    @include('products._filters')
                    <button type="submit" class="w-full rounded-xl bg-stone-900 px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-stone-700">Terapkan filter</button>
                </form>
            </details>

            <aside class="hidden lg:block">
                <form action="{{ route('products.index') }}" method="GET"
                      class="sticky top-28 space-y-6 rounded-3xl border border-yellow-100 bg-white p-5 shadow-sm">
                    @include('products._filters')
                </form>
            </aside>

            <div>
                <div class="mb-5 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($search !== '')
                            <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-yellow-700">
                                Cari: &ldquo;{{ $search }}&rdquo;
                                <a href="{{ route('products.index', request()->except('q', 'page')) }}" class="rounded-full p-0.5 hover:bg-amber-100" aria-label="Hapus pencarian">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </a>
                            </span>
                        @endif
                        @if ($saleOnly)
                            <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-yellow-700">
                                Produk sale
                                <a href="{{ route('products.index', request()->except('sale', 'page')) }}" class="rounded-full p-0.5 hover:bg-amber-100" aria-label="Hapus filter sale">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </a>
                            </span>
                        @endif
                        @if ($activeCategory)
                            <span class="inline-flex items-center gap-2 rounded-full bg-yellow-50 px-3 py-1.5 text-xs font-bold text-yellow-600">
                                {{ $activeCategory->name }}
                                <a href="{{ route('products.index', $queryWithoutCategory) }}" class="rounded-full p-0.5 hover:bg-yellow-100" aria-label="Hapus kategori">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </a>
                            </span>
                        @endif
                        @if ($gender)
                            <span class="inline-flex items-center gap-2 rounded-full bg-teal-50 px-3 py-1.5 text-xs font-bold text-teal-600">
                                {{ $gender === 'laki-laki' ? 'Anak Laki-laki' : ($gender === 'perempuan' ? 'Anak Perempuan' : 'Uniseks') }}
                                <a href="{{ route('products.index', request()->except('gender', 'page')) }}" class="rounded-full p-0.5 hover:bg-teal-100" aria-label="Hapus filter gender">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </a>
                            </span>
                        @endif
                    </div>

                    @if ($hasFilters)
                        <a href="{{ route('products.index') }}" class="text-xs font-bold text-stone-400 transition hover:text-amber-600">Reset semua</a>
                    @endif
                </div>

                @if ($products->isEmpty())
                    <div class="rounded-3xl border border-dashed border-yellow-200 bg-white px-6 py-20 text-center">
                        <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-yellow-50 text-3xl">🔍</span>
                        <h2 class="mt-4 font-display text-2xl font-bold text-stone-900">Produk tidak ditemukan</h2>
                        <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-stone-500">
                            Coba kata kunci lain, longgarkan filter harga, atau jelajahi seluruh katalog kami.
                        </p>
                        <a href="{{ route('products.index') }}" class="mt-6 inline-flex rounded-full bg-amber-500 px-6 py-3 text-sm font-extrabold text-white transition hover:bg-amber-600">
                            Lihat semua produk
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-4 sm:gap-5 xl:grid-cols-3">
                        @foreach ($products as $product)
                            <x-store.product-card :product="$product" />
                        @endforeach
                    </div>

                    <div class="mt-10">
                        {{ $products->links('vendor.pagination.mamikhevan') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
