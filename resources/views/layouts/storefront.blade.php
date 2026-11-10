@php
    $cartCount = app(\App\Services\CartService::class)->count();
    $navLinks = [
        ['label' => 'Beranda', 'route' => 'home'],
        ['label' => 'Katalog', 'route' => 'products.index'],
        ['label' => 'Tentang', 'route' => 'about'],
        ['label' => 'Kontak', 'route' => 'contact.index'],
    ];
    $footerCategories = [
        'Bayi & Newborn' => 'bayi-newborn',
        'Atasan' => 'atasan',
        'Bawahan' => 'bawahan',
        'Dress & Rok' => 'dress-rok',
        'Jaket & Outer' => 'jaket-outer',
        'Sepatu & Aksesori' => 'sepatu-aksesori',
    ];
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F59E0B">
    <meta name="description" content="@yield('meta_description', 'Mamikhevanshop adalah katalog pakaian anak dengan bahan lembut, desain lucu, dan harga bersahabat. Belanja online dengan COD atau transfer.')">

    <title>@yield('title', 'Mamikhevanshop — Pakaian Anak Lucu &amp; Nyaman')</title>

    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E%F0%9F%91%95%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#FFFDF4] font-sans text-stone-800 antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[80] focus:rounded-xl focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-yellow-700 focus:shadow-lg">
        Lewati ke konten
    </a>

    <header x-data="{ open: false }" class="sticky top-0 z-50">
        <div class="bg-gradient-to-r from-amber-600 via-amber-500 to-yellow-500 text-white">
            <div class="mx-auto flex max-w-7xl items-center justify-center gap-2 px-4 py-2 text-center text-[11px] font-bold uppercase tracking-[0.18em] sm:text-xs">
                <svg class="h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                <span>Gratis ongkir untuk belanja min. Rp300.000 &middot; Bisa COD</span>
            </div>
        </div>

        <div class="border-b border-yellow-100/80 bg-[#FFFDF4]/95 backdrop-blur">
            <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="group flex flex-none items-center gap-2.5">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-400 text-amber-950 shadow-soft transition group-hover:scale-105">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75 4.5 6l1.875 4.5L9 9.75V21h6V9.75l2.625.75L19.5 6 15 3.75a3 3 0 0 1-6 0Z" /></svg>
                    </span>
                    <span class="font-display text-2xl font-bold tracking-tight text-stone-900">Mamikhevan<span class="text-amber-600">Shop</span></span>
                </a>

                <nav class="hidden items-center gap-1 lg:flex">
                    @foreach ($navLinks as $link)
                        <a href="{{ route($link['route']) }}"
                           class="rounded-full px-4 py-2 text-sm font-bold transition {{ request()->routeIs($link['route']) ? 'bg-amber-100 text-yellow-700' : 'text-stone-600 hover:bg-yellow-50 hover:text-yellow-700' }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-1.5 sm:gap-2">
                    <form action="{{ route('products.index') }}" method="GET" class="relative hidden xl:block">
                        <input type="search" name="q" value="{{ request()->routeIs('products.index') ? request('q') : '' }}"
                               placeholder="Cari baju anak..."
                               class="w-52 rounded-full border-yellow-200 bg-white py-2 pl-10 pr-4 text-sm placeholder:text-stone-400 focus:border-amber-300 focus:ring-amber-200">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    </form>

                    <a href="{{ route('cart.index') }}" class="relative grid h-11 w-11 place-items-center rounded-full border border-yellow-100 bg-white text-stone-600 transition hover:border-amber-200 hover:text-amber-600" aria-label="Keranjang belanja">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
                        @if ($cartCount > 0)
                            <span class="absolute -right-0.5 -top-0.5 grid h-5 min-w-5 place-items-center rounded-full bg-amber-500 px-1 text-[10px] font-extrabold text-white ring-2 ring-[#FFFDF4]">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                        @endif
                    </a>

                    @auth
                        <a href="{{ auth()->user()->is_admin ? route('admin.dashboard') : route('dashboard') }}"
                           class="hidden items-center gap-2 rounded-full bg-stone-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-stone-700 sm:inline-flex">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="hidden items-center gap-2 rounded-full bg-stone-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-stone-700 sm:inline-flex">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                            Masuk Admin
                        </a>
                    @endauth

                    <button type="button" @click="open = !open"
                            class="grid h-11 w-11 place-items-center rounded-full border border-yellow-100 bg-white text-stone-600 lg:hidden"
                            :aria-expanded="open" aria-label="Buka menu">
                        <svg x-show="!open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                        <svg x-show="open" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <div x-show="open" x-cloak x-transition class="border-t border-yellow-100 bg-white px-4 py-4 lg:hidden">
                <form action="{{ route('products.index') }}" method="GET" class="relative mb-3">
                    <input type="search" name="q" value="{{ request()->routeIs('products.index') ? request('q') : '' }}"
                           placeholder="Cari baju anak..."
                           class="w-full rounded-full border-yellow-200 py-2.5 pl-10 pr-4 text-sm focus:border-amber-300 focus:ring-amber-200">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </form>
                <nav class="grid gap-1">
                    @foreach ($navLinks as $link)
                        <a href="{{ route($link['route']) }}"
                           class="rounded-2xl px-4 py-3 text-sm font-bold {{ request()->routeIs($link['route']) ? 'bg-amber-50 text-yellow-700' : 'text-stone-600 hover:bg-yellow-50' }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                    <a href="{{ route('cart.index') }}" class="rounded-2xl px-4 py-3 text-sm font-bold text-stone-600 hover:bg-yellow-50">
                        Keranjang ({{ $cartCount }})
                    </a>
                    @auth
                        <a href="{{ auth()->user()->is_admin ? route('admin.dashboard') : route('dashboard') }}" class="rounded-2xl bg-stone-900 px-4 py-3 text-center text-sm font-bold text-white">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-2xl bg-stone-900 px-4 py-3 text-center text-sm font-bold text-white">
                            Masuk Admin
                        </a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <main id="main-content">
        @yield('content')
    </main>

    <footer class="mt-20 border-t border-yellow-100 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-[1.4fr_1fr_1fr_1.1fr]">
                <div>
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-400 text-amber-950 shadow-soft">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75 4.5 6l1.875 4.5L9 9.75V21h6V9.75l2.625.75L19.5 6 15 3.75a3 3 0 0 1-6 0Z" /></svg>
                        </span>
                        <span class="font-display text-2xl font-bold text-stone-900">Mamikhevan<span class="text-amber-600">Shop</span></span>
                    </a>
                    <p class="mt-4 max-w-sm text-sm leading-7 text-stone-500">
                        Katalog pakaian anak pilihan dengan bahan lembut, jahitan aman, dan desain ceria. Dibuat untuk kenyamanan si kecil dan ketenangan orang tua.
                    </p>
                    <div class="mt-5 flex items-center gap-2">
                        <span class="rounded-full bg-yellow-50 px-3 py-1.5 text-xs font-bold text-yellow-600">COD</span>
                        <span class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-yellow-700">Transfer Bank</span>
                        <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-600">QRIS</span>
                    </div>
                </div>

                <div>
                    <h3 class="font-display text-lg font-bold text-stone-900">Kategori</h3>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @foreach ($footerCategories as $label => $slug)
                            <li>
                                <a href="{{ route('products.index', ['category' => $slug]) }}" class="text-stone-500 transition hover:text-amber-600">{{ $label }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h3 class="font-display text-lg font-bold text-stone-900">Bantuan</h3>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li><a href="{{ route('about') }}" class="text-stone-500 transition hover:text-amber-600">Tentang Kami</a></li>
                        <li><a href="{{ route('contact.index') }}" class="text-stone-500 transition hover:text-amber-600">Hubungi Kami</a></li>
                        <li><a href="{{ route('products.index') }}" class="text-stone-500 transition hover:text-amber-600">Cara Belanja</a></li>
                        <li><a href="{{ route('login') }}" class="text-stone-500 transition hover:text-amber-600">Masuk Admin</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-display text-lg font-bold text-stone-900">Hubungi Kami</h3>
                    <ul class="mt-4 space-y-3 text-sm text-stone-500">
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 flex-none text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                            <span>Jl. Ceria Anak No. 17, Bandung, Jawa Barat</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 flex-none text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                            <span>0812-3456-7890 (WhatsApp)</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 flex-none text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                            <span>halo@mamikhevanshop.id</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-yellow-100 pt-6 text-xs text-stone-400 sm:flex-row">
                <p>&copy; {{ date('Y') }} Mamikhevanshop. Dibuat dengan cinta untuk si kecil.</p>
                <p>Laravel 13 &middot; Blade &middot; Tailwind CSS</p>
            </div>
        </div>
    </footer>

    <a href="https://wa.me/6281234567890?text=Halo%20Mamikhevanshop%2C%20saya%20ingin%20bertanya%20tentang%20produk%20pakaian%20anak."
       target="_blank" rel="noopener noreferrer"
       class="fixed bottom-5 right-5 z-40 inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-3 text-sm font-bold text-white shadow-xl shadow-emerald-500/30 transition hover:-translate-y-0.5 hover:bg-emerald-600"
       aria-label="Hubungi kami via WhatsApp">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.1-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
        <span class="hidden sm:inline">Tanya via WhatsApp</span>
    </a>

    @if (session('success') || session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition
             class="fixed inset-x-4 bottom-4 z-[70] sm:inset-x-auto sm:bottom-6 sm:right-6 sm:w-96">
            <div class="flex items-start gap-3 rounded-2xl border p-4 shadow-2xl {{ session('error') ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}">
                <svg class="mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    @if (session('error'))
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    @endif
                </svg>
                <p class="flex-1 text-sm font-semibold leading-6">{{ session('error') ?? session('success') }}</p>
                <button type="button" @click="show = false" class="rounded-full p-1 transition hover:bg-black/5" aria-label="Tutup notifikasi">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>
    @endif
</body>
</html>
