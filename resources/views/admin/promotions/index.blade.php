@extends('layouts.admin')

@section('title', 'Master Promo')
@section('heading', 'Master promo')
@section('subheading', 'Atur sale dan manfaat yang dipilih otomatis saat checkout.')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-xs font-semibold text-stone-500">{{ $promotions->total() }} promo terdaftar. Satu promo dengan nilai manfaat terbesar dipakai per pesanan.</p>
        <a href="{{ route('admin.promotions.create') }}" class="rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-extrabold text-white hover:bg-amber-600">Tambah promo</a>
    </div>

    <div class="mt-5 overflow-x-auto rounded-3xl border border-stone-200 bg-white shadow-sm">
        <table class="w-full min-w-[850px] text-left text-sm">
            <thead class="bg-stone-50 text-[10px] font-extrabold uppercase tracking-wider text-stone-500">
                <tr>
                    <th class="px-5 py-4">Promo / event</th>
                    <th class="px-5 py-4">Manfaat</th>
                    <th class="px-5 py-4">Sasaran</th>
                    <th class="px-5 py-4">Jadwal WIB</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($promotions as $promotion)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-extrabold text-stone-800">{{ $promotion->name }}</p>
                            @if ($promotion->event_name)<p class="text-xs text-amber-700">{{ $promotion->event_name }}</p>@endif
                            @if ($promotion->voucher_code)
                                <p class="mt-1 inline-flex rounded-md bg-stone-100 px-2 py-0.5 font-mono text-[11px] font-bold text-stone-700">{{ $promotion->voucher_code }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-xs font-semibold text-stone-600">
                            @if ($promotion->type === 'buy_get')
                                Beli {{ $promotion->buy_quantity }}, gratis {{ $promotion->gift_quantity }} {{ $promotion->giftProduct?->name ?? 'produk terhapus' }}
                            @elseif ($promotion->type === 'giveaway')
                                Gratis {{ $promotion->gift_quantity }} &times; {{ $promotion->giftProduct?->name ?? 'produk terhapus' }} (kode {{ $promotion->voucher_code }})
                            @elseif ($promotion->type === 'shipping_subsidy')
                                Subsidi ongkir Rp{{ number_format($promotion->discount_value, 0, ',', '.') }}
                            @elseif ($promotion->type === 'quantity_tier')
                                Bertingkat:
                                @foreach (collect($promotion->tiers ?? [])->sortBy('min_quantity') as $tier)
                                    <span class="block">{{ $tier['min_quantity'] }}+ item &rarr; {{ ($tier['discount_kind'] ?? 'percent') === 'percent' ? ($tier['discount_value'] ?? 0).'%' : 'Rp'.number_format((int) ($tier['discount_value'] ?? 0), 0, ',', '.') }}</span>
                                @endforeach
                            @else
                                {{ $promotion->type === 'price' ? 'Diskon harga' : 'Diskon ongkir' }}
                                {{ $promotion->discount_kind === 'percent' ? $promotion->discount_value.'%' : 'Rp'.number_format($promotion->discount_value, 0, ',', '.') }}
                            @endif
                        </td>
                        <td class="px-5 py-4 text-xs text-stone-600">
                            {{ match ($promotion->target_type) { 'product' => $promotion->product?->name ?? 'Produk terhapus', 'category' => $promotion->category?->name ?? 'Kategori terhapus', default => 'Semua produk' } }}
                        </td>
                        <td class="px-5 py-4 text-xs text-stone-600">
                            {{ $promotion->starts_at->timezone(\App\Models\Promotion::TIMEZONE)->format('d/m/Y H:i') }}<br>
                            s.d. {{ $promotion->ends_at->timezone(\App\Models\Promotion::TIMEZONE)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-4 text-xs font-bold">
                            @if (! $promotion->is_active)
                                <span class="text-stone-400">Nonaktif</span>
                            @elseif ($promotion->starts_at->isFuture())
                                <span class="text-sky-600">Terjadwal</span>
                            @elseif ($promotion->ends_at->isPast())
                                <span class="text-stone-400">Selesai</span>
                            @else
                                <span class="text-emerald-600">Aktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.promotions.edit', $promotion) }}" class="rounded-lg border border-stone-200 px-3 py-1.5 text-xs font-bold text-stone-700">Edit</a>
                                <form action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" onsubmit="return confirm('Hapus promo ini? Pesanan lama tetap menyimpan riwayat promonya.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-bold text-red-700">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-stone-500">Belum ada promo. Tambahkan promo pertama untuk sale toko.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $promotions->links('vendor.pagination.mamikhevan') }}</div>
@endsection
