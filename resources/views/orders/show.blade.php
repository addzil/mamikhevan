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
                <p class="mt-2 text-sm text-stone-500">Dibuat {{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full px-4 py-2 text-xs font-extrabold ring-1 {{ $order->statusColor() }}">
                {{ $order->statusLabel() }}
            </span>
        </div>

        <div class="mt-6 flex flex-col gap-2 rounded-2xl border border-yellow-100 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs font-semibold text-stone-500">
                Simpan kode pesanan untuk melacak status: <span class="font-extrabold text-stone-800">{{ $order->code }}</span>
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
            @endif
        </div>

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
                                        {{ $item->quantity }} &times; {{ $item->price_formatted }}
                                        @if ($item->size) &middot; Ukuran {{ $item->size }} @endif
                                    </p>
                                </div>
                                <p class="text-sm font-extrabold text-stone-800">{{ $item->subtotal_formatted }}</p>
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
                        <div class="flex items-end justify-between gap-4 border-t border-dashed border-yellow-200 pt-3">
                            <dt class="text-sm font-extrabold text-stone-900">Total</dt>
                            <dd class="font-display text-2xl font-extrabold text-amber-600">{{ $order->total_formatted }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                    <h2 class="font-display text-lg font-bold text-stone-900">Alamat pengiriman</h2>
                    <p class="mt-3 text-sm font-extrabold text-stone-800">{{ $order->customer_name }}</p>
                    <p class="mt-1 text-sm font-semibold text-stone-500">{{ $order->customer_phone }}</p>
                    @if ($order->customer_email)
                        <p class="text-sm font-semibold text-stone-500">{{ $order->customer_email }}</p>
                    @endif
                    <p class="mt-3 text-sm leading-7 text-stone-600">{!! nl2br(e($order->customer_address)) !!}</p>
                    @if ($order->note)
                        <div class="mt-4 rounded-2xl bg-yellow-50 px-4 py-3">
                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Catatan</p>
                            <p class="mt-1 text-xs font-semibold leading-6 text-stone-600">{{ $order->note }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <aside class="space-y-6">
                <div class="rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                    <h2 class="font-display text-lg font-bold text-stone-900">Pembayaran</h2>
                    <p class="mt-3 text-sm font-extrabold text-stone-800">{{ $order->paymentLabel() }}</p>
                    <p class="mt-1 text-xs font-semibold text-stone-400">
                        {{ $order->payment_method === 'cod' ? 'Siapkan uang tunai saat kurir tiba.' : 'Instruksi transfer dikirim oleh admin via WhatsApp.' }}
                    </p>
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
