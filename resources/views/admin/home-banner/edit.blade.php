@extends('layouts.admin')

@section('title', 'Banner Beranda')
@section('heading', 'Master banner beranda')
@section('subheading', 'Atur teks, gambar, dan gaya visual banner utama.')

@section('content')
    @if ($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">Periksa kembali isian dan gambar banner.</div>
    @endif
    <form action="{{ route('admin.home-banner.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')
        <section class="grid gap-4 rounded-3xl border border-stone-200 bg-white p-6 sm:grid-cols-2">
            <label class="text-xs font-bold text-stone-600">Teks kecil di atas judul<input name="eyebrow" maxlength="80" required value="{{ old('eyebrow', $banner->eyebrow) }}" class="mt-2 w-full rounded-xl border-stone-200 text-sm"></label>
            <label class="text-xs font-bold text-stone-600">Judul utama<input name="headline" maxlength="180" required value="{{ old('headline', $banner->headline) }}" class="mt-2 w-full rounded-xl border-stone-200 text-sm"></label>
            <label class="text-xs font-bold text-stone-600 sm:col-span-2">Deskripsi<textarea name="description" rows="3" maxlength="500" class="mt-2 w-full rounded-xl border-stone-200 text-sm">{{ old('description', $banner->description) }}</textarea></label>
            <label class="text-xs font-bold text-stone-600">Desain kartu produk<select name="design" class="mt-2 w-full rounded-xl border-stone-200 text-sm"><option value="collage" @selected($banner->design === 'collage')>Kolase bertumpuk</option><option value="single" @selected($banner->design === 'single')>Satu gambar utama</option><option value="stacked" @selected($banner->design === 'stacked')>Tiga kartu rapi</option></select></label>
            <label class="text-xs font-bold text-stone-600">Tema warna<select name="background" class="mt-2 w-full rounded-xl border-stone-200 text-sm"><option value="warm" @selected($banner->background === 'warm')>Hangat amber</option><option value="mint" @selected($banner->background === 'mint')>Mint segar</option><option value="lavender" @selected($banner->background === 'lavender')>Lavender lembut</option></select></label>
        </section>
        <section class="grid gap-4 rounded-3xl border border-stone-200 bg-white p-6 lg:grid-cols-3">
            @foreach ($banner->cards ?? [] as $index => $card)
                <div class="rounded-2xl border border-stone-100 p-4">
                    <h2 class="font-display font-bold text-stone-800">Kartu {{ $index + 1 }}</h2>
                    @php($selectedProduct = $products->firstWhere('id', old("cards.$index.product_id", $card['product_id'] ?? null)))
                    <img id="banner-product-preview-{{ $index }}" class="{{ $selectedProduct?->image_url ? 'mt-3' : 'mt-3 hidden' }} aspect-square w-full rounded-xl object-cover" src="{{ $selectedProduct?->image_url ?? '' }}" alt="Preview {{ $selectedProduct?->name ?? 'produk banner' }}">
                    <label class="mt-3 block text-xs font-bold text-stone-600">Pilih produk (nama, gambar, harga, dan tautan detail diambil otomatis)<select name="cards[{{ $index }}][product_id]" required onchange="const image = document.getElementById('banner-product-preview-{{ $index }}'); const source = this.selectedOptions[0].dataset.image || ''; image.src = source; image.alt = 'Preview ' + (this.selectedOptions[0].text || 'produk banner'); image.classList.toggle('hidden', !source);" class="mt-1 w-full rounded-lg border-stone-200 text-sm"><option value="" data-image="">Pilih produk</option>@foreach ($products as $product)<option value="{{ $product->id }}" data-image="{{ $product->image_url }}" @selected((string) old("cards.$index.product_id", $card['product_id'] ?? '') === (string) $product->id)>{{ $product->name }} — {{ $product->price_formatted }}</option>@endforeach</select></label>
                    <label class="mt-3 block text-xs font-bold text-stone-600">Label<input name="cards[{{ $index }}][label]" maxlength="40" value="{{ old("cards.$index.label", $card['label'] ?? '') }}" placeholder="Favorit, Newborn, Sale" class="mt-1 w-full rounded-lg border-stone-200 text-sm"></label>
                </div>
            @endforeach
        </section>
        <div class="flex items-center justify-between rounded-2xl border border-stone-200 bg-white p-4">
            <label class="flex items-center gap-2 text-sm font-bold text-stone-700"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($banner->is_active) class="rounded border-stone-300 text-amber-500">Tampilkan banner ini</label>
            <button class="rounded-xl bg-amber-500 px-5 py-3 text-sm font-extrabold text-white">Simpan banner</button>
        </div>
    </form>
@endsection
