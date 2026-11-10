@extends('layouts.storefront')

@section('title', 'Checkout — Mamikhevanshop')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <nav class="flex items-center gap-2 text-xs font-bold text-stone-400" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="transition hover:text-amber-600">Beranda</a>
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            <a href="{{ route('cart.index') }}" class="transition hover:text-amber-600">Keranjang</a>
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            <span class="text-amber-600">Checkout</span>
        </nav>

        <div class="mt-4">
            <h1 class="font-display text-3xl font-extrabold text-stone-900 sm:text-4xl">Checkout pesanan</h1>
            <p class="mt-2 text-sm text-stone-500">Lengkapi data pengiriman. Pesanan diproses setelah dikonfirmasi admin.</p>
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-extrabold text-amber-700">Mohon periksa kembali data berikut:</p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-xs font-semibold text-yellow-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('checkout.store') }}" method="POST" x-data="{ payment: '{{ old('payment_method', 'transfer') }}' }" class="mt-8 grid gap-6 lg:grid-cols-[1fr_380px] lg:gap-8">
            @csrf

            <div class="space-y-6">
                <div class="rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-2xl bg-amber-50 text-sm font-extrabold text-amber-600">1</span>
                        <div>
                            <h2 class="font-display text-xl font-bold text-stone-900">Data penerima</h2>
                            <p class="text-xs font-semibold text-stone-400">Pastikan nomor WhatsApp aktif untuk konfirmasi.</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="customer_name" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Nama lengkap <span class="text-amber-600">*</span></label>
                            <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}" required autocomplete="name"
                                   class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                            @error('customer_name') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="customer_phone" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">No. WhatsApp <span class="text-amber-600">*</span></label>
                            <input id="customer_phone" type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required placeholder="08xxxxxxxxxx" autocomplete="tel"
                                   class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                            @error('customer_phone') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="customer_email" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Email <span class="font-semibold text-stone-400">(opsional)</span></label>
                            <input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email') }}" autocomplete="email"
                                   class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                            @error('customer_email') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="customer_address" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Alamat lengkap <span class="text-amber-600">*</span></label>
                            <textarea id="customer_address" name="customer_address" rows="3" required autocomplete="street-address"
                                      class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">{{ old('customer_address') }}</textarea>
                            @error('customer_address') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="note" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Catatan pesanan <span class="font-semibold text-stone-400">(opsional)</span></label>
                            <textarea id="note" name="note" rows="2" placeholder="Contoh: tolong dibungkus kado, kirim setelah jam 15.00"
                                      class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">{{ old('note') }}</textarea>
                            @error('note') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-2xl bg-amber-50 text-sm font-extrabold text-amber-600">2</span>
                        <div>
                            <h2 class="font-display text-xl font-bold text-stone-900">Metode pembayaran</h2>
                            <p class="text-xs font-semibold text-stone-400">Pilih cara pembayaran yang paling nyaman.</p>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment_method" value="transfer" x-model="payment" class="peer sr-only">
                            <span class="flex h-full flex-col rounded-2xl border border-yellow-200 bg-white p-4 transition hover:border-amber-300 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:ring-1 peer-checked:ring-amber-300">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-amber-100 text-yellow-700">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h16.5a1.5 1.5 0 0 0 1.5-1.5V6.75a1.5 1.5 0 0 0-1.5-1.5H3.75a1.5 1.5 0 0 0-1.5 1.5v10.5a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                                    </span>
                                    <span class="h-4 w-4 rounded-full border-2 border-yellow-200 peer-checked:border-amber-500"></span>
                                </span>
                                <span class="mt-3 text-sm font-extrabold text-stone-800">Transfer Bank</span>
                                <span class="mt-1 text-xs font-semibold leading-5 text-stone-400">BCA / BRI / Mandiri. Instruksi dikirim via WhatsApp.</span>
                            </span>
                        </label>

                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment_method" value="cod" x-model="payment" class="peer sr-only">
                            <span class="flex h-full flex-col rounded-2xl border border-yellow-200 bg-white p-4 transition hover:border-amber-300 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:ring-1 peer-checked:ring-amber-300">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-100 text-emerald-600">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                                    </span>
                                    <span class="h-4 w-4 rounded-full border-2 border-yellow-200"></span>
                                </span>
                                <span class="mt-3 text-sm font-extrabold text-stone-800">Bayar di Tempat (COD)</span>
                                <span class="mt-1 text-xs font-semibold leading-5 text-stone-400">Bayar tunai saat paket diterima kurir.</span>
                            </span>
                        </label>
                    </div>

                    @error('payment_method') <p class="mt-2 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <aside>
                <div class="sticky top-28 rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                    <h2 class="font-display text-xl font-bold text-stone-900">Pesananmu</h2>

                    <div class="mt-5 max-h-72 space-y-3 overflow-y-auto pr-1">
                        @foreach ($items as $item)
                            <div class="flex items-center gap-3">
                                <div class="h-14 w-14 flex-none overflow-hidden rounded-xl bg-yellow-50">
                                    <x-store.product-image :product="$item['product']" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-extrabold text-stone-800">{{ $item['product']->name }}</p>
                                    <p class="text-[11px] font-semibold text-stone-400">
                                        {{ $item['quantity'] }}x{{ $item['size'] ? ' · '.$item['size'] : '' }}
                                    </p>
                                </div>
                                <p class="text-xs font-extrabold text-stone-700">Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>

                    <dl class="mt-5 space-y-3 border-t border-dashed border-yellow-200 pt-5 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-semibold text-stone-500">Subtotal</dt>
                            <dd class="font-extrabold text-stone-800">Rp{{ number_format($subtotal, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-semibold text-stone-500">Ongkos kirim</dt>
                            <dd class="font-extrabold {{ $shipping === 0 ? 'text-emerald-600' : 'text-stone-800' }}">
                                {{ $shipping === 0 ? 'Gratis' : 'Rp'.number_format($shipping, 0, ',', '.') }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-5 flex items-end justify-between border-t border-dashed border-yellow-200 pt-5">
                        <p class="text-sm font-extrabold text-stone-900">Total</p>
                        <p class="font-display text-2xl font-extrabold text-amber-600">Rp{{ number_format($total, 0, ',', '.') }}</p>
                    </div>

                    <button type="submit" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3.5 text-sm font-extrabold text-white shadow-soft transition hover:bg-amber-600">
                        Buat pesanan
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </button>

                    <p class="mt-4 text-center text-[11px] font-semibold leading-5 text-stone-400">
                        Dengan membuat pesanan, kamu menyetujui syarat &amp; ketentuan Mamikhevanshop. Admin akan menghubungi via WhatsApp untuk konfirmasi.
                    </p>
                </div>
            </aside>
        </form>
    </section>
@endsection
