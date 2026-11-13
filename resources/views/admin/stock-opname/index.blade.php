@extends('layouts.admin')

@section('title', 'Opname Stok')
@section('heading', 'Opname Stok')
@section('subheading', 'Cocokkan stok fisik dengan stok sistem dan lihat unit terjual per produk.')

@section('content')
    @if ($errors->any())
        <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4 text-sm font-bold text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl border border-stone-200 bg-white p-5"><p class="text-xs font-extrabold uppercase tracking-wide text-stone-400">Produk terdaftar</p><p class="mt-2 font-display text-2xl font-extrabold text-stone-900">{{ number_format($stats['products']) }}</p></article>
        <article class="rounded-3xl border border-sky-100 bg-sky-50 p-5"><p class="text-xs font-extrabold uppercase tracking-wide text-sky-700">Stok sistem</p><p class="mt-2 font-display text-2xl font-extrabold text-stone-900">{{ number_format($stats['stock_units']) }} unit</p></article>
        <article class="rounded-3xl border border-amber-100 bg-amber-50 p-5"><p class="text-xs font-extrabold uppercase tracking-wide text-amber-700">Ukuran menipis</p><p class="mt-2 font-display text-2xl font-extrabold text-stone-900">{{ number_format($stats['low_stock']) }} ukuran</p><p class="mt-1 text-xs font-semibold text-stone-500">Maksimal 5 unit per ukuran</p></article>
        <article class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5"><p class="text-xs font-extrabold uppercase tracking-wide text-emerald-700">Unit dalam pesanan</p><p class="mt-2 font-display text-2xl font-extrabold text-stone-900">{{ number_format($stats['ordered_units']) }} unit</p><p class="mt-1 text-xs font-semibold text-stone-500">Termasuk menunggu pembayaran, tidak termasuk pesanan batal</p></article>
    </div>

    <form action="{{ route('admin.stock-opname.index') }}" method="GET" class="mb-5 grid gap-3 rounded-3xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_180px_180px_auto]">
        <div><label for="q" class="mb-1 block text-xs font-extrabold text-stone-500">Cari produk</label><input id="q" type="search" name="q" value="{{ $search }}" placeholder="Nama produk..." class="w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200"></div>
        <div><label for="from" class="mb-1 block text-xs font-extrabold text-stone-500">Penjualan dari</label><input id="from" type="date" name="from" value="{{ $from }}" class="w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200"></div>
        <div><label for="to" class="mb-1 block text-xs font-extrabold text-stone-500">Sampai</label><input id="to" type="date" name="to" value="{{ $to }}" class="w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200"></div>
        <div class="flex items-end"><button type="submit" class="w-full rounded-xl bg-stone-900 px-4 py-2.5 text-xs font-extrabold text-white hover:bg-stone-700">Tampilkan</button></div>
    </form>

    <section class="mb-7 overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
        <div class="border-b border-stone-100 px-5 py-4"><h2 class="font-display text-lg font-extrabold text-stone-900">Stok produk</h2><p class="text-xs font-semibold text-stone-400">Masukkan hasil hitung fisik untuk menyimpan jumlah baru.</p></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] text-left text-sm">
                <thead><tr class="border-b border-stone-100 bg-stone-50 text-[10px] font-extrabold uppercase tracking-wider text-stone-400"><th class="px-5 py-3">Produk</th><th class="px-5 py-3 text-right">Stok sistem</th><th class="px-5 py-3 text-right">Unit pesanan</th><th class="px-5 py-3 text-right">Nilai item pesanan*</th><th class="px-5 py-3">Stok fisik</th><th class="px-5 py-3">Catatan</th><th class="px-5 py-3"></th></tr></thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($products as $product)
                        <tr>
                            <td class="px-5 py-4"><p class="text-xs font-extrabold text-stone-800">{{ $product->name }}</p><p class="mt-1 text-[11px] font-semibold text-stone-400">{{ $product->category?->name ?? 'Tanpa kategori' }}</p>@if ($product->sizeList() !== [])<p class="mt-1 text-[10px] font-semibold text-stone-400">{{ collect($product->sizeStockMap())->map(fn ($quantity, $size) => $size.': '.$quantity)->implode(' · ') }}</p>@endif</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-extrabold {{ $product->stock <= 5 ? 'text-amber-700' : 'text-stone-700' }}">{{ number_format($product->stock) }} unit</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right text-xs font-bold text-stone-600">{{ number_format((int) $product->ordered_quantity) }} unit</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right text-xs font-bold text-stone-600">Rp {{ number_format((int) $product->ordered_value, 0, ',', '.') }}</td>
                            <td colspan="3" class="px-5 py-3">
                                <form action="{{ route('admin.stock-opname.update', $product) }}" method="POST" class="grid grid-cols-[120px_minmax(180px,1fr)_auto] items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="q" value="{{ $search }}"><input type="hidden" name="from" value="{{ $from }}"><input type="hidden" name="to" value="{{ $to }}">
                                    @if ($product->sizeList() !== [])
                                        <div class="col-span-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                            @foreach ($product->sizeList() as $index => $size)
                                                <label class="rounded-xl border border-stone-200 p-3 text-xs font-bold text-stone-600">
                                                    Ukuran {{ $size }}
                                                    <input type="hidden" name="sizes[{{ $index }}]" value="{{ $size }}">
                                                    <input name="size_stocks[{{ $index }}]" type="number" min="0" max="1000000" required value="{{ old('product_id') == $product->id ? old('size_stocks.'.$index, $product->stockForSize($size)) : $product->stockForSize($size) }}" class="mt-2 w-full rounded-lg border-stone-200 px-2 py-2 text-xs focus:border-amber-300 focus:ring-amber-200">
                                                </label>
                                            @endforeach
                                        </div>
                                        @if ($product->unallocated_stock > 0)
                                            <p class="col-span-3 rounded-lg bg-amber-50 p-2 text-xs font-bold text-amber-800">{{ $product->unallocated_stock }} unit stok lama belum dialokasikan. Isi hitung fisik semua ukuran untuk menyelesaikan alokasi.</p>
                                        @endif
                                    @else
                                        <label class="sr-only" for="stock-{{ $product->id }}">Stok fisik {{ $product->name }}</label>
                                        <input id="stock-{{ $product->id }}" name="counted_stock" type="number" min="0" max="1000000" required placeholder="{{ $product->stock }}" value="{{ old('product_id') == $product->id ? old('counted_stock') : '' }}" class="w-full rounded-lg border-stone-200 px-2 py-2 text-xs focus:border-amber-300 focus:ring-amber-200">
                                    @endif
                                    <label class="sr-only" for="note-{{ $product->id }}">Catatan opname {{ $product->name }}</label>
                                    <input id="note-{{ $product->id }}" name="note" type="text" maxlength="500" placeholder="Catatan (opsional)" class="w-full rounded-lg border-stone-200 px-2 py-2 text-xs focus:border-amber-300 focus:ring-amber-200">
                                    <button type="submit" class="rounded-lg bg-amber-500 px-3 py-2 text-[11px] font-extrabold text-white hover:bg-amber-600">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-12 text-center text-sm font-semibold text-stone-400">Produk tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-5">{{ $products->links('vendor.pagination.mamikhevan') }}</div>
        <p class="px-5 pb-5 text-[11px] font-semibold text-stone-400">*Jumlah dan nilai item berasal dari pesanan yang belum dibatalkan, termasuk yang masih menunggu pembayaran. Nilai item menggunakan subtotal sebelum diskon promosi.</p>
    </section>

    <section class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
        <div class="border-b border-stone-100 px-5 py-4"><h2 class="font-display text-lg font-extrabold text-stone-900">Riwayat opname terbaru</h2><p class="text-xs font-semibold text-stone-400">Perubahan stok dicatat beserta stok awal, hasil hitung, selisih, dan akun yang menyimpan.</p></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[700px] text-left text-sm">
            <thead><tr class="border-b border-stone-100 bg-stone-50 text-[10px] font-extrabold uppercase tracking-wider text-stone-400"><th class="px-5 py-3">Tanggal</th><th class="px-5 py-3">Produk</th><th class="px-5 py-3 text-right">Sistem</th><th class="px-5 py-3 text-right">Fisik</th><th class="px-5 py-3 text-right">Selisih</th><th class="px-5 py-3">Petugas / Catatan</th></tr></thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($adjustments as $adjustment)
                    <tr><td class="whitespace-nowrap px-5 py-4 text-xs font-semibold text-stone-500">{{ $adjustment->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td><td class="px-5 py-4 text-xs font-extrabold text-stone-700">{{ $adjustment->product_name }}{{ $adjustment->size ? ' · Ukuran '.$adjustment->size : '' }}</td><td class="px-5 py-4 text-right text-xs font-semibold text-stone-500">{{ number_format($adjustment->system_stock) }}</td><td class="px-5 py-4 text-right text-xs font-semibold text-stone-500">{{ number_format($adjustment->counted_stock) }}</td><td class="px-5 py-4 text-right text-xs font-extrabold {{ $adjustment->difference > 0 ? 'text-emerald-700' : 'text-red-700' }}">{{ $adjustment->difference > 0 ? '+' : '' }}{{ number_format($adjustment->difference) }}</td><td class="px-5 py-4"><p class="text-xs font-bold text-stone-700">{{ $adjustment->user?->name ?? 'Akun dihapus' }}</p><p class="text-[11px] text-stone-400">{{ $adjustment->note ?: 'Tanpa catatan' }}</p></td></tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-sm font-semibold text-stone-400">Belum ada penyesuaian stok.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </section>
@endsection
