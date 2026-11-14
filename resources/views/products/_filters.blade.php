@php
    $selectedCategory = request('category');
    $selectedGender = request('gender');
    $selectedPrice = (int) request('max_price');
    $currentSort = request('sort', 'latest');
@endphp

@if ($saleOnly)
    <input type="hidden" name="sale" value="1">
@endif

<div>
    <label for="filter-q" class="text-xs font-extrabold uppercase tracking-wider text-stone-400">Cari produk</label>
    <div class="relative mt-2">
        <input id="filter-q" type="search" name="q" value="{{ $search }}"
               placeholder="Nama produk..."
               class="w-full rounded-xl border-yellow-200 py-2.5 pl-9 pr-3 text-sm focus:border-amber-300 focus:ring-amber-200">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
    </div>
</div>

<div>
    <p class="text-xs font-extrabold uppercase tracking-wider text-stone-400">Kategori</p>
    <div class="mt-2 space-y-1">
        <a href="{{ route('products.index', array_merge(request()->except('category', 'page'), ['q' => $search ?: null, 'gender' => $selectedGender, 'max_price' => $selectedPrice ?: null, 'sort' => $currentSort])) }}"
           class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-bold transition {{ ! $selectedCategory ? 'bg-amber-50 text-yellow-700' : 'text-stone-600 hover:bg-yellow-50' }}">
            <span>Semua kategori</span>
            <span class="text-xs text-stone-400">{{ $categories->sum('products_count') }}</span>
        </a>
        @foreach ($categories as $category)
            <a href="{{ route('products.index', array_merge(request()->except('page'), ['category' => $category->slug, 'q' => $search ?: null, 'gender' => $selectedGender, 'max_price' => $selectedPrice ?: null, 'sort' => $currentSort])) }}"
               class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-bold transition {{ $selectedCategory === $category->slug ? 'bg-amber-50 text-yellow-700' : 'text-stone-600 hover:bg-yellow-50' }}">
                <span class="flex items-center gap-2"><span>{{ $category->icon }}</span>{{ $category->name }}</span>
                <span class="text-xs text-stone-400">{{ $category->products_count }}</span>
            </a>
        @endforeach
    </div>
</div>

<div>
    <p class="text-xs font-extrabold uppercase tracking-wider text-stone-400">Untuk</p>
    <div class="mt-2 space-y-1.5">
        @foreach (['' => 'Semua anak', 'uniseks' => 'Uniseks', 'laki-laki' => 'Anak laki-laki', 'perempuan' => 'Anak perempuan'] as $value => $label)
            <label class="flex cursor-pointer items-center gap-2.5 rounded-xl px-2 py-1.5 text-sm font-semibold text-stone-600 transition hover:bg-yellow-50">
                <input type="radio" name="gender" value="{{ $value }}" @checked($selectedGender === $value)
                       class="border-yellow-300 text-amber-600 focus:ring-amber-200">
                {{ $label }}
            </label>
        @endforeach
    </div>
</div>

<div>
    <label for="filter-price" class="text-xs font-extrabold uppercase tracking-wider text-stone-400">Harga maksimal</label>
    <select id="filter-price" name="max_price"
            class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        <option value="">Semua harga</option>
        <option value="100000" @selected($selectedPrice === 100000)>Sampai Rp100.000</option>
        <option value="150000" @selected($selectedPrice === 150000)>Sampai Rp150.000</option>
        <option value="200000" @selected($selectedPrice === 200000)>Sampai Rp200.000</option>
        <option value="300000" @selected($selectedPrice === 300000)>Sampai Rp300.000</option>
    </select>
</div>

<div>
    <label for="filter-sort" class="text-xs font-extrabold uppercase tracking-wider text-stone-400">Urutkan</label>
    <select id="filter-sort" name="sort"
            class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        <option value="latest" @selected($currentSort === 'latest')>Terbaru</option>
        <option value="popular" @selected($currentSort === 'popular')>Paling populer</option>
        <option value="price_asc" @selected($currentSort === 'price_asc')>Harga terendah</option>
        <option value="price_desc" @selected($currentSort === 'price_desc')>Harga tertinggi</option>
    </select>
</div>

<button type="submit" class="w-full rounded-xl bg-stone-900 px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-stone-700 lg:inline-flex lg:items-center lg:justify-center">
    Terapkan filter
</button>
