@extends('layouts.print')

@section('title', 'Resi '.$order->code)
@section('back-url', route('admin.orders.show', $order))

@php
    $weightLabel = $weightGrams >= 1000
        ? number_format($weightGrams / 1000, 2, ',', '.').' kg'
        : number_format($weightGrams, 0, ',', '.').' g';
    $itemCount = $order->items->sum('quantity');
    $isCod = $order->payment_method === \App\Models\Order::PAYMENT_COD;
    $printedAt = now('Asia/Jakarta');
@endphp

@push('styles')
    <style>
        @page {
            size: 100mm 150mm;
            margin: 2mm;
        }

        .label-header,
        .label-codes,
        .label-footer {
            padding: 2mm 3mm !important;
        }

        .label-courier > div,
        .label-addresses > div,
        .label-items {
            padding: 2mm 3mm !important;
        }

        .label-summary > div {
            padding: 1.5mm 1mm !important;
        }

        .label-footer .signature-line {
            margin-top: 3mm !important;
        }

        @media print {
            html,
            body {
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            main {
                padding: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .print-sheet {
                max-width: none !important;
                margin: 0 !important;
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                break-inside: avoid;
            }

            .avoid-break {
                break-inside: avoid;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        function setLabelPaper(size) {
            const [width, height] = size.split('x').map(Number);
            const sheet = document.querySelector('.print-sheet');
            const contentWidth = `${width - 4}mm`;

            document.getElementById('label-page-size').textContent = `@page { size: ${width}mm ${height}mm; margin: 2mm; }`;
            sheet.style.width = contentWidth;
            sheet.style.maxWidth = contentWidth;
            sheet.style.zoom = '1';

            const ruler = document.createElement('div');
            ruler.style.cssText = 'position:absolute;visibility:hidden;height:100mm;';
            document.body.appendChild(ruler);

            const pixelsPerMm = ruler.getBoundingClientRect().height / 100;
            const availableHeight = (height - 8) * pixelsPerMm;
            const sheetHeight = sheet.getBoundingClientRect().height;

            ruler.remove();
            sheet.style.zoom = String(Math.min(1, availableHeight / sheetHeight));
        }

        window.addEventListener('beforeprint', () => {
            setLabelPaper(document.getElementById('label-paper').value);
        });
    </script>
@endpush

@section('content')
    <style id="label-page-size"></style>
    <div class="no-print mb-3 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-stone-200 bg-white p-3 text-xs">
        <label for="label-paper" class="font-bold text-stone-700">Ukuran kertas</label>
        <select id="label-paper" onchange="setLabelPaper(this.value)" class="rounded-lg border-stone-200 py-1.5 text-xs">
            <option value="100x150">Label 100 × 150 mm (4 × 6 inci) · XP-420B</option>
            <option value="100x100">Label 100 × 100 mm</option>
            <option value="100x75">Label 100 × 75 mm</option>
            <option value="80x100">Label 80 × 100 mm</option>
            <option value="80x150">Label 80 × 150 mm</option>
            <option value="58x100">Label 58 × 100 mm</option>
        </select>
    </div>
    <div class="print-sheet mx-auto w-full max-w-[96mm] overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        <header class="label-header flex items-start justify-between gap-4 border-b-2 border-stone-900 px-6 py-5">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-[0.25em] text-stone-400">Label pengiriman</p>
                <h1 class="mt-1 font-display text-2xl font-extrabold leading-none text-stone-900">{{ config('shop.store.name') }}</h1>
                <p class="mt-2 max-w-[72mm] text-[10px] font-semibold leading-4 text-stone-500">{{ config('shop.store.address') }}</p>
                <p class="mt-1 text-[10px] font-bold text-stone-500">{{ config('shop.store.phone') }} &middot; {{ config('shop.store.email') }}</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Tanggal cetak</p>
                <p class="mt-1 text-xs font-extrabold text-stone-800">{{ $printedAt->format('d M Y') }}</p>
                <p class="text-[10px] font-semibold text-stone-400">{{ $printedAt->format('H:i') }} WIB</p>
            </div>
        </header>

        <div class="label-courier grid grid-cols-[1fr_auto] items-stretch border-b border-stone-200">
            <div class="px-6 py-4">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Ekspedisi</p>
                <p class="mt-1 text-lg font-extrabold text-stone-900">{{ $order->shippingLabel() }}</p>
                <p class="mt-1 max-w-[78mm] text-[11px] font-semibold leading-5 text-stone-500">{{ $order->courierHandlingNote() }}</p>
            </div>
            <div class="flex w-36 flex-col items-center justify-center {{ $order->courierBadgeClasses() }} px-4 py-4 text-center">
                <p class="text-[10px] font-extrabold uppercase tracking-widest opacity-80">Kurir</p>
                <p class="mt-1 font-display text-2xl font-extrabold leading-none">{{ $order->courierLabel() }}</p>
                <p class="mt-1 text-[10px] font-extrabold uppercase tracking-wider opacity-90">{{ $order->shippingServiceLabel() }}</p>
            </div>
        </div>

        <div class="label-codes flex items-center justify-between gap-4 border-b border-stone-200 bg-stone-50 px-6 py-4">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Nomor resi</p>
                <p class="mt-1 font-mono text-xl font-extrabold tracking-wider text-stone-900">{{ $order->shipping_tracking_number ?: '________________' }}</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Kode pesanan</p>
                <p class="mt-1 text-xs font-extrabold text-stone-700">{{ $order->code }}</p>
            </div>
        </div>

        <div class="label-addresses grid grid-cols-2 border-b border-stone-200">
            <div class="border-r border-stone-200 px-6 py-4">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Pengirim</p>
                <p class="mt-2 text-sm font-extrabold text-stone-900">{{ config('shop.store.name') }}</p>
                <p class="mt-1 text-[11px] font-semibold leading-5 text-stone-600">{{ config('shop.store.address') }}</p>
                <p class="mt-1 text-[11px] font-bold text-stone-600">{{ config('shop.store.phone') }}</p>
            </div>
            <div class="px-6 py-4">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-amber-600">Penerima</p>
                <p class="mt-2 text-base font-extrabold leading-tight text-stone-900">{{ $order->customer_name }}</p>
                <p class="mt-1 text-[11px] font-bold text-stone-600">{{ $order->customer_phone }}</p>
                <p class="mt-2 text-[11px] font-semibold leading-5 text-stone-700">{!! nl2br(e($order->customer_address)) !!}</p>
                @if ($order->shipping_postal_code)
                    <p class="mt-1 text-[11px] font-semibold leading-5 text-stone-600">
                        {{ $order->shipping_subdistrict }}, {{ $order->shipping_district }}, {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}
                    </p>
                @endif
                @if ($order->note)
                    <p class="mt-2 rounded-lg bg-amber-50 px-2 py-1 text-[10px] font-bold leading-4 text-amber-700">Catatan: {{ $order->note }}</p>
                @endif
            </div>
        </div>

        <div class="label-summary grid grid-cols-4 divide-x divide-stone-200 border-b border-stone-200 text-center">
            <div class="px-2 py-3">
                <p class="text-[9px] font-extrabold uppercase tracking-wider text-stone-400">Pembayaran</p>
                <p class="mt-1 text-[11px] font-extrabold text-stone-800">{{ $isCod ? 'COD' : 'Transfer' }}</p>
            </div>
            <div class="px-2 py-3">
                <p class="text-[9px] font-extrabold uppercase tracking-wider text-stone-400">{{ $isCod ? 'Tagih tunai' : 'Total' }}</p>
                <p class="mt-1 text-[11px] font-extrabold text-stone-800">{{ $order->total_formatted }}</p>
            </div>
            <div class="px-2 py-3">
                <p class="text-[9px] font-extrabold uppercase tracking-wider text-stone-400">Berat</p>
                <p class="mt-1 text-[11px] font-extrabold text-stone-800">{{ $weightLabel }}</p>
            </div>
            <div class="px-2 py-3">
                <p class="text-[9px] font-extrabold uppercase tracking-wider text-stone-400">Item</p>
                <p class="mt-1 text-[11px] font-extrabold text-stone-800">{{ $itemCount }} pcs</p>
            </div>
        </div>

        <div class="label-items avoid-break px-6 py-4">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Isi paket</p>
            <ul class="mt-2 divide-y divide-stone-100">
                @foreach ($order->items as $item)
                    <li class="flex items-start justify-between gap-3 py-1.5 text-[11px]">
                        <span class="font-semibold text-stone-700">
                            {{ $item->product_name }}
                            @if ($item->size)
                                <span class="text-stone-400">&middot; {{ $item->size }}</span>
                            @endif
                            @if ($item->is_promo_gift)
                                <span class="font-bold text-emerald-600">(hadiah)</span>
                            @endif
                        </span>
                        <span class="flex-none font-extrabold text-stone-800">{{ $item->quantity }}x</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <footer class="label-footer grid grid-cols-[1fr_auto] items-end gap-6 border-t border-stone-200 bg-stone-50 px-6 py-4">
            <div>
                <p class="text-[9px] font-extrabold uppercase tracking-wider text-stone-400">Catatan pengiriman</p>
                <p class="mt-1 text-[10px] font-semibold leading-4 text-stone-500">
                    {{ $isCod ? 'Pastikan kurir menagih pembayaran sebelum paket diserahkan.' : 'Pembayaran sudah dilakukan. Paket siap dikirim.' }}
                </p>
            </div>
            <div class="flex items-end gap-6">
                <div class="text-center">
                    <p class="text-[9px] font-bold text-stone-400">Disiapkan oleh</p>
                    <div class="signature-line mt-6 w-20 border-t border-stone-400"></div>
                </div>
                <div class="text-center">
                    <p class="text-[9px] font-bold text-stone-400">Kurir</p>
                    <div class="signature-line mt-6 w-20 border-t border-stone-400"></div>
                </div>
            </div>
        </footer>
    </div>
@endsection
