@extends('layouts.admin')

@section('title', 'Master Rating')
@section('heading', 'Master rating')
@section('subheading', 'Kelola ulasan pelanggan dan ulasan Google Maps yang tampil di toko.')

@section('content')
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Total rating</p>
            <p class="mt-1 font-display text-2xl font-extrabold text-stone-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Rata-rata</p>
            <p class="mt-1 flex items-center gap-2 font-display text-2xl font-extrabold text-stone-900">
                {{ number_format((float) ($stats['average'] ?? 0), 1, ',', '.') }}
                <x-star-display :rating="$stats['average'] ?? 0" size="h-4 w-4" />
            </p>
        </div>
        <div class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Tampil di toko</p>
            <p class="mt-1 font-display text-2xl font-extrabold text-emerald-600">{{ $stats['visible'] }}</p>
        </div>
        <div class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Dari pelanggan</p>
            <p class="mt-1 font-display text-2xl font-extrabold text-amber-600">{{ $stats['customer'] }}</p>
        </div>
    </div>

    <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama, kota, atau ulasan"
                   class="w-56 rounded-xl border-stone-200 py-2 text-xs focus:border-amber-300 focus:ring-amber-200">
            <select name="source" class="rounded-xl border-stone-200 py-2 text-xs focus:border-amber-300 focus:ring-amber-200">
                <option value="all" @selected($source === 'all')>Semua sumber</option>
                @foreach (\App\Models\Review::SOURCE_LABELS as $value => $label)
                    <option value="{{ $value }}" @selected($source === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="visibility" class="rounded-xl border-stone-200 py-2 text-xs focus:border-amber-300 focus:ring-amber-200">
                <option value="all" @selected($visibility === 'all')>Semua status</option>
                <option value="visible" @selected($visibility === 'visible')>Tampil</option>
                <option value="hidden" @selected($visibility === 'hidden')>Disembunyikan</option>
            </select>
            <button type="submit" class="rounded-xl border border-stone-200 bg-white px-3 py-2 text-xs font-extrabold text-stone-600 transition hover:bg-stone-50">Filter</button>
            @if ($search !== '' || $source !== 'all' || $visibility !== 'all')
                <a href="{{ route('admin.reviews.index') }}" class="text-xs font-bold text-stone-400 hover:text-stone-600">Reset</a>
            @endif
        </form>
        <a href="{{ route('admin.reviews.create') }}" class="rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-extrabold text-white hover:bg-amber-600">Tambah rating</a>
    </div>

    <div class="mt-5 overflow-x-auto rounded-3xl border border-stone-200 bg-white shadow-sm">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="bg-stone-50 text-[10px] font-extrabold uppercase tracking-wider text-stone-500">
                <tr>
                    <th class="px-5 py-4">Pemberi ulasan</th>
                    <th class="px-5 py-4">Rating</th>
                    <th class="px-5 py-4">Produk</th>
                    <th class="px-5 py-4">Ulasan</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($reviews as $review)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-extrabold text-stone-800">{{ $review->reviewer_name }}</p>
                            <p class="text-xs text-stone-400">{{ $review->reviewer_city ?: 'Tanpa kota' }}</p>
                            <p class="mt-0.5 text-[11px] font-semibold text-stone-400">{{ $review->reviewed_at?->format('d/m/Y') ?? '—' }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-1.5">
                                <x-star-display :rating="$review->rating" />
                                <span class="text-xs font-extrabold text-stone-700">{{ $review->rating }}/5</span>
                            </div>
                            <span class="mt-1 inline-flex rounded-md bg-stone-100 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-stone-500">{{ $review->sourceLabel() }}</span>
                        </td>
                        <td class="px-5 py-4 text-xs font-semibold text-stone-600">
                            {{ $review->product?->name ?? 'Ulasan toko' }}
                        </td>
                        <td class="px-5 py-4 text-xs leading-6 text-stone-600">
                            {{ \Illuminate\Support\Str::limit($review->comment ?: 'Tanpa teks ulasan.', 90) }}
                            @if ($review->admin_reply)
                                <p class="mt-1 text-[11px] font-bold text-sky-700">Sudah dibalas toko</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if ($review->is_visible)
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-extrabold text-emerald-700">Tampil</span>
                            @else
                                <span class="inline-flex rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-extrabold text-stone-500">Disembunyikan</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <form action="{{ route('admin.reviews.visibility', $review) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-lg border border-stone-200 px-3 py-1.5 text-xs font-bold text-stone-700">
                                        {{ $review->is_visible ? 'Sembunyikan' : 'Tampilkan' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.reviews.edit', $review) }}" class="rounded-lg border border-stone-200 px-3 py-1.5 text-xs font-bold text-stone-700">Edit</a>
                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Hapus rating ini? Tindakan ini tidak dapat dibatalkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-bold text-red-700">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-stone-500">Belum ada rating yang cocok dengan filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $reviews->links('vendor.pagination.mamikhevan') }}</div>
@endsection
