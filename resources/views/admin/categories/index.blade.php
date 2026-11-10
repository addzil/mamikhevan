@extends('layouts.admin')

@section('title', 'Kategori Produk')
@section('heading', 'Kategori')
@section('subheading', 'Kelola kelompok produk yang tampil di katalog toko.')

@section('content')
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <p class="text-xs font-semibold text-stone-400">Total {{ $categories->total() }} kategori terdaftar.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-amber-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Tambah kategori
        </a>
    </div>

    <div class="mt-5 overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead>
                    <tr class="border-b border-stone-100 bg-stone-50/60 text-[10px] font-extrabold uppercase tracking-wider text-stone-400">
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5">Slug</th>
                        <th class="px-5 py-3.5">Jumlah produk</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($categories as $category)
                        <tr class="transition hover:bg-stone-50/70">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-11 w-11 flex-none place-items-center rounded-2xl bg-gradient-to-br from-yellow-50 to-amber-50 text-xl">
                                        {{ $category->icon ?: '🧺' }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-extrabold text-stone-800">{{ $category->name }}</p>
                                        <p class="max-w-xs truncate text-xs font-semibold text-stone-400">{{ $category->description ?: 'Tanpa deskripsi' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <code class="rounded-lg bg-stone-100 px-2 py-1 text-[11px] font-bold text-stone-500">{{ $category->slug }}</code>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" class="text-xs font-extrabold text-stone-600 hover:text-yellow-700">
                                    {{ $category->products_count }} produk
                                </a>
                            </td>
                            <td class="px-5 py-4">
                                @if ($category->is_active)
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
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-stone-200 px-2.5 py-1.5 text-[11px] font-extrabold text-stone-600 transition hover:border-amber-200 hover:text-yellow-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" /></svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Hapus kategori ini? Pastikan tidak ada produk di dalamnya.')">
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
                            <td colspan="5" class="px-5 py-16 text-center">
                                <p class="font-display text-lg font-bold text-stone-700">Belum ada kategori</p>
                                <p class="mt-1 text-xs font-semibold text-stone-400">Tambahkan kategori pertama untuk mulai mengelompokkan produk.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $categories->links('vendor.pagination.mamikhevan') }}
    </div>
@endsection
