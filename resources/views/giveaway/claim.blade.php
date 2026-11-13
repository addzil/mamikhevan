@extends('layouts.storefront')

@section('title', 'Klaim Hadiah — Mamikhevanshop')

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <nav class="flex items-center gap-2 text-xs font-bold text-stone-400" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="transition hover:text-amber-600">Beranda</a>
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            <span class="text-amber-600">Klaim hadiah</span>
        </nav>

        <div class="mt-4">
            <h1 class="font-display text-3xl font-extrabold text-stone-900 sm:text-4xl">Klaim hadiah gratis</h1>
            <p class="mt-2 text-sm leading-6 text-stone-500">
                Punya kode giveaway atau treasure? Masukkan kodenya di sini. Hadiahnya benar-benar gratis &mdash; kamu hanya membayar ongkos kirim.
            </p>
        </div>

        @if ($giveaway)
            <div class="mt-8 rounded-3xl border border-emerald-200 bg-emerald-50/60 p-6 shadow-sm">
                <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-700">Hadiah terkunci</p>
                <div class="mt-4 flex items-center gap-4">
                    <div class="h-24 w-24 flex-none overflow-hidden rounded-2xl bg-white">
                        <x-store.product-image :product="$giveaway['gift_product']" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-display text-lg font-bold text-stone-900">{{ $giveaway['gift_product']->name }}</p>
                        <p class="mt-1 text-xs font-semibold text-stone-500">
                            {{ $giveaway['gift_quantity'] }} item gratis
                            @if ($giveaway['promotion']->gift_size)
                                &middot; Ukuran {{ $giveaway['promotion']->gift_size }}
                            @endif
                        </p>
                        <p class="mt-1 font-mono text-xs font-bold text-emerald-700">{{ $voucherCode }}</p>
                    </div>
                </div>
                <div class="mt-5 flex flex-col gap-2 sm:flex-row">
                    <a href="{{ route('checkout.index') }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-extrabold text-white shadow-soft transition hover:bg-amber-600">
                        Lanjut checkout &amp; hitung ongkir
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <form action="{{ route('giveaway.clear') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-xl border border-stone-200 bg-white px-5 py-3 text-sm font-extrabold text-stone-600 transition hover:border-amber-200 hover:text-amber-600">
                            Batalkan klaim
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <div class="mt-8 rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
            <h2 class="font-display text-lg font-bold text-stone-900">{{ $giveaway ? 'Klaim kode lain' : 'Masukkan kode hadiah' }}</h2>
            <p class="mt-1 text-xs font-semibold leading-5 text-stone-400">Kode bersifat khusus dan hanya bisa dipakai selama promo masih aktif serta stok hadiah tersedia.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs font-semibold text-amber-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('giveaway.store') }}" method="POST" class="mt-4 flex flex-col gap-2 sm:flex-row">
                @csrf
                <label for="code" class="sr-only">Kode giveaway</label>
                <input id="code" name="code" type="text" required maxlength="40" value="{{ old('code') }}" placeholder="Contoh: TREASURE24"
                       class="w-full rounded-xl border-yellow-200 py-3 text-sm font-bold uppercase focus:border-amber-300 focus:ring-amber-200">
                <button type="submit" class="rounded-xl bg-stone-900 px-6 py-3 text-sm font-extrabold text-white transition hover:bg-stone-700">
                    Klaim hadiah
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs font-semibold text-stone-400">
            Belum punya kode? <a href="{{ route('products.index') }}" class="font-extrabold text-amber-600 hover:underline">Jelajahi katalog</a> atau ikuti promo di media sosial kami.
        </p>
    </section>
@endsection
