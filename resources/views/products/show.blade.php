@extends('layouts.storefront')

@section('title', $product->name . ' — Mamikhevanshop')
@section('meta_description', Str::limit($product->description, 150))

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <nav class="flex flex-wrap items-center gap-2 text-xs font-bold text-stone-400"
             aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="transition hover:text-amber-600">Beranda</a>
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
            <a href="{{ route('products.index') }}" class="transition hover:text-amber-600">Katalog</a>
            @if ($product->category)
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
                <a href="{{ route('products.index', ['category' => $product->category->slug]) }}"
                   class="transition hover:text-amber-600">{{ $product->category->name }}</a>
            @endif
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
            <span class="text-amber-600">{{ $product->name }}</span>
        </nav>

        <div class="mt-6 grid gap-8 lg:grid-cols-2 lg:gap-12">
            @php
                $gallery = $product->galleryImageUrls();
            @endphp

            <div x-data="{ active: 0, images: @js($gallery) }">
                <div
                     class="relative overflow-hidden rounded-[2.5rem] border border-yellow-100 bg-white p-3 shadow-sm">
                    <div class="aspect-square overflow-hidden rounded-[2rem] bg-yellow-50">
                        <template x-if="images.length">
                            <img :src="images[active]" alt="Foto {{ $product->name }}"
                                 class="h-full w-full object-cover transition duration-500 hover:scale-[1.02]">
                        </template>
                        <template x-if="!images.length">
                            <x-store.product-image :product="$product"
                                                   class="transition duration-500 hover:scale-[1.02]" />
                        </template>
                    </div>

                    <div class="absolute left-6 top-6 flex flex-col items-start gap-2">
                        @if ($product->hasDiscount())
                            <span
                                  class="rounded-full bg-amber-500 px-3 py-1.5 text-xs font-extrabold text-white shadow-sm">Hemat
                                {{ $product->discountPercent() }}%</span>
                        @endif
                        @if ($product->is_featured)
                            <span
                                  class="rounded-full bg-white/90 px-3 py-1.5 text-xs font-extrabold text-yellow-700 shadow-sm backdrop-blur">Favorit
                                orang tua</span>
                        @endif
                    </div>
                </div>

                @if (count($gallery) > 1)
                    <div class="mt-3 flex gap-2 overflow-x-auto pb-1">
                        <template x-for="(image, index) in images" :key="index">
                            <button type="button" @click="active = index"
                                    class="h-16 w-16 flex-none overflow-hidden rounded-xl border-2 bg-white p-0.5 transition"
                                    :class="active === index ? 'border-amber-500' : 'border-yellow-100 hover:border-amber-300'"
                                    :aria-label="'Tampilkan foto ' + (index + 1)">
                                <img :src="image" alt="" class="h-full w-full rounded-lg object-cover">
                            </button>
                        </template>
                    </div>
                @endif

                <div class="mt-4 grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-yellow-100 bg-white p-3 text-center">
                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">
                            Bahan</p>
                        <p class="mt-1 text-xs font-bold text-stone-700">
                            {{ $product->material ?: 'Katun lembut' }}</p>
                    </div>
                    <div class="rounded-2xl border border-yellow-100 bg-white p-3 text-center">
                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">
                            Usia</p>
                        <p class="mt-1 text-xs font-bold text-stone-700">
                            {{ $product->age_group ?: 'Anak' }}</p>
                    </div>
                    <div class="rounded-2xl border border-yellow-100 bg-white p-3 text-center">
                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">
                            Untuk</p>
                        <p class="mt-1 text-xs font-bold text-stone-700">{{ $product->genderLabel() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="lg:py-2">
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-amber-400">
                    {{ $product->category?->name ?? 'Pakaian Anak' }}</p>
                <h1
                    class="mt-2 font-display text-3xl font-extrabold leading-tight text-stone-900 sm:text-4xl">
                    {{ $product->name }}</h1>

                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-1 text-amber-400">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                      d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.539 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.98 10.1c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69l1.519-4.674Z" />
                            </svg>
                        @endfor
                        <span class="ml-1 text-xs font-bold text-stone-500">4,9 (128 ulasan)</span>
                    </div>
                    @if ($product->isInStock())
                        <span
                              class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-extrabold text-emerald-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Stok tersedia
                        </span>
                    @else
                        <span
                              class="rounded-full bg-amber-50 px-3 py-1 text-xs font-extrabold text-amber-600">Stok
                            habis</span>
                    @endif
                </div>

                <div class="mt-5 flex flex-wrap items-end gap-3">
                    <p class="font-display text-4xl font-extrabold text-amber-600">
                        {{ $product->price_formatted }}</p>
                    @if ($product->hasDiscount())
                        <p class="pb-1 text-base font-bold text-stone-400 line-through">
                            {{ $product->compare_price_formatted }}</p>
                    @endif
                </div>

                <p class="mt-5 text-sm leading-7 text-stone-600">{!! nl2br(e($product->description)) !!}</p>

                <form action="{{ route('cart.add') }}" method="POST" x-data="{ qty: 1, max: {{ max(1, $product->stock) }} }"
                      class="mt-7 rounded-3xl border border-yellow-100 bg-white p-5 shadow-sm">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    @if (count($product->sizeList()))
                        <div>
                            <div class="flex items-center justify-between">
                                <p
                                   class="text-xs font-extrabold uppercase tracking-wider text-stone-400">
                                    Pilih ukuran</p>
                                <a href="{{ route('contact.index') }}"
                                   class="text-xs font-bold text-amber-600 hover:text-yellow-700">Panduan
                                    ukuran</a>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($product->sizeList() as $index => $size)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="size"
                                               value="{{ $size }}" class="peer sr-only"
                                               @checked($index === 0)>
                                        <span
                                              class="inline-flex min-w-14 items-center justify-center rounded-xl border border-yellow-200 bg-white px-4 py-2.5 text-sm font-bold text-stone-600 transition hover:border-amber-300 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-yellow-700 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-200">
                                            {{ $size }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-5">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-stone-400">
                                Jumlah</p>
                            <p class="text-xs font-semibold text-stone-400">Sisa {{ $product->stock }}
                                buah</p>
                        </div>
                        <div class="mt-2 flex items-center gap-3">
                            <div
                                 class="inline-flex items-center rounded-xl border border-yellow-200 bg-white">
                                <button type="button" @click="qty = Math.max(1, qty - 1)"
                                        class="grid h-11 w-11 place-items-center text-lg font-bold text-stone-500 transition hover:text-amber-600"
                                        aria-label="Kurangi jumlah">&minus;</button>
                                <input type="number" name="quantity" x-model.number="qty"
                                       min="1" max="{{ max(1, $product->stock) }}"
                                       class="w-14 border-0 bg-transparent p-0 text-center text-sm font-extrabold text-stone-800 focus:ring-0"
                                       aria-label="Jumlah">
                                <button type="button" @click="qty = Math.min(max, qty + 1)"
                                        class="grid h-11 w-11 place-items-center text-lg font-bold text-stone-500 transition hover:text-amber-600"
                                        aria-label="Tambah jumlah">+</button>
                            </div>
                            <span class="text-xs font-semibold text-stone-400">Subtotal: <span
                                      class="font-extrabold text-stone-700"
                                      x-text="'Rp' + ({{ $product->price }} * qty).toLocaleString('id-ID')"></span></span>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                        <button type="submit" @disabled(!$product->isInStock())
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3.5 text-sm font-extrabold text-white shadow-soft transition hover:bg-amber-600 disabled:cursor-not-allowed disabled:bg-stone-200 disabled:text-stone-400 disabled:shadow-none">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                            </svg>
                            {{ $product->isInStock() ? 'Tambah ke Keranjang' : 'Stok Habis' }}
                        </button>
                        <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Mamikhevanshop, saya ingin bertanya tentang ' . $product->name . ' (' . $product->price_formatted . ').') }}"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3.5 text-sm font-extrabold text-emerald-600 transition hover:border-emerald-300 hover:bg-emerald-100">
                            Tanya via WA
                        </a>
                    </div>

                    @error('size')
                        <p class="mt-3 text-xs font-bold text-amber-600">{{ $message }}</p>
                    @enderror
                    @error('cart')
                        <p class="mt-3 text-xs font-bold text-amber-600">{{ $message }}</p>
                    @enderror
                </form>

                @if ($product->sizeGuideType && count($product->sizeList()) > 0)
                    @php
                        $guideType = $product->sizeGuideType;
                        $guideEntries = $guideType->getEntriesForLabels($product->sizeList());
                        $measurementFields = $guideType->measurement_fields ?? [];
                    @endphp
                    @if ($guideEntries->isNotEmpty() && count($measurementFields) > 0)
                        <div class="mt-6 rounded-3xl border border-yellow-100 bg-white p-5 shadow-sm">
                            <h3 class="text-sm font-extrabold text-stone-800">📏 Panduan Ukuran —
                                {{ $guideType->name }}</h3>
                            <p class="mt-1 text-xs font-semibold text-stone-400">Pengukuran dalam
                                satuan cm</p>
                            <div class="mt-4 overflow-x-auto">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="border-b border-stone-100">
                                            <th
                                                class="pb-2 pr-3 text-left font-extrabold uppercase tracking-wider text-stone-400">
                                                Ukuran</th>
                                            <th
                                                class="pb-2 pr-3 text-left font-extrabold uppercase tracking-wider text-stone-400">
                                                Usia</th>
                                            <th
                                                class="pb-2 pr-3 text-left font-extrabold uppercase tracking-wider text-stone-400">
                                                BB (kg)</th>
                                            <th
                                                class="pb-2 pr-3 text-left font-extrabold uppercase tracking-wider text-stone-400">
                                                TB (cm)</th>
                                            @foreach ($measurementFields as $field)
                                                <th
                                                    class="pb-2 pr-3 text-left font-extrabold uppercase tracking-wider text-stone-400">
                                                    {{ \App\Models\SizeGuideEntry::MEASUREMENT_LABELS[$field] ?? $field }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-stone-50">
                                        @foreach ($guideEntries as $entry)
                                            <tr class="transition hover:bg-yellow-50/40">
                                                <td class="py-2 pr-3 font-extrabold text-stone-800">
                                                    {{ $entry->label }}</td>
                                                <td class="py-2 pr-3 text-stone-500">
                                                    {{ $entry->age_range ?: '-' }}</td>
                                                <td class="py-2 pr-3 text-stone-500">
                                                    {{ $entry->weight_kg ?: '-' }}</td>
                                                <td class="py-2 pr-3 text-stone-500">
                                                    {{ $entry->height_cm ?: '-' }}</td>
                                                @foreach ($measurementFields as $field)
                                                    <td class="py-2 pr-3 text-stone-500">
                                                        {{ $entry->$field ?: '-' }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @endif

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="flex items-start gap-2.5 rounded-2xl bg-yellow-50/70 p-3">
                        <svg class="mt-0.5 h-5 w-5 flex-none text-amber-600" fill="none"
                             viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                        <p class="text-[11px] font-semibold leading-5 text-stone-500">Gratis ongkir
                            min. Rp300.000</p>
                    </div>
                    <div class="flex items-start gap-2.5 rounded-2xl bg-yellow-50/70 p-3">
                        <svg class="mt-0.5 h-5 w-5 flex-none text-amber-600" fill="none"
                             viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <p class="text-[11px] font-semibold leading-5 text-stone-500">Bahan aman &amp;
                            bersertifikat</p>
                    </div>
                    <div class="flex items-start gap-2.5 rounded-2xl bg-yellow-50/70 p-3">
                        <svg class="mt-0.5 h-5 w-5 flex-none text-amber-600" fill="none"
                             viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <p class="text-[11px] font-semibold leading-5 text-stone-500">Tukar ukuran
                            dalam 3 hari</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pb-10 pt-6 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-amber-400">Lengkapi
                        lemari si kecil</p>
                    <h2 class="mt-2 font-display text-2xl font-extrabold text-stone-900 sm:text-3xl">
                        Produk serupa</h2>
                </div>
                @if ($product->category)
                    <a href="{{ route('products.index', ['category' => $product->category->slug]) }}"
                       class="hidden items-center gap-2 text-sm font-extrabold text-amber-600 hover:text-yellow-700 sm:inline-flex">
                        Lihat kategori
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                @endif
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
                @foreach ($relatedProducts as $related)
                    <x-store.product-card :product="$related" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
