@extends('layouts.storefront')

@section('title', 'Lacak Pesanan — Mamikhevanshop')

@section('content')
    <section class="mx-auto max-w-xl px-4 py-10 sm:px-6 sm:py-14">
        <h1 class="font-display text-3xl font-extrabold text-stone-900">Lacak pesanan</h1>
        <p class="mt-2 text-sm text-stone-500">Masukkan kode pesanan dan nomor WhatsApp yang digunakan saat checkout.</p>

        <form method="POST" action="{{ route('orders.lookup') }}" class="mt-8 space-y-5 rounded-2xl border border-yellow-100 bg-white p-6 shadow-sm">
            @csrf
            <div>
                <label for="code" class="text-sm font-bold text-stone-700">Kode pesanan</label>
                <input id="code" name="code" type="text" value="{{ strtoupper(preg_replace('/[^A-Za-z0-9]/', '', old('code', '')) ?? '') }}" required autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="30" inputmode="text" placeholder="KK261112ABCDE" pattern="[A-Za-z0-9]+" oninput="this.value = this.value.replace(/[^A-Za-z0-9]/g, '').toUpperCase()"
                       class="mt-2 w-full rounded-xl border-yellow-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                <p class="mt-1.5 text-xs text-stone-500">Ketik huruf dan angka saja, tanpa tanda hubung.</p>
                @error('code') <p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="phone" class="text-sm font-bold text-stone-700">Nomor WhatsApp</label>
                <div class="mt-2 flex gap-2">
                    <select id="country_code" name="country_code" aria-label="Kode negara" class="w-40 shrink-0 rounded-xl border-yellow-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                        @foreach ($countryCodes as $code => $country)
                            <option value="{{ $code }}" @selected((string) old('country_code', '62') === (string) $code)>{{ $country }} (+{{ $code }})</option>
                        @endforeach
                    </select>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required autocomplete="tel-national" inputmode="numeric" maxlength="15" pattern="[0-9]{7,15}" placeholder="81234567890" oninput="this.value = this.value.replace(/\D/g, '')"
                           class="min-w-0 flex-1 rounded-xl border-yellow-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                </div>
                <p class="mt-1.5 text-xs text-stone-500">Masukkan nomor setelah kode negara. Awalan 0 tetap boleh.</p>
                @error('country_code') <p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
                @error('phone') <p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full rounded-xl bg-amber-500 px-4 py-3 text-sm font-extrabold text-white hover:bg-amber-600">Lihat status pesanan</button>
        </form>
    </section>
@endsection
