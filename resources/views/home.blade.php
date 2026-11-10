@extends('layouts.storefront')

@section('title', 'Mamikhevanshop — Pakaian Anak Lucu, Nyaman & Terjangkau')

@section('content')
    @php
        $heroImages = [
            ['src' => 'images/products/dress.svg', 'name' => 'Dress Bunga Cherry', 'price' => 'Rp149.000', 'class' => 'left-0 top-8 w-40 rotate-[-6deg] sm:w-48', 'label' => 'Best seller'],
            ['src' => 'images/products/tshirt.svg', 'name' => 'Kaos Little Dino', 'price' => 'Rp89.000', 'class' => 'right-0 top-0 w-36 rotate-[7deg] sm:w-44', 'label' => 'Favorit'],
            ['src' => 'images/products/onesie.svg', 'name' => 'Onesie Cloud Soft', 'price' => 'Rp129.000', 'class' => 'bottom-0 left-1/2 w-40 -translate-x-1/2 rotate-[3deg] sm:w-52', 'label' => 'Newborn'],
        ];
    @endphp

    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute -left-24 top-10 h-72 w-72 rounded-full bg-amber-200/40 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-20 top-40 h-80 w-80 rounded-full bg-amber-200/40 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 left-1/3 h-64 w-64 rounded-full bg-teal-200/30 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-14 px-4 pb-16 pt-14 sm:px-6 lg:grid-cols-2 lg:gap-8 lg:px-8 lg:pb-24 lg:pt-20">
            <div class="text-center lg:text-left">
                <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-white px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-amber-600 shadow-sm">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-amber-400"></span>
                    Koleksi baru 2026
                </span>

                <h1 class="mt-6 font-display text-4xl font-extrabold leading-[1.1] tracking-tight text-stone-900 text-balance sm:text-5xl lg:text-6xl">
                    Baju anak
                    <span class="relative inline-block text-amber-600">
                        lucu
                        <svg class="absolute -bottom-2 left-0 w-full" height="12" viewBox="0 0 200 12" fill="none" preserveAspectRatio="none" aria-hidden="true">
                            <path d="M2 9.5C40 3.5 92 2.5 198 6.5" stroke="#FCD34D" stroke-width="5" stroke-linecap="round"/>
                        </svg>
                    </span>,
                    nyaman,
                    <br class="hidden sm:block"> siap dipakai.
                </h1>

                <p class="mx-auto mt-6 max-w-xl text-base leading-8 text-stone-500 sm:text-lg lg:mx-0">
                    Katalog pakaian anak dengan bahan lembut bersertifikat, jahitan aman, dan desain ceria. Temukan ukuran yang pas untuk si kecil tanpa ribet.
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

            <div class="relative mx-auto h-[420px] w-full max-w-lg sm:h-[480px] lg:h-[540px]" aria-hidden="true">
                <div class="absolute inset-8 rounded-[3rem] bg-gradient-to-br from-amber-200 via-yellow-100 to-amber-100 opacity-80 blur-sm"></div>

                @foreach ($heroImages as $image)
                    <div class="absolute {{ $image['class'] }} overflow-hidden rounded-[2rem] border border-white/80 bg-white p-2 shadow-2xl shadow-amber-200/50 transition duration-500 hover:rotate-0 hover:scale-[1.03]">
                        <div class="overflow-hidden rounded-[1.6rem]">
                            <img src="{{ asset($image['src']) }}" alt="{{ $image['name'] }}" class="aspect-square w-full object-cover">
                        </div>
                        <div class="flex items-center justify-between gap-2 px-2 pb-1 pt-3">
                            <div>
                                <p class="text-[9px] font-extrabold uppercase tracking-wider text-amber-400">{{ $image['label'] }}</p>
                                <p class="font-display text-sm font-bold leading-tight text-stone-800">{{ $image['name'] }}</p>
                            </div>
                            <p class="text-xs font-extrabold text-amber-600">{{ $image['price'] }}</p>
                        </div>
                    </div>
                @endforeach

                <div class="absolute right-2 top-1/2 z-10 animate-float rounded-2xl border border-yellow-100 bg-white px-4 py-3 shadow-xl sm:right-0">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Bahan aman</p>
                    <p class="font-display text-sm font-bold text-stone-800">OEKO-TEX&reg;</p>
                </div>

                <div class="absolute bottom-16 left-0 z-10 animate-float-slow rounded-2xl border border-yellow-100 bg-white px-4 py-3 shadow-xl">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Pengiriman</p>
                    <p class="font-display text-sm font-bold text-stone-800">1-3 hari kerja</p>
                </div>
            </div>
        </div>
    </section>

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

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-stone-900 via-stone-800 to-amber-900 px-6 py-12 sm:px-12 lg:px-16">
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-amber-500/30 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-1/3 h-72 w-72 rounded-full bg-yellow-400/20 blur-2xl"></div>

            <div class="relative grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <span class="inline-flex rounded-full bg-white/10 px-4 py-1.5 text-xs font-extrabold uppercase tracking-[0.2em] text-amber-200">Paket hemat</span>
                    <h2 class="mt-4 font-display text-3xl font-extrabold leading-tight text-white sm:text-4xl">
                        Belanja Rp300.000,<br>ongkir kami yang tanggung.
                    </h2>
                    <p class="mt-4 max-w-md text-sm leading-7 text-stone-300">
                        Isi lemari si kecil sekaligus. Gratis ongkir otomatis untuk semua pesanan mulai Rp300.000, tanpa kode promo.
                    </p>
                    <a href="{{ route('products.index') }}"
                       class="mt-7 inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-extrabold text-stone-900 transition hover:-translate-y-0.5 hover:bg-amber-50">
                        Mulai belanja
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    @foreach ($newestProducts->take(2) as $product)
                        <div class="overflow-hidden rounded-3xl border border-white/15 bg-white/10 p-2 backdrop-blur">
                            <div class="aspect-square overflow-hidden rounded-2xl">
                                <x-store.product-image :product="$product" />
                            </div>
                            <p class="truncate px-1 pt-2 text-xs font-bold text-white">{{ $product->name }}</p>
                            <p class="px-1 pb-1 text-xs font-extrabold text-amber-200">{{ $product->price_formatted }}</p>
                        </div>
                    @endforeach
                    @for ($i = $newestProducts->take(2)->count(); $i < 2; $i++)
                        <div class="grid aspect-square place-items-center rounded-3xl border border-dashed border-white/20 bg-white/5"></div>
                    @endfor
                </div>
            </div>
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
                <h3 class="mt-4 font-display text-xl font-bold text-stone-900">Mudah tukar ukuran</h3>
                <p class="mt-2 text-sm leading-7 text-stone-500">Salah ukuran? Bisa tukar dalam 3 hari setelah paket diterima.</p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-amber-400">Kata orang tua</p>
            <h2 class="mt-2 font-display text-3xl font-extrabold text-stone-900 sm:text-4xl">Mereka sudah mencoba</h2>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3 sm:gap-5">
            @foreach ([
                ['name' => 'Rina Kusuma', 'city' => 'Bandung', 'text' => 'Bahan kaosnya benar-benar lembut dan tidak gerah. Anak saya langsung mau pakai setiap hari. Packing rapi dan bonus stiker lucu!', 'color' => 'bg-amber-100 text-yellow-700'],
                ['name' => 'Budi Santoso', 'city' => 'Jakarta', 'text' => 'Pesan dress untuk ulang tahun anak, ukurannya sesuai panduan. Adminnya cepat balas WhatsApp dan membantu pilih ukuran.', 'color' => 'bg-amber-100 text-yellow-700'],
                ['name' => 'Sari Ayu', 'city' => 'Malang', 'text' => 'Sudah tiga kali belanja di sini. Kualitas konsisten, harga masuk akal, dan pengiriman selalu lebih cepat dari perkiraan.', 'color' => 'bg-teal-100 text-teal-600'],
            ] as $testimonial)
                <figure class="rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-0.5 text-amber-400">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.539 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.98 10.1c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69l1.519-4.674Z"/></svg>
                        @endfor
                    </div>
                    <blockquote class="mt-4 text-sm leading-7 text-stone-600">&ldquo;{{ $testimonial['text'] }}&rdquo;</blockquote>
                    <figcaption class="mt-5 flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-full text-xs font-extrabold {{ $testimonial['color'] }}">
                            {{ strtoupper(substr($testimonial['name'], 0, 1)) }}
                        </span>
                        <div>
                            <p class="text-sm font-extrabold text-stone-900">{{ $testimonial['name'] }}</p>
                            <p class="text-xs font-semibold text-stone-400">{{ $testimonial['city'] }}</p>
                        </div>
                    </figcaption>
                </figure>
            @endforeach
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
