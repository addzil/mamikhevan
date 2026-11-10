@extends('layouts.admin')

@section('title', 'Panduan Ukuran')
@section('heading', 'Panduan Ukuran')
@section('subheading', 'Master tipe dan entri ukuran pakaian anak.')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm font-semibold text-stone-500">{{ $sizeGuides->count() }} tipe panduan ukuran terdaftar.</p>
        <a href="{{ route('admin.size-guides.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-amber-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Tambah Tipe Baru
        </a>
    </div>

    @if ($sizeGuides->isEmpty())
        <div class="rounded-3xl border border-dashed border-stone-300 bg-white p-12 text-center">
            <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-2xl bg-stone-100 text-3xl">📏</div>
            <p class="text-sm font-extrabold text-stone-700">Belum ada panduan ukuran</p>
            <p class="mt-1 text-xs font-semibold text-stone-400">Tambahkan tipe panduan ukuran pertama untuk produk Anda.</p>
            <a href="{{ route('admin.size-guides.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-amber-600">
                Tambah Sekarang
            </a>
        </div>
    @else
        <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="border-b border-stone-100 bg-stone-50">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-xs font-extrabold uppercase tracking-wider text-stone-400">Tipe</th>
                        <th class="px-5 py-3.5 text-left text-xs font-extrabold uppercase tracking-wider text-stone-400">Jumlah Ukuran</th>
                        <th class="px-5 py-3.5 text-left text-xs font-extrabold uppercase tracking-wider text-stone-400">Measurement Fields</th>
                        <th class="px-5 py-3.5 text-right text-xs font-extrabold uppercase tracking-wider text-stone-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @foreach ($sizeGuides as $guide)
                        <tr class="transition hover:bg-stone-50/60">
                            <td class="px-5 py-4">
                                <p class="font-extrabold text-stone-800">{{ $guide->name }}</p>
                                @if ($guide->description)
                                    <p class="mt-0.5 text-xs font-semibold text-stone-400">{{ $guide->description }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-xl bg-amber-50 px-3 py-1 text-xs font-extrabold text-yellow-700">
                                    {{ $guide->entries_count }} ukuran
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($guide->measurement_fields ?? [] as $field)
                                        <span class="inline-flex items-center rounded-lg bg-yellow-50 px-2.5 py-1 text-[11px] font-bold text-yellow-600">
                                            {{ \App\Models\SizeGuideEntry::MEASUREMENT_LABELS[$field] ?? $field }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-2" x-data="{}">
                                    <a href="{{ route('admin.size-guides.edit', $guide) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-stone-200 px-3 py-2 text-xs font-extrabold text-stone-600 transition hover:border-amber-200 hover:text-yellow-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.size-guides.destroy', $guide) }}" method="POST"
                                          @submit.prevent="if(confirm('Hapus panduan ukuran \'{{ addslashes($guide->name) }}\'? Semua entri ukuran akan ikut terhapus.')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-xl border border-amber-100 px-3 py-2 text-xs font-extrabold text-yellow-700 transition hover:bg-amber-50">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
