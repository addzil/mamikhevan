@extends('layouts.storefront')

@section('title', 'Tentang Mamikhevanshop — Pakaian Anak yang Nyaman')
@section('meta_description', 'Kenali Mamikhevanshop: toko katalog pakaian anak yang mengutamakan bahan aman, kenyamanan, dan desain ceria untuk si kecil.')

@section('content')
    <section class="relative overflow-hidden border-b border-yellow-100 bg-gradient-to-b from-yellow-50/80 to-[#FFFDF4]">
        <div class="pointer-events-none absolute -left-24 top-0 h-72 w-72 rounded-full bg-amber-200/40 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-16 bottom-0 h-64 w-64 rounded-full bg-amber-200/40 blur-3xl"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
            <div class="grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <span class="inline-flex rounded-full border border-amber-200 bg-white px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-amber-600 shadow-sm">Cerita kami</span>
                    <h1 class="mt-5 font-display text-3xl font-extrabold leading-tight text-stone-900 sm:text-5xl">
                        Pakaian anak yang<br><span class="text-amber-600">nyaman</span> untuk bermain.
                    </h1>
                    <p class="mt-5 max-w-xl text-sm leading-8 text-stone-500 sm:text-base">
                        Mamikhevanshop lahir dari pengalaman sederhana: mencari pakaian anak yang benar-benar nyaman, aman, dan tidak membuat dompet menangis. Kami memilih bahan terbaik, menguji jahitan, dan merancang warna yang ceria agar si kecil bebas bergerak.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-amber-500 px-6 py-3.5 text-sm font-extrabold text-white shadow-soft transition hover:bg-amber-600">
                            Lihat koleksi
                        </a>
                        <a href="{{ route('contact.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-yellow-200 bg-white px-6 py-3.5 text-sm font-extrabold text-stone-700 transition hover:border-amber-200 hover:text-amber-600">
                            Hubungi kami
                        </a>
                    </div>
                </div>

                <div class="relative">
                    <div class="grid grid-cols-2 gap-4">
                        <img src="{{ asset('images/products/dress.svg') }}" alt="Ilustrasi dress anak" class="aspect-square w-full rounded-[2rem] border border-white object-cover shadow-xl shadow-amber-100">
                        <img src="{{ asset('images/products/tshirt.svg') }}" alt="Ilustrasi kaos anak" class="mt-8 aspect-square w-full rounded-[2rem] border border-white object-cover shadow-xl shadow-amber-100">
                        <img src="{{ asset('images/products/onesie.svg') }}" alt="Ilustrasi onesie bayi" class="-mt-4 aspect-square w-full rounded-[2rem] border border-white object-cover shadow-xl shadow-amber-100">
                        <img src="{{ asset('images/products/shoes.svg') }}" alt="Ilustrasi sepatu anak" class="mt-4 aspect-square w-full rounded-[2rem] border border-white object-cover shadow-xl shadow-amber-100">
                    </div>
                    <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 rounded-2xl border border-yellow-100 bg-white px-5 py-3 text-center shadow-xl">
                        <p class="font-display text-2xl font-extrabold text-amber-600">1.200+</p>
                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">orang tua bahagia</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['value' => '1.200+', 'label' => 'Pesanan terkirim', 'color' => 'text-amber-600'],
                ['value' => '4,9/5', 'label' => 'Rating pelanggan', 'color' => 'text-amber-600'],
                ['value' => '6', 'label' => 'Kategori produk', 'color' => 'text-teal-500'],
                ['value' => '< 24 jam', 'label' => 'Respons admin', 'color' => 'text-sky-500'],
            ] as $stat)
                <div class="rounded-3xl border border-yellow-100 bg-white p-6 text-center shadow-sm">
                    <p class="font-display text-3xl font-extrabold {{ $stat['color'] }}">{{ $stat['value'] }}</p>
                    <p class="mt-1 text-xs font-extrabold uppercase tracking-wider text-stone-400">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-amber-400">Yang kami pegang</p>
            <h2 class="mt-2 font-display text-3xl font-extrabold text-stone-900 sm:text-4xl">Tiga janji Mamikhevanshop</h2>
        </div>

        <div class="mt-8 grid gap-5 md:grid-cols-3">
            <div class="rounded-3xl border border-yellow-100 bg-white p-7 shadow-sm">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-50 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                </span>
                <h3 class="mt-4 font-display text-xl font-bold text-stone-900">Aman untuk kulit anak</h3>
                <p class="mt-2 text-sm leading-7 text-stone-500">Kami memilih katun organik dan pewarna ramah anak yang sudah lolos uji bahan.</p>
            </div>
            <div class="rounded-3xl border border-yellow-100 bg-white p-7 shadow-sm">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-50 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </span>
                <h3 class="mt-4 font-display text-xl font-bold text-stone-900">Kualitas yang dicek satu per satu</h3>
                <p class="mt-2 text-sm leading-7 text-stone-500">Setiap produk melewati pengecekan jahitan, warna, dan ukuran sebelum dikirim.</p>
            </div>
            <div class="rounded-3xl border border-yellow-100 bg-white p-7 shadow-sm">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-teal-50 text-teal-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                </span>
                <h3 class="mt-4 font-display text-xl font-bold text-stone-900">Dibantu manusia, bukan bot</h3>
                <p class="mt-2 text-sm leading-7 text-stone-500">Tim kami siap membantu memilih ukuran lewat WhatsApp atau form kontak.</p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="rounded-[2.5rem] border border-yellow-100 bg-gradient-to-r from-yellow-50 via-amber-50 to-amber-50 px-6 py-12 text-center sm:px-12">
            <h2 class="font-display text-3xl font-extrabold text-stone-900">Siap cari pakaian baru si kecil?</h2>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-stone-500">Jelajahi koleksi kami dan temukan yang paling cocok untuk gaya dan aktivitasnya.</p>
            <a href="{{ route('products.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-full bg-amber-500 px-7 py-3.5 text-sm font-extrabold text-white shadow-soft transition hover:bg-amber-600">
                Mulai belanja
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>
    </section>
@endsection
