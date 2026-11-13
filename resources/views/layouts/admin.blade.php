@php
    $unreadMessages = \App\Models\ContactMessage::query()->where('is_read', false)->count();
    $pendingOrders = \App\Models\Order::query()
        ->where('status', \App\Models\Order::STATUS_PENDING)
        ->count();
    $adminNav = [
        [
            'label' => 'Dashboard',
            'route' => 'admin.dashboard',
            'active' => 'admin.dashboard',
            'icon' => 'dashboard',
            'badge' => null,
            'roles' => ['administrator', 'management'],
        ],
        [
            'label' => 'Produk',
            'route' => 'admin.products.index',
            'active' => 'admin.products.*',
            'icon' => 'products',
            'badge' => null,
            'roles' => ['administrator', 'management', 'operator'],
        ],
        [
            'label' => 'Opname Stok',
            'route' => 'admin.stock-opname.index',
            'active' => 'admin.stock-opname.*',
            'icon' => 'products',
            'badge' => null,
            'roles' => ['administrator', 'management', 'operator'],
        ],
        [
            'label' => 'Kategori',
            'route' => 'admin.categories.index',
            'active' => 'admin.categories.*',
            'icon' => 'categories',
            'badge' => null,
            'roles' => ['administrator', 'management', 'operator'],
        ],
        [
            'label' => 'Promo',
            'route' => 'admin.promotions.index',
            'active' => 'admin.promotions.*',
            'icon' => 'products',
            'badge' => null,
            'roles' => ['administrator', 'management'],
        ],
        [
            'label' => 'Rating',
            'route' => 'admin.reviews.index',
            'active' => 'admin.reviews.*',
            'icon' => 'reviews',
            'badge' => null,
            'roles' => ['administrator', 'management', 'operator'],
        ],
        [
            'label' => 'Banner Beranda',
            'route' => 'admin.home-banner.edit',
            'active' => 'admin.home-banner.*',
            'icon' => 'products',
            'badge' => null,
            'roles' => ['administrator', 'management', 'operator'],
        ],
        [
            'label' => 'Panduan Ukuran',
            'route' => 'admin.size-guides.index',
            'active' => 'admin.size-guides.*',
            'icon' => 'size-guide',
            'badge' => null,
            'roles' => ['administrator', 'management', 'operator'],
        ],
        [
            'label' => 'Pesanan',
            'route' => 'admin.orders.index',
            'active' => 'admin.orders.*',
            'icon' => 'orders',
            'badge' => $pendingOrders ?: null,
            'roles' => ['administrator', 'management'],
        ],
        [
            'label' => 'Keuangan',
            'route' => 'admin.finance.index',
            'active' => 'admin.finance.*',
            'icon' => 'orders',
            'badge' => null,
            'roles' => ['administrator', 'management'],
        ],
        [
            'label' => 'Pembayaran',
            'route' => 'admin.payment-settings.edit',
            'active' => 'admin.payment-settings.*',
            'icon' => 'messages',
            'badge' => null,
            'roles' => ['administrator', 'management'],
        ],
        [
            'label' => 'Pesan',
            'route' => 'admin.messages.index',
            'active' => 'admin.messages.*',
            'icon' => 'messages',
            'badge' => $unreadMessages ?: null,
            'roles' => ['administrator', 'management', 'operator'],
        ],
        [
            'label' => 'Manajemen User',
            'route' => 'admin.users.index',
            'active' => 'admin.users.*',
            'icon' => 'users',
            'badge' => null,
            'roles' => ['administrator'],
        ],
        [
            'label' => 'Audit Trail',
            'route' => 'admin.audit-trail.index',
            'active' => 'admin.audit-trail.*',
            'icon' => 'audit',
            'badge' => null,
            'roles' => ['administrator'],
        ],
    ];
    $adminNav = array_values(array_filter($adminNav, fn (array $item): bool => in_array(auth()->user()->role, $item['roles'], true)));
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin Panel') — Mamikhevanshop</title>

    <link rel="icon"
          href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E%F0%9F%91%95%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Nunito:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#FAF6EA] font-sans text-stone-800 antialiased"
      x-data="{ sidebar: false, userMenu: false }">
    <div x-show="sidebar" x-cloak x-transition.opacity @click="sidebar = false"
         class="fixed inset-0 z-40 bg-stone-900/40 backdrop-blur-sm lg:hidden"></div>

    <aside x-cloak :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-stone-200 bg-white transition-transform duration-300 lg:translate-x-0">
        <div
             class="flex h-20 flex-none items-center justify-between gap-2 border-b border-stone-100 px-5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                <span
                      class="grid h-10 w-10 place-items-center rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-400 text-amber-950 shadow-soft">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 3.75 4.5 6l1.875 4.5L9 9.75V21h6V9.75l2.625.75L19.5 6 15 3.75a3 3 0 0 1-6 0Z" />
                    </svg>
                </span>
                <span>
                    <span
                          class="block font-display text-lg font-bold leading-none text-stone-900">Mamikhevan<span
                              class="text-amber-600">Shop</span></span>
                    <span
                          class="mt-1 block text-[10px] font-extrabold uppercase tracking-[0.2em] text-stone-400">Admin
                        panel</span>
                </span>
            </a>
            <button type="button" @click="sidebar = false"
                    class="grid h-9 w-9 place-items-center rounded-full text-stone-400 hover:bg-stone-100 lg:hidden"
                    aria-label="Tutup menu">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5">
            <p
               class="px-3 pb-2 text-[10px] font-extrabold uppercase tracking-[0.2em] text-stone-400">
                Menu utama</p>
            @foreach ($adminNav as $item)
                <a href="{{ route($item['route']) }}"
                   class="{{ request()->routeIs($item['active']) ? 'bg-amber-50 text-yellow-700' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-900' }} group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold transition">
                    <span
                          class="{{ request()->routeIs($item['active']) ? 'bg-amber-500 text-white shadow-sm' : 'bg-stone-100 text-stone-500 group-hover:bg-white' }} grid h-8 w-8 place-items-center rounded-xl">
                        @switch($item['icon'])
                            @case('dashboard')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                </svg>
                            @break

                            @case('products')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M6 6h.008v.008H6V6Z" />
                                </svg>
                            @break

                            @case('categories')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75ZM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-2.25Z" />
                                </svg>
                            @break

                            @case('size-guide')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                                </svg>
                            @break

                            @case('reviews')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                </svg>
                            @break

                            @case('orders')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                </svg>
                            @break

                            @case('messages')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                            @break

                            @case('users')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.75-.728 3.375 3.375 0 0 0-3.75-3.192m-3.75 3.92a9.094 9.094 0 0 1-3.75-.728 3.375 3.375 0 0 1 3.75-3.192m0 3.92v.003M12 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm6 0a2.25 2.25 0 1 0 0-4.5A2.25 2.25 0 0 0 18 12Z" />
                                </svg>
                            @break

                            @case('audit')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5 6v5c0 4.2 2.9 8.1 7 9.5 4.1-1.4 7-5.3 7-9.5V6l-7-3Zm-3.5 9 2.2 2.2 4.8-5" />
                                </svg>
                            @break
                        @endswitch
                    </span>
                    <span class="flex-1">{{ $item['label'] }}</span>
                    @if ($item['badge'])
                        <span
                              class="grid h-5 min-w-5 place-items-center rounded-full bg-amber-500 px-1.5 text-[10px] font-extrabold text-white">{{ $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach

            <div class="my-4 border-t border-stone-100"></div>
            <p
               class="px-3 pb-2 text-[10px] font-extrabold uppercase tracking-[0.2em] text-stone-400">
                Lainnya</p>
            <a href="{{ route('home') }}" target="_blank" rel="noopener"
               class="group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold text-stone-500 transition hover:bg-stone-50 hover:text-stone-900">
                <span
                      class="grid h-8 w-8 place-items-center rounded-xl bg-stone-100 text-stone-500 group-hover:bg-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </span>
                <span class="flex-1">Lihat toko</span>
            </a>
            <a href="{{ route('profile.edit') }}"
               class="group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold text-stone-500 transition hover:bg-stone-50 hover:text-stone-900">
                <span
                      class="grid h-8 w-8 place-items-center rounded-xl bg-stone-100 text-stone-500 group-hover:bg-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </span>
                <span class="flex-1">Profil saya</span>
            </a>
        </nav>

        <div class="flex-none border-t border-stone-100 p-4">
            <div class="rounded-2xl bg-gradient-to-br from-yellow-50 to-amber-50 p-4">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Masuk
                    sebagai</p>
                <p class="mt-1 truncate text-sm font-extrabold text-stone-900">
                    {{ auth()->user()->name }}</p>
                <p class="truncate text-xs font-semibold text-stone-500">
                    {{ auth()->user()->email }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-extrabold text-stone-600 shadow-sm transition hover:text-amber-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-30 border-b border-yellow-100 bg-[#FAF6EA]/90 backdrop-blur">
            <div class="flex h-20 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" @click="sidebar = true"
                            class="grid h-10 w-10 flex-none place-items-center rounded-xl border border-stone-200 bg-white text-stone-500 lg:hidden"
                            aria-label="Buka menu">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <h1
                            class="truncate font-display text-xl font-extrabold text-stone-900 sm:text-2xl">
                            @yield('heading', 'Dashboard')</h1>
                        <p class="hidden truncate text-xs font-semibold text-stone-400 sm:block">
                            @yield('subheading', 'Selamat datang kembali di panel admin Mamikhevanshop.')</p>
                    </div>
                </div>

                <div class="flex flex-none items-center gap-2">
                    <a href="{{ route('home') }}" target="_blank" rel="noopener"
                       class="hidden items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2 text-xs font-extrabold text-stone-600 transition hover:border-amber-200 hover:text-amber-600 sm:inline-flex">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                        Lihat toko
                    </a>

                    <div class="relative">
                        <button type="button" @click="userMenu = !userMenu"
                                class="flex items-center gap-2 rounded-xl border border-stone-200 bg-white py-1.5 pl-1.5 pr-3 transition hover:border-amber-200"
                                :aria-expanded="userMenu">
                            <span
                                  class="grid h-8 w-8 place-items-center rounded-lg bg-gradient-to-br from-amber-400 to-yellow-400 text-xs font-extrabold text-amber-950">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden text-left sm:block">
                                <span
                                      class="block max-w-32 truncate text-xs font-extrabold text-stone-800">{{ auth()->user()->name }}</span>
                                <span
                                      class="block text-[10px] font-bold uppercase tracking-wider text-amber-400">{{ auth()->user()->roleLabel() }}</span>
                            </span>
                            <svg class="h-4 w-4 text-stone-400" fill="none"
                                 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div x-show="userMenu" x-cloak @click.outside="userMenu = false"
                             x-transition
                             class="absolute right-0 top-full z-50 mt-2 w-56 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-xl">
                            <div class="border-b border-stone-100 px-4 py-3">
                                <p class="truncate text-sm font-extrabold text-stone-900">
                                    {{ auth()->user()->name }}</p>
                                <p class="truncate text-xs font-semibold text-stone-400">
                                    {{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}"
                               class="block px-4 py-2.5 text-sm font-bold text-stone-600 transition hover:bg-stone-50">Profil
                                saya</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full px-4 py-2.5 text-left text-sm font-bold text-amber-600 transition hover:bg-amber-50">Keluar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            @yield('content')
        </main>

        <footer
                class="px-4 pb-8 text-center text-[11px] font-semibold text-stone-400 sm:px-6 lg:px-8">
            Mamikhevanshop Admin &copy; {{ date('Y') }} &middot; Laravel
            {{ app()->version() }}
        </footer>
    </div>

    @if (session('success') || session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition
             class="fixed inset-x-4 bottom-4 z-[70] sm:inset-x-auto sm:bottom-6 sm:right-6 sm:w-96">
            <div
                 class="{{ session('error') ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }} flex items-start gap-3 rounded-2xl border p-4 shadow-2xl">
                <svg class="mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24"
                     stroke-width="2" stroke="currentColor">
                    @if (session('error'))
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    @endif
                </svg>
                <p class="flex-1 text-sm font-semibold leading-6">
                    {{ session('error') ?? session('success') }}</p>
                <button type="button" @click="show = false"
                        class="rounded-full p-1 transition hover:bg-black/5" aria-label="Tutup">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @stack('scripts')
</body>

</html>
