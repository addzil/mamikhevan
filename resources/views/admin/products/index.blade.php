@extends('layouts.admin')

@section('title', 'Produk')
@section('heading', 'Produk')
@section('subheading', 'Kelola katalog pakaian anak yang tampil di toko.')

@section('content')
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <p class="text-xs font-semibold text-stone-400">Total {{ $products->total() }} produk ditemukan.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-amber-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Tambah produk
        </a>
    </div>

    <form action="{{ route('admin.products.index') }}" method="GET" class="mt-5 grid gap-3 rounded-3xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_200px_170px_auto]">
        <div class="relative">
            <label for="q" class="sr-only">Cari produk</label>
            <input id="q" type="search" name="q" value="{{ $search }}" placeholder="Cari nama produk..."
                   class="w-full rounded-xl border-stone-200 py-2.5 pl-9 pr-3 text-sm focus:border-amber-300 focus:ring-amber-200">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
        </div>

        <label for="category" class="sr-only">Kategori</label>
        <select id="category" name="category" class="rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>

        <label for="status" class="sr-only">Status</label>
        <select id="status" name="status" class="rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
            <option value="">Semua status</option>
            <option value="active" @selected($status === 'active')>Aktif</option>
            <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
            <option value="low" @selected($status === 'low')>Stok menipis</option>
        </select>

        <div class="flex items-center gap-2">
            <button type="submit" class="flex-1 rounded-xl bg-stone-900 px-4 py-2.5 text-xs font-extrabold text-white transition hover:bg-stone-700">Filter</button>
            @if ($search !== '' || $categoryId || $status !== '')
                <a href="{{ route('admin.products.index') }}" class="rounded-xl border border-stone-200 px-3 py-2.5 text-xs font-extrabold text-stone-500 transition hover:bg-stone-50">Reset</a>
            @endif
        </div>
    </form>

    <div class="mt-5 overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[860px] text-left text-sm">
                <thead>
                    <tr class="border-b border-stone-100 bg-stone-50/60 text-[10px] font-extrabold uppercase tracking-wider text-stone-400">
                        <th class="px-5 py-3.5">Produk</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5">Harga</th>
                        <th class="px-5 py-3.5">Stok</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($products as $product)
                        <tr class="transition hover:bg-stone-50/70">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-12 w-12 flex-none overflow-hidden rounded-xl bg-yellow-50">
                                        <x-store.product-image :product="$product" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="flex items-center gap-1.5 text-sm font-extrabold text-stone-800">
                                            <span class="truncate">{{ $product->name }}</span>
                                            @if ($product->is_featured)
                                                <svg class="h-3.5 w-3.5 flex-none text-amber-400" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.539 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.98 10.1c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69l1.519-4.674Z"/></svg>
                                            @endif
                                        </p>
                                        <p class="text-[11px] font-semibold text-stone-400">{{ $product->age_group ?: 'Semua usia' }} &middot; {{ $product->genderLabel() }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-xs font-bold text-stone-500">{{ $product->category?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="text-xs font-extrabold text-stone-800">{{ $product->price_formatted }}</p>
                                @if ($product->hasDiscount())
                                    <p class="text-[11px] font-semibold text-stone-400 line-through">{{ $product->compare_price_formatted }}</p>
                                @endif
                                @if ($product->cost_price !== null)
                                    <p class="mt-0.5 text-[11px] font-semibold text-stone-400">Modal {{ $product->cost_price_formatted }} &middot; margin {{ $product->margin_percent }}%</p>
                                @else
                                    <p class="mt-0.5 text-[11px] font-semibold text-stone-400">Modal belum diisi</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="rounded-lg px-2 py-1 text-[11px] font-extrabold {{ $product->isInStock() ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-yellow-700' }}">
                                    {{ $product->stock }} total
                                </span>
                                @if ($product->sizeList() !== [])
                                    <p class="mt-1 text-[10px] font-semibold text-stone-400">{{ collect($product->sizeStockMap())->map(fn ($quantity, $size) => $size.': '.$quantity)->implode(' · ') }}</p>
                                    @if ($product->unallocated_stock > 0)
                                        <p class="mt-1 text-[10px] font-bold text-amber-700">{{ $product->unallocated_stock }} belum dialokasikan</p>
                                    @endif
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($product->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-extrabold text-emerald-600 ring-1 ring-emerald-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-extrabold text-stone-500 ring-1 ring-stone-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-stone-400"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('products.show', $product) }}" target="_blank" rel="noopener"
                                       class="grid h-8 w-8 place-items-center rounded-lg border border-stone-200 text-stone-500 transition hover:border-amber-200 hover:text-yellow-700" aria-label="Lihat di toko">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-stone-200 px-2.5 py-1.5 text-[11px] font-extrabold text-stone-600 transition hover:border-amber-200 hover:text-yellow-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" /></svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Hapus produk ini? Tindakan ini tidak bisa dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-stone-200 px-2.5 py-1.5 text-[11px] font-extrabold text-stone-600 transition hover:border-amber-200 hover:bg-amber-50 hover:text-yellow-700">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <p class="font-display text-lg font-bold text-stone-700">Produk tidak ditemukan</p>
                                <p class="mt-1 text-xs font-semibold text-stone-400">Coba ubah filter atau tambahkan produk baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $products->links('vendor.pagination.mamikhevan') }}
    </div>
@endsection
