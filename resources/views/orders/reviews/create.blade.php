@extends('layouts.storefront')

@section('title', 'Beri Penilaian — '.$order->code)

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <nav class="flex items-center gap-2 text-xs font-bold text-stone-400" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="transition hover:text-amber-600">Beranda</a>
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            <a href="{{ route('orders.show', $order) }}" class="transition hover:text-amber-600">Pesanan {{ $order->code }}</a>
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            <span class="text-amber-600">Beri penilaian</span>
        </nav>

        <div class="mt-4">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-amber-400">Terima kasih sudah berbelanja</p>
            <h1 class="mt-2 font-display text-3xl font-extrabold text-stone-900 sm:text-4xl">Beri penilaian</h1>
            <p class="mt-2 text-sm text-stone-500">
                Pesanan <span class="font-extrabold text-stone-700">{{ $order->code }}</span>
                @if ($order->delivered_at)
                    &middot; diterima {{ $order->delivered_at->translatedFormat('d F Y') }}
                @endif
            </p>
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-extrabold text-amber-700">Ada penilaian yang perlu diperiksa:</p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-xs font-semibold text-yellow-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('orders.reviews.store', $order) }}" method="POST" class="mt-6 space-y-5">
            @csrf

            @foreach ($items as $item)
                <div class="rounded-3xl border border-yellow-100 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-4">
                        <div class="h-16 w-16 flex-none overflow-hidden rounded-2xl bg-yellow-50">
                            @if ($item->product)
                                <x-store.product-image :product="$item->product" />
                            @else
                                <div class="grid h-full w-full place-items-center bg-yellow-50 text-2xl">👕</div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-extrabold text-stone-900">{{ $item->product_name }}</p>
                            <p class="mt-1 text-xs font-semibold text-stone-400">
                                {{ $item->quantity }} item
                                @if ($item->size) &middot; Ukuran {{ $item->size }} @endif
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-4 border-t border-stone-100 pt-5">
                        <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Rating keseluruhan <span class="text-amber-600">*</span></p>
                            <x-star-input :name="'reviews['.$item->id.'][rating]'" :value="old('reviews.'.$item->id.'.rating', 5)" size="h-7 w-7" />
                        </div>
                        @error('reviews.'.$item->id.'.rating') <p class="text-right text-xs font-bold text-amber-600">{{ $message }}</p> @enderror

                        <div class="grid gap-3 sm:grid-cols-3">
                            @foreach (\App\Models\Review::ASPECTS as $field => $label)
                                <div class="rounded-2xl bg-yellow-50/60 p-3">
                                    <p class="text-[11px] font-extrabold text-stone-500">{{ $label }}</p>
                                    <div class="mt-1.5">
                                        <x-star-input :name="'reviews['.$item->id.']['.$field.']'" :value="old('reviews.'.$item->id.'.'.$field, 5)" size="h-5 w-5" />
                                    </div>
                                    @error('reviews.'.$item->id.'.'.$field) <p class="mt-1 text-[10px] font-bold text-amber-600">{{ $message }}</p> @enderror
                                </div>
                            @endforeach
                        </div>

                        <div>
                            <label for="comment-{{ $item->id }}" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Ulasan <span class="font-semibold text-stone-400">(opsional)</span></label>
                            <textarea id="comment-{{ $item->id }}" name="reviews[{{ $item->id }}][comment]" rows="3" maxlength="1000"
                                      placeholder="Ceritakan kualitas bahan, ukuran, atau pengalamanmu memakai produk ini..."
                                      class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">{{ old('reviews.'.$item->id.'.comment') }}</textarea>
                            @error('reviews.'.$item->id.'.comment') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center justify-center rounded-xl border border-stone-200 px-4 py-2.5 text-xs font-extrabold text-stone-600 transition hover:bg-stone-50">
                    Nanti saja
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-amber-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    Kirim penilaian
                </button>
            </div>
        </form>

        @if ($reviewedItems->isNotEmpty())
            <div class="mt-6 rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-extrabold text-stone-800">Sudah dinilai</h2>
                <div class="mt-3 divide-y divide-stone-100">
                    @foreach ($reviewedItems as $item)
                        <div class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <p class="min-w-0 truncate text-xs font-semibold text-stone-600">{{ $item->product_name }}</p>
                            <x-star-display :rating="$item->review->rating" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
