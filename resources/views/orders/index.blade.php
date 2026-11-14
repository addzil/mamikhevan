@extends('layouts.storefront')

@section('title', 'Pesanan Saya — Mamikhevanshop')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl font-extrabold text-stone-900">Pesanan saya</h1>
                <p class="mt-2 text-sm text-stone-500">Pesanan yang dibuat saat masuk ke akun ini.</p>
            </div>
            <a href="{{ route('profile.edit') }}" class="rounded-xl border border-yellow-200 bg-white px-4 py-2.5 text-sm font-bold text-stone-700 hover:border-amber-300">Atur profil</a>
        </div>

        <div class="mt-8 space-y-4">
            @forelse ($orders as $order)
                <a href="{{ route('orders.show', $order) }}" class="block rounded-2xl border border-yellow-100 bg-white p-5 shadow-sm hover:border-amber-200">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-extrabold text-stone-900">{{ $order->code }}</p>
                            <p class="mt-1 text-xs text-stone-500">{{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                    </div>
                    <p class="mt-4 text-sm font-bold text-amber-700">{{ $order->total_formatted }}</p>
                </a>
            @empty
                <div class="rounded-2xl border border-yellow-100 bg-white p-8 text-center">
                    <p class="font-bold text-stone-800">Belum ada pesanan di akun ini.</p>
                    <p class="mt-2 text-sm text-stone-500">Pesanan tamu yang lama dapat dibuka lewat kode pesanan dan nomor telepon.</p>
                    <a href="{{ route('products.index') }}" class="mt-5 inline-block rounded-xl bg-amber-500 px-5 py-3 text-sm font-bold text-white hover:bg-amber-600">Lihat katalog</a>
                </div>
            @endforelse
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
        <a href="{{ route('orders.track') }}" class="mt-6 inline-block text-sm font-bold text-amber-600 hover:underline">Lacak pesanan tamu</a>
    </section>
@endsection
