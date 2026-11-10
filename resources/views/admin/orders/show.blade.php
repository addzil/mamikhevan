@extends('layouts.admin')

@section('title', 'Pesanan '.$order->code)
@section('heading', 'Detail pesanan')
@section('subheading', 'Kode '.$order->code.' · '.$order->created_at->format('d/m/Y H:i').' WIB')

@section('content')
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 text-xs font-extrabold text-stone-500 transition hover:text-yellow-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke daftar pesanan
        </a>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('orders.show', $order) }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2 text-[11px] font-extrabold text-stone-600 transition hover:border-amber-200 hover:text-yellow-700">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                Lihat halaman pelacakan
            </a>
            <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $order->customer_phone)) }}?text={{ urlencode('Halo '.$order->customer_name.', kami dari Mamikhevanshop ingin mengonfirmasi pesanan '.$order->code.'.') }}"
               target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-3 py-2 text-[11px] font-extrabold text-white transition hover:bg-emerald-600">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                Chat pelanggan
            </a>
        </div>
    </div>

    <div class="mt-5 flex flex-wrap items-center gap-3 rounded-2xl border border-stone-200 bg-white px-5 py-4 shadow-sm">
        <span class="text-sm font-extrabold text-stone-900">{{ $order->code }}</span>
        <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-extrabold ring-1 {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
        <span class="text-xs font-semibold text-stone-400">{{ $order->items->sum('quantity') }} item &middot; {{ $order->paymentLabel() }}</span>
        <span class="ml-auto font-display text-xl font-extrabold text-yellow-700">{{ $order->total_formatted }}</span>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_340px]">
        <div class="space-y-6">
            <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-100 px-5 py-4">
                    <h2 class="font-display text-lg font-bold text-stone-900">Produk dipesan</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-stone-100 bg-stone-50/60 text-[10px] font-extrabold uppercase tracking-wider text-stone-400">
                                <th class="px-5 py-3">Produk</th>
                                <th class="px-5 py-3">Ukuran</th>
                                <th class="px-5 py-3">Harga</th>
                                <th class="px-5 py-3">Qty</th>
                                <th class="px-5 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 flex-none overflow-hidden rounded-lg bg-yellow-50">
                                                @if ($item->product)
                                                    <x-store.product-image :product="$item->product" />
                                                @else
                                                    <div class="grid h-full w-full place-items-center text-lg">👕</div>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-xs font-extrabold text-stone-800">{{ $item->product_name }}</p>
                                                @if (! $item->product)
                                                    <p class="text-[10px] font-bold text-stone-400">Produk sudah dihapus</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs font-bold text-stone-500">{{ $item->size ?: '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-xs font-bold text-stone-500">{{ $item->price_formatted }}</td>
                                    <td class="px-5 py-3.5 text-xs font-extrabold text-stone-700">{{ $item->quantity }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-right text-xs font-extrabold text-stone-800">{{ $item->subtotal_formatted }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-stone-100 bg-stone-50/50 px-5 py-4">
                    <dl class="ml-auto max-w-xs space-y-2 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="font-semibold text-stone-500">Subtotal</dt>
                            <dd class="font-extrabold text-stone-800">{{ $order->subtotal_formatted }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="font-semibold text-stone-500">Ongkos kirim</dt>
                            <dd class="font-extrabold text-stone-800">{{ $order->shipping_formatted }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-t border-stone-200 pt-2">
                            <dt class="font-extrabold text-stone-900">Total</dt>
                            <dd class="font-display text-lg font-extrabold text-yellow-700">{{ $order->total_formatted }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                    <h2 class="font-display text-lg font-bold text-stone-900">Data pelanggan</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div>
                            <dt class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Nama</dt>
                            <dd class="mt-0.5 text-xs font-extrabold text-stone-800">{{ $order->customer_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Telepon</dt>
                            <dd class="mt-0.5 text-xs font-bold text-stone-600">{{ $order->customer_phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Email</dt>
                            <dd class="mt-0.5 break-all text-xs font-bold text-stone-600">{{ $order->customer_email ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                    <h2 class="font-display text-lg font-bold text-stone-900">Alamat &amp; catatan</h2>
                    <p class="mt-4 text-xs font-semibold leading-6 text-stone-600">{!! nl2br(e($order->customer_address)) !!}</p>
                    @if ($order->note)
                        <div class="mt-3 rounded-xl bg-amber-50 px-3 py-2">
                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-yellow-700">Catatan</p>
                            <p class="mt-0.5 text-xs font-semibold leading-5 text-amber-700">{{ $order->note }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="font-display text-lg font-bold text-stone-900">Perbarui status</h2>
                <p class="mt-1 text-xs font-semibold leading-5 text-stone-400">
                    Status akan langsung terlihat di halaman pelacakan pelanggan.
                </p>

                <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <label for="status" class="sr-only">Status pesanan</label>
                    <select id="status" name="status" class="w-full rounded-xl border-stone-200 py-2.5 text-sm font-bold focus:border-amber-300 focus:ring-amber-200">
                        @foreach (\App\Models\Order::STATUS_LABELS as $value => $label)
                            <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror

                    <button type="submit" class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-stone-900 px-4 py-2.5 text-xs font-extrabold text-white transition hover:bg-stone-700">
                        Simpan status
                    </button>
                </form>

                <p class="mt-3 rounded-xl bg-yellow-50 px-3 py-2 text-[11px] font-semibold leading-5 text-yellow-700">
                    Membatalkan pesanan akan mengembalikan stok produk secara otomatis.
                </p>
            </div>

            <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="font-display text-lg font-bold text-stone-900">Pembayaran</h2>
                <p class="mt-3 text-xs font-extrabold text-stone-800">{{ $order->paymentLabel() }}</p>
                <p class="mt-1 text-xs font-semibold leading-5 text-stone-400">
                    {{ $order->payment_method === 'cod' ? 'Ditagih tunai oleh kurir saat pengiriman.' : 'Pastikan pembayaran sudah masuk sebelum status diubah menjadi Diproses.' }}
                </p>
            </div>

            <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="font-display text-lg font-bold text-stone-900">Timeline</h2>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                        <div>
                            <p class="text-xs font-extrabold text-stone-700">Dibuat</p>
                            <p class="text-[11px] font-semibold text-stone-400">{{ $order->created_at->format('d/m/Y H:i') }} WIB</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                        <div>
                            <p class="text-xs font-extrabold text-stone-700">Terakhir diperbarui</p>
                            <p class="text-[11px] font-semibold text-stone-400">{{ $order->updated_at->format('d/m/Y H:i') }} WIB</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
@endsection
