@extends('layouts.admin')

@section('title', 'Pesan Masuk')
@section('heading', 'Pesan masuk')
@section('subheading', 'Pesan dari formulir kontak pelanggan.')

@section('content')
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <p class="text-xs font-semibold text-stone-400">
            {{ $unreadCount }} pesan belum dibaca dari total {{ $messages->total() }} pesan.
        </p>
        <div class="flex flex-wrap gap-2">
            @foreach (['all' => 'Semua', 'unread' => 'Belum dibaca', 'read' => 'Sudah dibaca'] as $value => $label)
                <a href="{{ route('admin.messages.index', ['filter' => $value]) }}"
                   class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-[11px] font-extrabold transition {{ $filter === $value ? 'border-amber-200 bg-amber-50 text-yellow-700' : 'border-stone-200 bg-white text-stone-500 hover:border-amber-200 hover:text-yellow-700' }}">
                    {{ $label }}
                    @if ($value === 'unread' && $unreadCount > 0)
                        <span class="grid h-4 min-w-4 place-items-center rounded-full bg-amber-500 px-1 text-[9px] text-white">{{ $unreadCount }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <div class="mt-5 overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
        @forelse ($messages as $message)
            <a href="{{ route('admin.messages.show', $message) }}"
               class="flex items-start gap-4 border-b border-stone-100 px-5 py-4 transition last:border-0 hover:bg-stone-50/70 {{ $message->is_read ? '' : 'bg-yellow-50/40' }}">
                <div class="grid h-11 w-11 flex-none place-items-center rounded-2xl text-sm font-extrabold {{ $message->is_read ? 'bg-stone-100 text-stone-500' : 'bg-amber-100 text-yellow-700' }}">
                    {{ strtoupper(mb_substr($message->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <p class="text-sm font-extrabold text-stone-800">{{ $message->name }}</p>
                        @if (! $message->is_read)
                            <span class="rounded-full bg-amber-500 px-1.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-white">Baru</span>
                        @endif
                        <span class="text-[11px] font-semibold text-stone-400">{{ $message->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="mt-0.5 truncate text-xs font-bold text-stone-600">{{ $message->subject }}</p>
                    <p class="mt-1 line-clamp-2 text-xs font-semibold leading-5 text-stone-400">{{ $message->message }}</p>
                    <p class="mt-1.5 text-[11px] font-semibold text-stone-400">{{ $message->email }}{{ $message->phone ? ' · '.$message->phone : '' }}</p>
                </div>
                <svg class="mt-1 h-4 w-4 flex-none text-stone-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
            </a>
        @empty
            <div class="px-5 py-16 text-center">
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-yellow-50 text-2xl">📭</div>
                <p class="mt-3 font-display text-lg font-bold text-stone-700">Belum ada pesan</p>
                <p class="mt-1 text-xs font-semibold text-stone-400">
                    {{ $filter === 'all' ? 'Pesan dari pelanggan akan muncul di sini.' : 'Tidak ada pesan pada filter ini.' }}
                </p>
            </div>
        @endforelse
    </div>

    <div class="mt-5">
        {{ $messages->links('vendor.pagination.mamikhevan') }}
    </div>
@endsection
