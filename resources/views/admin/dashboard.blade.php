@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('heading', 'Dashboard')
@section('subheading', 'Ringkasan performa toko dan aktivitas terbaru.')

@section('content')
    @php
        $cards = [
            [
                'label' => 'Nilai pesanan aktif',
                'value' => 'Rp'.number_format($stats['revenue'], 0, ',', '.'),
                'hint' => $stats['orders'].' pesanan, tidak termasuk yang dibatalkan',
                'icon' => 'revenue',
                'tone' => 'bg-amber-50 text-yellow-700',
            ],
            [
                'label' => 'Margin produk',
                'value' => 'Rp'.number_format($stats['margin'], 0, ',', '.'),
                'hint' => 'Penjualan bersih produk - modal tersimpan',
                'icon' => 'margin',
                'tone' => 'bg-emerald-50 text-emerald-500',
            ],
            [
                'label' => 'Persentase margin',
                'value' => number_format($stats['margin_percent'], 1, ',', '.').'%',
                'hint' => 'Dari penjualan bersih produk',
                'icon' => 'percent',
                'tone' => 'bg-violet-50 text-violet-500',
            ],
            [
                'label' => 'Pesanan baru',
                'value' => number_format($stats['pending_orders'], 0, ',', '.'),
                'hint' => 'Menunggu konfirmasi admin',
                'icon' => 'clock',
                'tone' => 'bg-amber-50 text-yellow-700',
            ],
            [
                'label' => 'Produk aktif',
                'value' => number_format($stats['products'], 0, ',', '.'),
                'hint' => $stats['customers'].' pelanggan dari pesanan aktif',
                'icon' => 'box',
                'tone' => 'bg-sky-50 text-sky-500',
            ],
            [
                'label' => 'Pesan belum dibaca',
                'value' => number_format($stats['unread_messages'], 0, ',', '.'),
                'hint' => 'Dari form kontak',
                'icon' => 'mail',
                'tone' => 'bg-emerald-50 text-emerald-500',
            ],
        ];
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($cards as $card)
            <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-stone-400">{{ $card['label'] }}</p>
                        <p class="mt-2 font-display text-2xl font-extrabold leading-none text-stone-900">{{ $card['value'] }}</p>
                    </div>
                    <span class="grid h-11 w-11 flex-none place-items-center rounded-2xl {{ $card['tone'] }}">
                        @switch($card['icon'])
                            @case('revenue')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                @break
                            @case('margin')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>
                                @break
                            @case('percent')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 4.5 4.5 19.5M6.75 6.75h.008v.008H6.75V6.75Zm10.5 10.5h.008v.008h-.008v-.008Z" /></svg>
                                @break
                            @case('clock')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                @break
                            @case('box')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                                @break
                            @case('mail')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                                @break
                        @endswitch
                    </span>
                </div>
                <p class="mt-3 text-xs font-semibold text-stone-400">{{ $card['hint'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-amber-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Tambah produk
        </a>
        <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-xs font-extrabold text-stone-600 transition hover:border-amber-200 hover:text-yellow-700">
            Tambah kategori
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="inline-flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-xs font-extrabold text-stone-600 transition hover:border-amber-200 hover:text-yellow-700">
            Pesanan menunggu
        </a>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <p class="xl:col-span-3 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-xs font-semibold leading-5 text-amber-800">
            Nilai pesanan memakai total order tersimpan (termasuk ongkir dan kode unik) serta mencakup transfer yang menunggu pembayaran. Margin produk mengurangi diskon dan memasukkan modal hadiah promo, tetapi belum mencakup ongkir ekspedisi, biaya pembayaran, atau biaya operasional. Pesanan lama sebelum snapshot modal memakai modal produk saat ini.
        </p>
        <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm xl:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h2 class="font-display text-lg font-bold text-stone-900">Nilai pesanan per bulan</h2>
                    <p class="text-xs font-semibold text-stone-400">Nilai pesanan dan jumlah pesanan 12 bulan terakhir.</p>
                </div>
                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-extrabold text-yellow-700">12 bulan</span>
            </div>
            <div class="relative mt-4 h-72">
                <canvas id="chartMonthly" aria-label="Grafik nilai pesanan per bulan" role="img"></canvas>
            </div>
        </div>

        <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
            <div>
                <h2 class="font-display text-lg font-bold text-stone-900">Kategori paling laku</h2>
                <p class="text-xs font-semibold text-stone-400">Berdasarkan jumlah produk terjual.</p>
            </div>
            <div class="relative mt-4 h-72">
                <canvas id="chartCategories" aria-label="Grafik kategori paling laku" role="img"></canvas>
            </div>
        </div>

        <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
            <div>
                    <h2 class="font-display text-lg font-bold text-stone-900">Nilai pesanan per tahun</h2>
                <p class="text-xs font-semibold text-stone-400">Nilai pesanan lima tahun terakhir.</p>
            </div>
            <div class="relative mt-4 h-72">
                <canvas id="chartYearly" aria-label="Grafik penjualan per tahun" role="img"></canvas>
            </div>
        </div>

        <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm xl:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h2 class="font-display text-lg font-bold text-stone-900">Penjualan produk, modal &amp; margin</h2>
                    <p class="text-xs font-semibold text-stone-400">Penjualan produk setelah diskon, modal produk, dan margin per bulan.</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-extrabold text-emerald-600">Margin {{ number_format($stats['margin_percent'], 1, ',', '.') }}%</span>
            </div>
            <div class="relative mt-4 h-72">
                <canvas id="chartMargin" aria-label="Grafik margin dan pendapatan" role="img"></canvas>
            </div>
        </div>

        <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm xl:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h2 class="font-display text-lg font-bold text-stone-900">Proyeksi permintaan kategori</h2>
                    <p class="text-xs font-semibold text-stone-400">Tren 6 bulan terakhir dan perkiraan 3 bulan ke depan untuk kategori terlaris.</p>
                </div>
                <span class="rounded-full bg-sky-50 px-2.5 py-1 text-[10px] font-extrabold text-sky-600">Forecast</span>
            </div>
            <div class="relative mt-4 h-72">
                <canvas id="chartForecast" aria-label="Grafik proyeksi permintaan kategori" role="img"></canvas>
            </div>
            <p class="mt-3 text-[11px] font-semibold text-stone-400">Garis putus-putus adalah perkiraan berbasis tren penjualan (regresi linear sederhana), bukan angka pasti.</p>
        </div>

        <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
            <h2 class="font-display text-lg font-bold text-stone-900">Ringkasan kategori</h2>
            <p class="text-xs font-semibold text-stone-400">Penjualan bersih dan margin menurut kategori produk.</p>

            <div class="mt-4 space-y-4">
                @php $maxCategoryQty = max(1, $bestSellingCategories->max('quantity')); @endphp
                @forelse ($bestSellingCategories->take(6) as $category)
                    <div>
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="truncate font-extrabold text-stone-600">{{ $category['name'] }}</span>
                            <span class="font-extrabold text-stone-400">{{ $category['quantity'] }} pcs</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-stone-100">
                            <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-yellow-400" style="width: {{ round(($category['quantity'] / $maxCategoryQty) * 100) }}%"></div>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[10px] font-semibold text-stone-400">
                            <span>Rp{{ number_format($category['revenue'], 0, ',', '.') }}</span>
                            <span class="text-emerald-500">Margin Rp{{ number_format($category['margin'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                @empty
                    <p class="rounded-2xl bg-stone-50 px-4 py-6 text-center text-xs font-semibold text-stone-400">Belum ada penjualan.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.6fr_1fr]">
        <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-stone-100 px-5 py-4">
                <div>
                    <h2 class="font-display text-lg font-bold text-stone-900">Pesanan terbaru</h2>
                    <p class="text-xs font-semibold text-stone-400">Enam pesanan terakhir yang masuk.</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-extrabold text-yellow-700 hover:text-yellow-700">Lihat semua</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[620px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-stone-100 bg-stone-50/60 text-[10px] font-extrabold uppercase tracking-wider text-stone-400">
                            <th class="px-5 py-3">Kode</th>
                            <th class="px-5 py-3">Pelanggan</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($recentOrders as $order)
                            <tr class="transition hover:bg-stone-50/70">
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-extrabold text-stone-800 hover:text-yellow-700">{{ $order->code }}</a>
                                </td>
                                <td class="px-5 py-3.5">
                                    <p class="text-xs font-extrabold text-stone-800">{{ $order->customer_name }}</p>
                                    <p class="text-[11px] font-semibold text-stone-400">{{ $order->customer_phone }}</p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-xs font-extrabold text-stone-800">{{ $order->total_formatted }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-[10px] font-extrabold ring-1 {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-xs font-semibold text-stone-400">{{ $order->created_at->timezone('Asia/Jakarta')->format('d/m/Y') }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex rounded-lg border border-stone-200 px-2.5 py-1.5 text-[11px] font-extrabold text-stone-600 transition hover:border-amber-200 hover:text-yellow-700">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-sm font-semibold text-stone-400">Belum ada pesanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-display text-lg font-bold text-stone-900">Stok menipis</h2>
                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-extrabold text-yellow-700">≤ 5 buah</span>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($lowStockProducts as $product)
                        <a href="{{ route('admin.products.edit', $product) }}" class="flex items-center gap-3 rounded-2xl p-2 transition hover:bg-stone-50">
                            <div class="h-11 w-11 flex-none overflow-hidden rounded-xl bg-yellow-50">
                                <x-store.product-image :product="$product" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-extrabold text-stone-800">{{ $product->name }}</p>
                                <p class="text-[11px] font-semibold text-stone-400">{{ $product->category?->name ?? 'Tanpa kategori' }}</p>
                                @if ($product->sizeList() !== [])
                                    <p class="text-[10px] font-bold text-amber-700">{{ $product->low_stock_sizes->map(fn ($stock, $size) => $size.': '.$stock)->implode(' · ') }}</p>
                                @endif
                            </div>
                            <span class="rounded-lg bg-amber-50 px-2 py-1 text-[11px] font-extrabold text-yellow-700">{{ $product->stock }} total</span>
                        </a>
                    @empty
                        <p class="rounded-2xl bg-emerald-50 px-4 py-6 text-center text-xs font-semibold text-emerald-600">Semua stok aman. Mantap!</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="font-display text-lg font-bold text-stone-900">Produk per kategori</h2>
                <div class="mt-4 space-y-3">
                    @php $maxCount = max(1, $topCategories->max('products_count')); @endphp
                    @foreach ($topCategories as $category)
                        <div>
                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="font-extrabold text-stone-600">{{ $category->icon }} {{ $category->name }}</span>
                                <span class="font-extrabold text-stone-400">{{ $category->products_count }}</span>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-stone-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-yellow-400" style="width: {{ round(($category->products_count / $maxCount) * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.dashboardCharts = @json($charts);
    </script>
    @vite('resources/js/dashboard.js')
@endpush
