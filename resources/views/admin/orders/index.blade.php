@extends('layouts.admin')

@section('title', 'Pesanan')
@section('heading', 'Pesanan')
@section('subheading', 'Pantau dan perbarui status pesanan pelanggan.')

@section('content')
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <p class="text-xs font-semibold text-stone-400">Total {{ $orders->total() }} pesanan ditemukan.</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.orders.index') }}" class="rounded-xl border px-3 py-2 text-[11px] font-extrabold transition {{ $status === '' ? 'border-amber-200 bg-amber-50 text-yellow-700' : 'border-stone-200 bg-white text-stone-500 hover:border-amber-200 hover:text-yellow-700' }}">Semua</a>
            @foreach ([\App\Models\Order::STATUS_PENDING => 'Menunggu', \App\Models\Order::STATUS_PROCESSING => 'Diproses', \App\Models\Order::STATUS_COMPLETED => 'Selesai'] as $value => $label)
                <a href="{{ route('admin.orders.index', ['status' => $value]) }}" class="rounded-xl border px-3 py-2 text-[11px] font-extrabold transition {{ $status === $value ? 'border-amber-200 bg-amber-50 text-yellow-700' : 'border-stone-200 bg-white text-stone-500 hover:border-amber-200 hover:text-yellow-700' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    <form action="{{ route('admin.orders.index') }}" method="GET" class="mt-5 grid gap-3 rounded-3xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_200px_auto]">
        <div class="relative">
            <label for="q" class="sr-only">Cari pesanan</label>
            <input id="q" type="search" name="q" value="{{ $search }}" placeholder="Cari kode, nama, atau nomor HP..."
                   class="w-full rounded-xl border-stone-200 py-2.5 pl-9 pr-3 text-sm focus:border-amber-300 focus:ring-amber-200">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
        </div>

        <label for="status" class="sr-only">Status</label>
        <select id="status" name="status" class="rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
            <option value="">Semua status</option>
            @foreach (\App\Models\Order::STATUS_LABELS as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <div class="flex items-center gap-2">
            <button type="submit" class="flex-1 rounded-xl bg-stone-900 px-4 py-2.5 text-xs font-extrabold text-white transition hover:bg-stone-700">Filter</button>
            @if ($search !== '' || $status !== '')
                <a href="{{ route('admin.orders.index') }}" class="rounded-xl border border-stone-200 px-3 py-2.5 text-xs font-extrabold text-stone-500 transition hover:bg-stone-50">Reset</a>
            @endif
        </div>
    </form>

    <div class="mt-5 overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] text-left text-sm">
                <thead>
                    <tr class="border-b border-stone-100 bg-stone-50/60 text-[10px] font-extrabold uppercase tracking-wider text-stone-400">
                        <th class="px-5 py-3.5">Kode</th>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-5 py-3.5">Produk</th>
                        <th class="px-5 py-3.5">Kurir</th>
                        <th class="px-5 py-3.5">Total</th>
                        <th class="px-5 py-3.5">Pembayaran</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($orders as $order)
                        <tr class="transition hover:bg-stone-50/70">
                            <td class="whitespace-nowrap px-5 py-4">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-extrabold text-stone-800 hover:text-yellow-700">{{ $order->code }}</a>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-xs font-extrabold text-stone-800">{{ $order->customer_name }}</p>
                                <p class="text-[11px] font-semibold text-stone-400">{{ $order->customer_phone }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-xs font-bold text-stone-500">{{ $order->items()->count() }} item</td>
                            <td class="whitespace-nowrap px-5 py-4">
                                @if ($order->shipping_courier)
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-extrabold {{ $order->courierBadgeClasses() }}">{{ $order->courierLabel() }}</span>
                                    @if ($order->shipping_tracking_number)
                                        <p class="mt-1 font-mono text-[10px] font-bold text-stone-400">{{ $order->shipping_tracking_number }}</p>
                                    @endif
                                @else
                                    <span class="text-[10px] font-bold text-stone-400">Belum dipilih</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-xs font-extrabold text-stone-800">{{ $order->total_formatted }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-xs font-bold text-stone-500">
                                {{ $order->payment_method === 'cod' ? 'COD' : 'Transfer' }}
                                @if ($order->payment_proof_path)
                                    <span class="mt-1 block text-[10px] font-extrabold text-emerald-700">Bukti bayar masuk</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-extrabold ring-1 {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-xs font-semibold text-stone-400">{{ $order->created_at->timezone('Asia/Jakarta')->format('d/m/Y') }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.orders.shipping-label', $order) }}" target="_blank" rel="noopener" class="inline-flex rounded-lg border border-stone-200 px-2.5 py-1.5 text-[11px] font-extrabold text-stone-600 transition hover:border-amber-200 hover:text-yellow-700">Resi</a>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex rounded-lg border border-stone-200 px-2.5 py-1.5 text-[11px] font-extrabold text-stone-600 transition hover:border-amber-200 hover:text-yellow-700">Detail</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-16 text-center">
                                <p class="font-display text-lg font-bold text-stone-700">Belum ada pesanan</p>
                                <p class="mt-1 text-xs font-semibold text-stone-400">Pesanan baru akan muncul otomatis di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $orders->links('vendor.pagination.mamikhevan') }}
    </div>
@endsection
