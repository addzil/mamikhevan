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

        <form action="{{ route('checkout.store') }}" method="POST"
              x-data='checkoutShipping(@json(route("checkout.quote")), @json(csrf_token()), {{ $subtotal }}, @json(old("shipping_courier")), @json(old("payment_method")), @json(old("shipping_province")), @json(old("shipping_city")), @json(old("shipping_postal_code")), @json($komerceEnabled), @json(route("checkout.destinations")), @json(old("shipping_destination_id")), @json(old("shipping_district")), @json(old("shipping_subdistrict")), @json(old("voucher_code", $voucherCode)), @json(old("payment_destination")))'
              class="mt-8 grid gap-6 lg:grid-cols-[1fr_380px] lg:gap-8">
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
                            <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" required autocomplete="name"
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
                            <label for="customer_email" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Email <span class="text-amber-600">*</span></label>
                            <input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email', auth()->user()?->email) }}" autocomplete="email" required
                                   class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                            <p class="mt-1 text-[11px] text-stone-400">Konfirmasi pesanan, nominal pembayaran, dan nomor resi akan dikirim ke email ini.</p>
                            @error('customer_email') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="customer_address" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Alamat lengkap <span class="text-amber-600">*</span></label>
                            <textarea id="customer_address" name="customer_address" rows="3" required autocomplete="street-address"
                                      class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">{{ old('customer_address') }}</textarea>
                            <p class="mt-1 text-[11px] text-stone-400">Tulis jalan, nomor rumah, RT/RW, dan patokan jika ada.</p>
                            @error('customer_address') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>

                        @if ($komerceEnabled)
                            <div class="sm:col-span-2">
                                <label for="destination_search" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Cari wilayah tujuan <span class="text-amber-600">*</span></label>
                                <input id="destination_search" type="search" x-model="searchQuery" @input="invalidateDestination()" @input.debounce.350ms="searchDestinations()" placeholder="Ketik kelurahan, kecamatan, kota, atau kode pos"
                                       class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                                <input type="hidden" name="shipping_destination_id" x-model="destinationId">
                                <p x-show="searching" class="mt-2 text-xs text-stone-500">Mencari wilayah...</p>
                                <p x-show="searchError" x-text="searchError" class="mt-2 text-xs text-red-600"></p>
                                <div x-show="destinations.length > 0" class="mt-2 max-h-56 overflow-y-auto rounded-xl border border-yellow-200 bg-white shadow-sm">
                                    <template x-for="destination in destinations" :key="destination.id">
                                        <button type="button" @click="chooseDestination(destination)" class="block w-full border-b border-yellow-100 px-4 py-3 text-left text-xs hover:bg-amber-50" x-text="destination.label"></button>
                                    </template>
                                </div>
                                @error('shipping_destination_id') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <div>
                            <label for="shipping_province" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Provinsi <span class="text-amber-600">*</span></label>
                            <input id="shipping_province" name="shipping_province" type="text" list="shipping-provinces" x-model="province" @if (! $komerceEnabled) @input.debounce.500ms="refresh()" @else readonly @endif required value="{{ old('shipping_province') }}"
                                   class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                            <datalist id="shipping-provinces">
                                <option value="DKI Jakarta"><option value="Jawa Barat"><option value="Banten"><option value="Jawa Tengah"><option value="DI Yogyakarta"><option value="Jawa Timur"><option value="Bali">
                            </datalist>
                            @error('shipping_province') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="shipping_city" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Kota/Kabupaten <span class="text-amber-600">*</span></label>
                            <input id="shipping_city" name="shipping_city" type="text" x-model="city" @if (! $komerceEnabled) @input.debounce.500ms="refresh()" @else readonly @endif required value="{{ old('shipping_city') }}"
                                   class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                            @error('shipping_city') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="shipping_district" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Kecamatan <span class="text-amber-600">*</span></label>
                            <input id="shipping_district" name="shipping_district" type="text" x-model="district" value="{{ old('shipping_district') }}" @if ($komerceEnabled) readonly @endif required
                                   class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                            @error('shipping_district') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="shipping_subdistrict" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Kelurahan/Desa <span class="text-amber-600">*</span></label>
                            <input id="shipping_subdistrict" name="shipping_subdistrict" type="text" x-model="subdistrict" value="{{ old('shipping_subdistrict') }}" @if ($komerceEnabled) readonly @endif required
                                   class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                            @error('shipping_subdistrict') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="shipping_postal_code" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Kode pos <span class="text-amber-600">*</span></label>
                            <input id="shipping_postal_code" name="shipping_postal_code" type="text" inputmode="numeric" pattern="[0-9]{5}" maxlength="5" x-model="postalCode" @if (! $komerceEnabled) @input.debounce.500ms="refresh()" @else readonly @endif required value="{{ old('shipping_postal_code') }}"
                                   class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                            @error('shipping_postal_code') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
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
                            <h2 class="font-display text-xl font-bold text-stone-900">Pilihan pengiriman</h2>
                            <p class="text-xs font-semibold text-stone-400">Dikirim dari Desa Tambun, Tambun Selatan, Kabupaten Bekasi 17510.</p>
                        </div>
                    </div>

                    @if ($komerceEnabled)
                        <p class="mt-4 text-xs leading-5 text-stone-500">Pilih wilayah dari hasil pencarian untuk melihat tarif JNE/J&amp;T dari Komerce. GoSend SameDay memakai tarif toko yang ditampilkan saat checkout.</p>
                    @else
                        <p class="mt-4 text-xs leading-5 text-stone-500">Isi provinsi, kota/kabupaten, dan kode pos untuk melihat ongkir toko. Nominal yang ditampilkan saat checkout menjadi bagian dari total pembayaran.</p>
                    @endif
                    <p x-show="loading" class="mt-4 text-sm font-semibold text-stone-500">Menghitung estimasi ongkir...</p>
                    <p x-show="error" x-text="error" class="mt-4 text-sm font-semibold text-red-600"></p>
                    <p x-show="!loading && options.length === 0" class="mt-4 text-sm font-semibold text-stone-500">Lengkapi alamat tujuan untuk memilih kurir.</p>

                    <div x-show="options.length > 0" class="mt-5 space-y-3">
                        <template x-for="option in options" :key="option.courier">
                            <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-yellow-200 bg-white p-4 hover:border-amber-300">
                                <span class="flex items-center gap-3">
                                    <input type="radio" name="shipping_courier" :value="option.courier" x-model="selectedCourier" @change="if (selectedCourier === 'gosend' && payment === 'cod') payment = 'transfer'" required class="text-amber-600 focus:ring-amber-200">
                                    <span>
                                        <span class="block text-sm font-extrabold text-stone-800" x-text="option.label"></span>
                                        <span class="block text-xs text-stone-400" x-text="option.eta"></span>
                                        <span x-show="option.source === 'internal_rate'" class="block text-[11px] text-amber-700">Tarif toko</span>
                                    </span>
                                </span>
                                <span class="text-sm font-extrabold text-amber-700" x-text="option.cost === 0 ? 'Gratis' : formatRupiah(option.cost)"></span>
                            </label>
                        </template>
                    </div>
                    <p x-show="usesAssumedWeight && options.length > 0" class="mt-3 text-xs text-amber-700">Berat paket sementara memakai asumsi 500 gram per item yang belum memiliki berat produk.</p>
                    <p x-show="options.length > 0" class="mt-1 text-xs text-stone-500">Berat estimasi: <span x-text="weightGrams"></span> gram. GoSend SameDay hanya ditampilkan untuk area Jadetabek dan paket maksimal 5 kg; jarak serta ketersediaan akhir diperiksa admin.</p>
                    @error('shipping_courier') <p class="mt-2 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                </div>

                <div class="rounded-3xl border border-yellow-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-2xl bg-amber-50 text-sm font-extrabold text-amber-600">3</span>
                        <div>
                            <h2 class="font-display text-xl font-bold text-stone-900">Metode pembayaran</h2>
                            <p class="text-xs font-semibold text-stone-400">Pilih metode pembayaran dan satu rekening atau QRIS bila membayar di muka.</p>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment_method" value="transfer" x-model="payment" @change="paymentDestination = ''" @disabled($paymentDestinations === []) required class="peer sr-only">
                            <span class="flex h-full flex-col rounded-2xl border border-yellow-200 bg-white p-4 transition hover:border-amber-300 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:ring-1 peer-checked:ring-amber-300">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-amber-100 text-yellow-700">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h16.5a1.5 1.5 0 0 0 1.5-1.5V6.75a1.5 1.5 0 0 0-1.5-1.5H3.75a1.5 1.5 0 0 0-1.5 1.5v10.5a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                                    </span>
                                    <span class="h-4 w-4 rounded-full border-2 border-yellow-200 peer-checked:border-amber-500"></span>
                                </span>
                                <span class="mt-3 text-sm font-extrabold text-stone-800">Transfer Bank</span>
                                <span class="mt-1 text-xs font-semibold leading-5 text-stone-400">{{ $paymentDestinations === [] ? 'Rekening dan QRIS belum tersedia.' : 'Pilih rekening atau QRIS di bawah. Bayar sesuai total di halaman pesanan atau email.' }}</span>
                            </span>
                        </label>

                        <label class="relative cursor-pointer" :class="selectedCourier === 'gosend' ? 'cursor-not-allowed opacity-50' : ''">
                            <input type="radio" name="payment_method" value="cod" x-model="payment" @change="paymentDestination = ''" :disabled="selectedCourier === 'gosend' || {{ $codAvailable ? 'false' : 'true' }}" required class="peer sr-only">
                            <span class="flex h-full flex-col rounded-2xl border border-yellow-200 bg-white p-4 transition hover:border-amber-300 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:ring-1 peer-checked:ring-amber-300">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-100 text-emerald-600">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                                    </span>
                                    <span class="h-4 w-4 rounded-full border-2 border-yellow-200"></span>
                                </span>
                                <span class="mt-3 text-sm font-extrabold text-stone-800">Bayar di Tempat (COD)</span>
                                <span class="mt-1 text-xs font-semibold leading-5 text-stone-400">{{ $codAvailable ? 'COD tersedia untuk seluruh produk di keranjang. Tidak berlaku untuk GoSend SameDay.' : 'COD tidak tersedia karena ada produk di keranjang yang hanya menerima pembayaran transfer.' }}</span>
                            </span>
                        </label>
                    </div>

                    @error('payment_method') <p class="mt-2 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror

                    <div x-show="payment === 'transfer'" x-cloak class="mt-5 border-t border-yellow-100 pt-5">
                        <p class="text-sm font-extrabold text-stone-800">Tujuan pembayaran <span class="text-red-500">*</span></p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach ($paymentDestinations as $key => $destination)
                                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-yellow-200 p-4 hover:border-amber-400">
                                    <input type="radio" name="payment_destination" value="{{ $key }}" x-model="paymentDestination" :required="payment === 'transfer'" class="mt-1 text-amber-600 focus:ring-amber-200">
                                    <span class="text-xs leading-5 text-stone-600">
                                        @if ($destination['type'] === 'bank')
                                            <strong class="block text-sm text-stone-800">{{ $destination['bank'] }}</strong>
                                            {{ $destination['account_number'] }}<br>a.n. {{ $destination['account_name'] }}
                                        @else
                                            <strong class="block text-sm text-stone-800">QRIS</strong>
                                            Pindai kode QR pada email atau halaman pesanan.
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('payment_destination') <p class="mt-2 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                    </div>
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

                        @if ($giveaway)
                            <div class="flex items-center gap-3">
                                <div class="h-14 w-14 flex-none overflow-hidden rounded-xl bg-emerald-50">
                                    <x-store.product-image :product="$giveaway['gift_product']" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-extrabold text-stone-800">{{ $giveaway['gift_product']->name }}</p>
                                    <p class="text-[11px] font-semibold text-emerald-700">
                                        {{ $giveaway['gift_quantity'] }}x · Hadiah gratis{{ $giveaway['promotion']->gift_size ? ' · '.$giveaway['promotion']->gift_size : '' }}
                                    </p>
                                </div>
                                <p class="text-xs font-extrabold text-emerald-700">Gratis</p>
                            </div>
                        @endif
                    </div>

                    @if ($giveaway)
                        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-3">
                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Klaim hadiah aktif</p>
                            <p class="mt-1 text-xs font-semibold leading-5 text-emerald-800">{{ $giveaway['gift_quantity'] }} &times; {{ $giveaway['gift_product']->name }} gratis. Kamu hanya membayar ongkos kirim.</p>
                            <form action="{{ route('giveaway.clear') }}" method="POST" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[11px] font-extrabold text-emerald-700 underline transition hover:text-emerald-900">Batalkan klaim hadiah</button>
                            </form>
                        </div>
                    @endif

                    <div class="mt-5 rounded-2xl border border-dashed border-yellow-200 bg-yellow-50/40 p-3">
                        <label for="voucher_code" class="text-[10px] font-extrabold uppercase tracking-wider text-stone-500">Kode voucher</label>
                        <div class="mt-1.5 flex gap-2">
                            <input id="voucher_code" name="voucher_code" type="text" maxlength="40" x-model="voucherCode" value="{{ old('voucher_code', $voucherCode) }}" @keydown.enter.prevent="applyVoucher()" placeholder="Contoh: HEMAT10"
                                   class="w-full rounded-xl border-yellow-200 py-2 text-xs font-bold uppercase focus:border-amber-300 focus:ring-amber-200">
                            <button type="button" @click="applyVoucher()" class="rounded-xl border border-amber-200 bg-white px-3 text-xs font-extrabold text-amber-700 hover:bg-amber-50">Terapkan</button>
                        </div>
                        <p x-show="voucherError" x-text="voucherError" class="mt-1.5 text-[11px] font-bold text-red-600"></p>
                        <p x-show="!voucherError && selectedOption?.promotion?.voucher_code" class="mt-1.5 text-[11px] font-bold text-emerald-700" x-text="selectedOption?.promotion?.voucher_code ? 'Voucher ' + selectedOption.promotion.voucher_code + ' diterapkan.' : ''"></p>
                    </div>

                    <dl class="mt-5 space-y-3 border-t border-dashed border-yellow-200 pt-5 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-semibold text-stone-500">Subtotal</dt>
                            <dd class="font-extrabold text-stone-800">Rp{{ number_format($subtotal, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-semibold text-stone-500">Ongkos kirim</dt>
                            <dd class="font-extrabold text-stone-800" x-text="selectedOption ? (selectedOption.cost === 0 ? 'Gratis' : formatRupiah(selectedOption.cost)) : 'Pilih kurir'"></dd>
                        </div>
                        <template x-if="selectedOption?.promotion">
                            <div class="space-y-2 border-t border-dashed border-yellow-200 pt-3">
                                <p class="text-xs font-extrabold text-amber-700" x-text="'Promo: ' + selectedOption.promotion.name"></p>
                                <p x-show="selectedOption.promotion.product_discount > 0" class="flex justify-between gap-4 text-xs"><span>Diskon produk</span><span x-text="'-' + formatRupiah(selectedOption.promotion.product_discount)"></span></p>
                                <p x-show="selectedOption.promotion.shipping_discount > 0" class="flex justify-between gap-4 text-xs"><span>Diskon ongkir</span><span x-text="'-' + formatRupiah(selectedOption.promotion.shipping_discount)"></span></p>
                                <p x-show="selectedOption.promotion.tier_min_quantity > 0" class="text-xs font-semibold text-amber-700" x-text="'Diskon bertingkat mulai ' + selectedOption.promotion.tier_min_quantity + ' item'"></p>
                                <p x-show="selectedOption.promotion.gift_quantity > 0" class="text-xs font-semibold text-emerald-700" x-text="'Hadiah gratis: ' + selectedOption.promotion.gift_quantity + ' × ' + selectedOption.promotion.gift_name"></p>
                            </div>
                        </template>
                    </dl>

                    <div class="mt-5 flex items-end justify-between border-t border-dashed border-yellow-200 pt-5">
                        <p class="text-sm font-extrabold text-stone-900">Total estimasi</p>
                        <p class="font-display text-2xl font-extrabold text-amber-600" x-text="selectedOption ? formatRupiah(estimatedTotal) : '—'"></p>
                    </div>

                    <p x-show="payment === 'transfer' && selectedOption" class="mt-2 text-xs text-stone-500">Kode unik Rp1–199 ditambahkan ke nominal transfer setelah pesanan dibuat.</p>

                    <button type="submit" :disabled="!canSubmit" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3.5 text-sm font-extrabold text-white shadow-soft transition hover:bg-amber-600 disabled:cursor-not-allowed disabled:opacity-50">
                        Buat pesanan
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </button>

                    <p class="mt-4 text-center text-[11px] font-semibold leading-5 text-stone-400">
                        Dengan membuat pesanan, kamu menyetujui syarat &amp; ketentuan Mamikhevanshop. Rincian pembayaran langsung tersedia di halaman pesanan dan dikirim melalui email.
                    </p>
                </div>
            </aside>
        </form>
    </section>
@endsection
