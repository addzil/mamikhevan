@extends('layouts.admin')

@section('title', 'Keuangan')
@section('heading', 'Keuangan')
@section('subheading', 'Ringkasan transaksi penjualan berdasarkan tanggal dan status pesanan.')

@section('content')
    @if ($errors->any())
        <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4 text-sm font-bold text-red-700">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('admin.finance.index') }}" method="GET" class="mb-6 grid gap-3 rounded-3xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_1fr_1fr_auto]">
        <div>
            <label for="from" class="mb-1 block text-xs font-extrabold text-stone-500">Dari tanggal</label>
            <input id="from" type="date" name="from" value="{{ $filters['from'] }}" class="w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
        </div>
        <div>
            <label for="to" class="mb-1 block text-xs font-extrabold text-stone-500">Sampai tanggal</label>
            <input id="to" type="date" name="to" value="{{ $filters['to'] }}" class="w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
        </div>
        <div>
            <label for="status" class="mb-1 block text-xs font-extrabold text-stone-500">Status pesanan</label>
            <select id="status" name="status" class="w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                <option value="">Semua status</option>
                @foreach (\App\Models\Order::STATUS_LABELS as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 rounded-xl bg-stone-900 px-4 py-2.5 text-xs font-extrabold text-white hover:bg-stone-700">Tampilkan</button>
            <a href="{{ route('admin.finance.export', request()->query()) }}" class="rounded-xl bg-amber-500 px-4 py-2.5 text-center text-xs font-extrabold text-white hover:bg-amber-600">Unduh Excel</a>
        </div>
    </form>

    <div class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
            <p class="text-xs font-extrabold uppercase tracking-wide text-emerald-700">Pesanan selesai</p>
            <p class="mt-2 font-display text-2xl font-extrabold text-stone-900">Rp {{ number_format($summary['completed_total'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs font-semibold text-stone-500">{{ number_format($summary['completed_count']) }} transaksi tercatat selesai</p>
        </article>
        <article class="rounded-3xl border border-amber-100 bg-amber-50 p-5">
            <p class="text-xs font-extrabold uppercase tracking-wide text-amber-700">Dalam proses</p>
            <p class="mt-2 font-display text-2xl font-extrabold text-stone-900">Rp {{ number_format($summary['active_total'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs font-semibold text-stone-500">{{ number_format($summary['active_count']) }} transaksi belum selesai</p>
        </article>
        <article class="rounded-3xl border border-red-100 bg-red-50 p-5">
            <p class="text-xs font-extrabold uppercase tracking-wide text-red-700">Dibatalkan</p>
            <p class="mt-2 font-display text-2xl font-extrabold text-stone-900">Rp {{ number_format($summary['cancelled_total'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs font-semibold text-stone-500">{{ number_format($summary['cancelled_count']) }} transaksi dibatalkan</p>
        </article>
        <article class="rounded-3xl border border-stone-200 bg-white p-5">
            <p class="text-xs font-extrabold uppercase tracking-wide text-stone-500">Diskon pesanan selesai</p>
            <p class="mt-2 font-display text-2xl font-extrabold text-stone-900">Rp {{ number_format($summary['completed_discounts'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs font-semibold text-stone-500">Gabungan diskon produk dan pengiriman</p>
        </article>
    </div>

    <section class="mb-6 rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
        <h2 class="font-display text-lg font-extrabold text-stone-900">Rekonsiliasi transaksi selesai</h2>
        <p class="mt-1 text-xs font-semibold text-stone-500">Total transaksi terdiri dari penjualan produk bersih, ongkir bersih, dan kode unik transfer.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([['Penjualan produk bersih', $summary['completed_product_sales']], ['Ongkir bersih', $summary['completed_shipping_net']], ['Kode unik transfer', $summary['completed_unique_codes']], ['Total transaksi selesai', $summary['completed_total']]] as [$label, $amount])
                <div class="rounded-2xl bg-stone-50 p-4">
                    <p class="text-xs font-extrabold text-stone-600">{{ $label }}</p>
                    <p class="mt-1 text-lg font-extrabold text-stone-900">Rp {{ number_format($amount, 0, ',', '.') }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mb-6 rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
        <div class="mb-4">
            <h2 class="font-display text-lg font-extrabold text-stone-900">Pembayaran pesanan selesai</h2>
            <p class="mt-1 text-xs font-semibold text-stone-500">Nilai dikelompokkan berdasarkan metode pembayaran yang dipilih pelanggan.</p>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($paymentBreakdown as $payment)
                <div class="rounded-2xl bg-stone-50 p-4">
                    <p class="text-xs font-extrabold text-stone-700">{{ $payment['label'] }}</p>
                    <p class="mt-1 text-lg font-extrabold text-stone-900">Rp {{ number_format($payment['total'], 0, ',', '.') }}</p>
                    <p class="text-[11px] font-semibold text-stone-400">{{ $payment['count'] }} transaksi selesai</p>
                </div>
            @empty
                <p class="text-sm font-semibold text-stone-400">Belum ada transaksi selesai pada periode ini.</p>
            @endforelse
        </div>
    </section>

    <section class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-stone-100 px-5 py-4">
            <div>
                <h2 class="font-display text-lg font-extrabold text-stone-900">Rincian transaksi</h2>
                <p class="text-xs font-semibold text-stone-400">{{ $orders->total() }} pesanan pada filter yang dipilih</p>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold text-stone-500">{{ \Carbon\Carbon::parse($filters['from'])->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($filters['to'])->format('d/m/Y') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead><tr class="border-b border-stone-100 bg-stone-50 text-[10px] font-extrabold uppercase tracking-wider text-stone-400">
                    <th class="px-5 py-3">Tanggal</th><th class="px-5 py-3">Pesanan / Pelanggan</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Pembayaran</th><th class="px-5 py-3">Item</th><th class="px-5 py-3 text-right">Total</th>
                </tr></thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 text-xs font-semibold text-stone-500">{{ $order->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-4"><a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-extrabold text-stone-800 hover:text-amber-700">{{ $order->code }}</a><p class="mt-1 text-[11px] font-semibold text-stone-400">{{ $order->customer_name }}</p></td>
                            <td class="whitespace-nowrap px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-extrabold ring-1 {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span></td>
                            <td class="whitespace-nowrap px-5 py-4 text-xs font-bold text-stone-500">{{ $order->paymentLabel() }}</td>
                            <td class="px-5 py-4 text-xs font-semibold text-stone-500">{{ $order->items_count }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <p class="text-xs font-extrabold text-stone-800">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                                <p class="mt-1 text-[10px] font-semibold text-stone-400">Produk bersih Rp {{ number_format($order->subtotal - $order->promotion_discount, 0, ',', '.') }} · Ongkir bersih Rp {{ number_format($order->shipping_cost - $order->shipping_discount, 0, ',', '.') }}</p>
                                @if (($order->payment_unique_code ?? 0) > 0)
                                    <p class="text-[10px] font-semibold text-stone-400">Kode unik transfer Rp {{ number_format($order->payment_unique_code, 0, ',', '.') }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-sm font-semibold text-stone-400">Tidak ada transaksi pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-5">{{ $orders->links('vendor.pagination.mamikhevan') }}</div>
    </section>

    <p class="mt-4 text-[11px] font-semibold leading-5 text-stone-400">Kode unik transfer hanya pembeda nominal pembayaran dan bukan penjualan produk. Status selesai mengikuti konfirmasi penerimaan oleh pelanggan. Laporan ini belum mencakup biaya ekspedisi yang ditanggung toko, biaya pembayaran, pajak, utang, atau aset di luar aplikasi.</p>
@endsection
