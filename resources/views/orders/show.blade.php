@extends('layouts.storefront')

@section('title', 'Pesanan '.$order->code.' — Mamikhevanshop')

@section('content')
    @php
        $steps = [
            ['key' => 'pending', 'label' => 'Menunggu'],
            ['key' => 'confirmed', 'label' => 'Dikonfirmasi'],
            ['key' => 'processing', 'label' => 'Diproses'],
            ['key' => 'shipped', 'label' => 'Dikirim'],
            ['key' => 'completed', 'label' => 'Selesai'],
        ];
        $currentStep = array_search($order->status, array_column($steps, 'key'), true);
        $isCancelled = $order->status === \App\Models\Order::STATUS_CANCELLED;
    @endphp

    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <nav class="flex items-center gap-2 text-xs font-bold text-stone-400" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="transition hover:text-amber-600">Beranda</a>
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            <span class="text-amber-600">Pesanan {{ $order->code }}</span>
        </nav>

        <div class="mt-4 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-amber-400">Terima kasih sudah belanja</p>
                <h1 class="mt-2 font-display text-3xl font-extrabold text-stone-900 sm:text-4xl">Pesanan {{ $order->code }}</h1>
                <p class="mt-2 text-sm text-stone-500">Dibuat {{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</p>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full px-4 py-2 text-xs font-extrabold ring-1 {{ $order->statusColor() }}">
                {{ $order->statusLabel() }}
            </span>
        </div>

        <div class="mt-6 flex flex-col gap-2 rounded-2xl border border-yellow-100 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs font-semibold text-stone-500">
                Simpan kode pesanan dan nomor WhatsApp untuk <a href="{{ route('orders.track') }}" class="font-extrabold text-amber-600 underline">melacak status</a>: <span class="font-extrabold text-stone-800">{{ $order->code }}</span>
            </p>
            <div x-data="{ copied: false }" class="flex items-center gap-2">
                <button type="button"
                        @click="navigator.clipboard.writeText('{{ $order->code }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                        class="inline-flex items-center gap-1.5 rounded-full border border-yellow-200 px-3 py-1.5 text-xs font-extrabold text-stone-600 transition hover:border-amber-200 hover:text-amber-600">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" /></svg>
                    <span x-text="copied ? 'Tersalin!' : 'Salin kode'">Salin kode</span>
                </button>
                @if ($order->status === \App\Models\Order::STATUS_PENDING)
                    <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Mamikhevanshop, saya ingin konfirmasi pesanan '.$order->code.'.') }}"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500 px-3 py-1.5 text-xs font-extrabold text-white transition hover:bg-emerald-600">
                        Konfirmasi via WA
                    </a>
                @endif
            </div>
        </div>

        <div class="mt-6 rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
            <h2 class="font-display text-lg font-bold text-stone-900">Status pengiriman</h2>

            @if ($isCancelled)
                <div class="mt-4 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    <svg class="mt-0.5 h-5 w-5 flex-none text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                    <div>
                        <p class="text-sm font-extrabold text-amber-700">Pesanan dibatalkan</p>
                        <p class="mt-1 text-xs font-semibold leading-6 text-yellow-700">Jika kamu merasa ini keliru, silakan hubungi kami via WhatsApp dengan menyertakan kode pesanan.</p>
                    </div>
                </div>
            @else
                <div class="mt-6 grid grid-cols-5 gap-1">
                    @foreach ($steps as $index => $step)
                        @php $done = $currentStep !== false && $index <= $currentStep; @endphp
                        <div class="relative flex flex-col items-center text-center">
                            @if (! $loop->first)
                                <span class="absolute left-0 top-4 h-0.5 w-1/2 {{ $done ? 'bg-amber-400' : 'bg-yellow-100' }}"></span>
                            @endif
                            @if (! $loop->last)
                                <span class="absolute right-0 top-4 h-0.5 w-1/2 {{ $done && $index < $currentStep ? 'bg-amber-400' : 'bg-yellow-100' }}"></span>
                            @endif
                            <span class="relative z-10 grid h-8 w-8 place-items-center rounded-full text-xs font-extrabold {{ $done ? 'bg-amber-500 text-white shadow-sm' : 'border border-yellow-200 bg-white text-stone-400' }}">
                                @if ($done)
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </span>
                            <span class="mt-2 text-[10px] font-extrabold uppercase tracking-wide {{ $done ? 'text-amber-600' : 'text-stone-400' }} sm:text-[11px]">{{ $step['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="mt-5 text-center text-xs font-semibold text-stone-400">
                    Status diperbarui manual oleh admin. Butuh bantuan? <a href="{{ route('contact.index') }}" class="font-extrabold text-amber-600 hover:underline">Hubungi kami</a>.
                </p>
                @if ($order->status === \App\Models\Order::STATUS_PROCESSING)
                    <div class="mt-4 rounded-2xl border border-sky-100 bg-sky-50 p-4 text-center">
                        <p class="text-sm font-extrabold text-sky-800">Pesanan sedang disiapkan</p>
                        <p class="mt-1 text-xs leading-6 text-sky-700">Persiapan hingga paket diserahkan kepada kurir membutuhkan sekitar 1–2 hari. Nomor resi akan tampil di halaman ini setelah pesanan dikirim.</p>
                    </div>
                @elseif ($order->status === \App\Models\Order::STATUS_SHIPPED)
                    <div class="mt-4 rounded-2xl border border-violet-100 bg-violet-50 p-4" x-data="paymentCopy">
                        <p class="text-sm font-extrabold text-violet-800">Pesanan sudah dikirim</p>
                        @if ($order->shipping_tracking_number)
                            <p class="mt-2 text-xs font-semibold text-violet-700">Nomor resi</p>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <p class="select-all font-mono text-base font-extrabold text-stone-900">{{ $order->shipping_tracking_number }}</p>
                                <button type="button" data-copy-value="{{ $order->shipping_tracking_number }}" data-original-text="Salin resi" @click="copy($event)" class="rounded-lg border border-violet-200 bg-white px-2.5 py-1.5 text-[10px] font-extrabold text-violet-800 transition hover:bg-violet-100">Salin resi</button>
                            </div>
                        @else
                            <p class="mt-1 text-xs leading-5 text-violet-700">Informasi resi akan segera diperbarui oleh admin.</p>
                        @endif
                    </div>
                @endif
                @if ($order->delivered_at)
                    <p class="mt-4 text-center text-xs font-semibold text-emerald-600">Dikonfirmasi diterima pada {{ $order->delivered_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</p>
                @endif
            @endif
        </div>

        @if ($order->status === \App\Models\Order::STATUS_SHIPPED)
            <div class="mt-6 rounded-3xl border border-emerald-100 bg-emerald-50 p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div class="flex items-start gap-3">
                        <span class="grid h-11 w-11 flex-none place-items-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </span>
                        <div>
                            <h2 class="font-display text-lg font-bold text-emerald-900">Barang sudah diterima?</h2>
                            <p class="mt-1 text-xs font-semibold leading-6 text-emerald-700">Konfirmasi penerimaan agar pesanan ditandai selesai. Setelah itu kamu bisa memberi penilaian produk.</p>
                        </div>
                    </div>
                    <form action="{{ route('orders.confirm-receipt', $order) }}" method="POST" onsubmit="return confirm('Konfirmasi bahwa paket sudah kamu terima?')">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-emerald-700 sm:w-auto">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Konfirmasi barang diterima
                        </button>
                    </form>
                </div>
            </div>
        @endif

        @if ($order->status === \App\Models\Order::STATUS_COMPLETED)
            <div class="mt-6 rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                @if ($canReview)
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                        <div class="flex items-start gap-3">
                            <span class="grid h-11 w-11 flex-none place-items-center rounded-2xl bg-amber-50 text-amber-600">
                                <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.539 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.98 10.1c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69l1.519-4.674Z" /></svg>
                            </span>
                            <div>
                                <h2 class="font-display text-lg font-bold text-stone-900">Bagaimana produknya?</h2>
                                <p class="mt-1 text-xs font-semibold leading-6 text-stone-500">Beri penilaian bintang dan ulasan untuk produk yang sudah kamu terima. Ulasanmu membantu orang tua lain.</p>
                            </div>
                        </div>
                        <a href="{{ route('orders.reviews.create', $order) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-amber-600 sm:w-auto">
                            Beri penilaian
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                    </div>
                @elseif ($allReviewed)
                    <div class="flex items-start gap-3">
                        <span class="grid h-11 w-11 flex-none place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </span>
                        <div>
                            <h2 class="font-display text-lg font-bold text-stone-900">Terima kasih atas penilaianmu!</h2>
                            <p class="mt-1 text-xs font-semibold leading-6 text-stone-500">Semua produk pada pesanan ini sudah dinilai. Ulasanmu sangat membantu pelanggan lain.</p>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_340px]">
            <div class="space-y-6">
                <div class="rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                    <h2 class="font-display text-lg font-bold text-stone-900">Produk yang dipesan</h2>

                    <div class="mt-4 divide-y divide-yellow-100">
                        @foreach ($order->items as $item)
                            <div class="flex items-center gap-4 py-4 first:pt-0 last:pb-0">
                                <div class="h-16 w-16 flex-none overflow-hidden rounded-2xl bg-yellow-50">
                                    @if ($item->product)
                                        <x-store.product-image :product="$item->product" />
                                    @else
                                        <div class="grid h-full w-full place-items-center bg-yellow-50 text-2xl">👕</div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    @if ($item->product)
                                        <a href="{{ route('products.show', $item->product) }}" class="text-sm font-extrabold text-stone-800 transition hover:text-amber-600">{{ $item->product_name }}</a>
                                    @else
                                        <p class="text-sm font-extrabold text-stone-800">{{ $item->product_name }}</p>
                                    @endif
                                    <p class="mt-1 text-xs font-semibold text-stone-400">
                                        {{ $item->quantity }} &times; {{ $item->is_promo_gift ? 'Gratis' : $item->price_formatted }}
                                        @if ($item->size) &middot; Ukuran {{ $item->size }} @endif
                                    </p>
                                    @if ($item->is_promo_gift)
                                        <p class="mt-1 text-xs font-extrabold text-emerald-700">Hadiah promo</p>
                                    @endif
                                </div>
                                <p class="text-sm font-extrabold text-stone-800">{{ $item->is_promo_gift ? 'Gratis' : $item->subtotal_formatted }}</p>
                            </div>
                        @endforeach
                    </div>

                    <dl class="mt-5 space-y-3 border-t border-dashed border-yellow-200 pt-5 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-semibold text-stone-500">Subtotal</dt>
                            <dd class="font-extrabold text-stone-800">{{ $order->subtotal_formatted }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-semibold text-stone-500">Ongkos kirim</dt>
                            <dd class="font-extrabold {{ $order->shipping_cost === 0 ? 'text-emerald-600' : 'text-stone-800' }}">{{ $order->shipping_formatted }}</dd>
                        </div>
                        @if ($order->promotion_name)
                            <p class="text-xs font-extrabold text-amber-700">Promo: {{ $order->promotion_name }}</p>
                        @endif
                        @if ($order->voucher_code)
                            <div class="flex justify-between gap-4"><dt>Kode voucher</dt><dd class="font-mono font-bold text-stone-700">{{ $order->voucher_code }}</dd></div>
                        @endif
                        @if ($order->promotion_discount > 0)
                            <div class="flex justify-between gap-4"><dt>Diskon produk</dt><dd>-Rp{{ number_format($order->promotion_discount, 0, ',', '.') }}</dd></div>
                        @endif
                        @if ($order->shipping_discount > 0)
                            <div class="flex justify-between gap-4"><dt>Diskon ongkir</dt><dd>-Rp{{ number_format($order->shipping_discount, 0, ',', '.') }}</dd></div>
                        @endif
                        @if ($order->payment_unique_code)
                            <div class="flex items-center justify-between gap-4">
                                <dt class="font-semibold text-stone-500">Kode unik transfer</dt>
                                <dd class="font-extrabold text-stone-800">Rp{{ number_format($order->payment_unique_code, 0, ',', '.') }}</dd>
                            </div>
                        @endif
                        <div class="flex items-end justify-between gap-4 border-t border-dashed border-yellow-200 pt-3">
                            <dt class="text-sm font-extrabold text-stone-900">Total</dt>
                            <dd class="flex items-center gap-2" x-data="paymentCopy">
                                <span class="font-display text-2xl font-extrabold text-amber-600">{{ $order->total_formatted }}</span>
                                @if ($order->payment_method === 'transfer')
                                    <button type="button" data-copy-value="{{ $order->total }}" data-original-text="Salin total" @click="copy($event)" class="rounded-lg border border-yellow-200 px-2 py-1 text-[10px] font-extrabold text-stone-600 hover:bg-yellow-50">Salin total</button>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                    <h2 class="font-display text-lg font-bold text-stone-900">Alamat pengiriman</h2>
                    <p class="mt-2 text-xs font-extrabold text-amber-700">{{ $order->shippingLabel() }}</p>
                    <p class="mt-3 text-sm font-extrabold text-stone-800">{{ $order->customer_name }}</p>
                    <p class="mt-1 text-sm font-semibold text-stone-500">{{ $order->customer_phone }}</p>
                    @if ($order->customer_email)
                        <p class="text-sm font-semibold text-stone-500">{{ $order->customer_email }}</p>
                    @endif
                    <p class="mt-3 text-sm leading-7 text-stone-600">{!! nl2br(e($order->customer_address)) !!}</p>
                    @if ($order->shipping_postal_code)
                        <p class="mt-2 text-sm leading-6 text-stone-500">{{ $order->shipping_subdistrict }}, {{ $order->shipping_district }}, {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}</p>
                    @endif
                    @if ($order->note)
                        <div class="mt-4 rounded-2xl bg-yellow-50 px-4 py-3">
                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Catatan</p>
                            <p class="mt-1 text-xs font-semibold leading-6 text-stone-600">{{ $order->note }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <aside class="space-y-6" x-data="paymentCopy">
                <div class="rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                    <h2 class="font-display text-lg font-bold text-stone-900">Pembayaran</h2>
                    <p class="mt-3 text-sm font-extrabold text-stone-800">{{ $order->paymentLabel() }}</p>
                    <p class="mt-1 text-xs font-semibold text-stone-400">
                        @if ($order->payment_method === 'cod')
                            Siapkan uang tunai saat pesanan diterima.
                        @else
                            Transfer tepat sesuai total pesanan di atas. Rincian pembayaran juga dikirim melalui email.
                        @endif
                    </p>
                    @if ($order->payment_method === 'transfer')
                        @foreach (($order->payment_destination['type'] ?? null) === 'bank' ? [$order->payment_destination] : ($order->payment_destination === null ? $paymentSettings['bank_accounts'] : []) as $account)
                            <div class="mt-4 rounded-2xl border border-yellow-100 bg-yellow-50 p-4">
                                <p class="text-xs font-extrabold text-stone-800">{{ $account['bank'] }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <p class="select-all text-lg font-extrabold tracking-wide text-amber-700">{{ $account['account_number'] }}</p>
                                    <button type="button" data-copy-value="{{ $account['account_number'] }}" data-original-text="Salin rekening" @click="copy($event)" class="rounded-lg border border-yellow-200 px-2 py-1 text-[10px] font-extrabold text-stone-600 hover:bg-white">Salin</button>
                                </div>
                                <p class="text-xs text-stone-500">a.n. {{ $account['account_name'] }}</p>
                            </div>
                        @endforeach

                        @if ((($order->payment_destination['type'] ?? null) === 'qris' && \Illuminate\Support\Facades\Storage::disk('public')->exists($order->payment_destination['image'])) || ($order->payment_destination === null && $paymentSettings['qris_enabled'] && $paymentSettings['qris_image']))
                            <div class="mt-4 rounded-2xl border border-yellow-100 p-4">
                                <p class="mb-3 text-xs font-extrabold text-stone-800">Bayar dengan QRIS</p>
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_destination['image'] ?? $paymentSettings['qris_image']) }}" alt="QRIS Mamikhevanshop" class="mx-auto max-h-72 w-auto rounded-xl object-contain">
                            </div>
                        @endif

                        @foreach ($paymentSettings['whatsapp_numbers'] as $index => $whatsappNumber)
                            @php
                                $adminWhatsapp = preg_replace('/^0/', '62', $whatsappNumber);
                                $confirmationMessage = 'Halo Admin Mamikhevanshop, saya ingin konfirmasi pembayaran pesanan '.$order->code.'. Nama: '.$order->customer_name.'. Total transfer: '.$order->total_formatted.'.';
                            @endphp
                            <a href="https://wa.me/{{ $adminWhatsapp }}?text={{ urlencode($confirmationMessage) }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-emerald-500 px-4 py-3 text-sm font-extrabold text-white transition hover:bg-emerald-600">
                                Konfirmasi pembayaran ke Admin {{ $index + 1 }}
                            </a>
                        @endforeach

                        @if ($order->status === \App\Models\Order::STATUS_PENDING)
                            <div class="mt-5 rounded-2xl border border-amber-100 bg-amber-50 p-4">
                                <h3 class="text-sm font-extrabold text-stone-800">Sudah transfer?</h3>
                                @if ($order->payment_proof_path)
                                    <p class="mt-1 text-xs font-semibold leading-5 text-emerald-700">Bukti pembayaran terkirim {{ $order->payment_proof_uploaded_at?->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB dan sedang menunggu pemeriksaan admin.</p>
                                @else
                                    <p class="mt-1 text-xs font-semibold leading-5 text-stone-500">Kirim bukti pembayaran untuk diperiksa admin. Pesanan akan tetap menunggu konfirmasi sampai pembayaran diperiksa.</p>
                                @endif
                                <form action="{{ route('orders.payment-proof.store', $order) }}" method="POST" enctype="multipart/form-data" class="mt-3 space-y-2">
                                    @csrf
                                    <label for="payment_proof" class="block text-xs font-extrabold text-stone-700">{{ $order->payment_proof_path ? 'Ganti bukti pembayaran' : 'Upload bukti pembayaran' }}</label>
                                    <input id="payment_proof" type="file" name="payment_proof" accept="image/jpeg,image/png,image/webp" required class="w-full rounded-xl border border-amber-200 bg-white p-2 text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-amber-100 file:px-3 file:py-2 file:text-xs file:font-bold file:text-amber-800">
                                    @error('payment_proof') <p class="text-xs font-bold text-red-600">{{ $message }}</p> @enderror
                                    <p class="text-[10px] font-semibold text-stone-400">JPG, PNG, atau WebP. Maksimal 5 MB.</p>
                                    <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-stone-900 px-4 py-3 text-sm font-extrabold text-white transition hover:bg-stone-700">{{ $order->payment_proof_path ? 'Kirim bukti pengganti' : 'Kirim bukti pembayaran' }}</button>
                                </form>
                            </div>
                        @elseif ($order->payment_proof_path)
                            <p class="mt-4 rounded-xl bg-emerald-50 p-3 text-xs font-semibold leading-5 text-emerald-700">Bukti pembayaran sudah dikirim pada {{ $order->payment_proof_uploaded_at?->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB.</p>
                        @endif
                    @endif
                </div>

                <div class="rounded-3xl border border-yellow-100 bg-gradient-to-br from-yellow-50 to-amber-50 p-6">
                    <h2 class="font-display text-lg font-bold text-stone-900">Butuh bantuan?</h2>
                    <p class="mt-2 text-xs font-semibold leading-6 text-stone-500">
                        Sertakan kode <span class="font-extrabold text-stone-700">{{ $order->code }}</span> agar kami bisa membantu lebih cepat.
                    </p>
                    <a href="{{ route('contact.index') }}" class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-stone-900 px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-stone-700">
                        Hubungi kami
                    </a>
                </div>

                <a href="{{ route('products.index') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-yellow-200 bg-white px-4 py-3 text-sm font-extrabold text-stone-700 transition hover:border-amber-200 hover:text-amber-600">
                    Lanjut belanja
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
            </aside>
        </div>
    </section>
@endsection
