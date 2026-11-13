@extends('layouts.admin')

@section('title', 'Manajemen User')
@section('heading', 'Manajemen User')
@section('subheading', 'Kelola akun tim admin dan pelanggan pada bagian yang terpisah.')

@section('content')
    @if (session('success'))
        <div class="mb-5 rounded-2xl bg-emerald-50 p-4 text-sm font-bold text-emerald-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-5 rounded-2xl bg-red-50 p-4 text-sm font-bold text-red-700">{{ session('error') }}</div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <nav class="flex gap-2" aria-label="Bagian manajemen user">
            <a href="#akun-admin" class="rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-xs font-extrabold text-stone-700 hover:border-amber-300">Akun Admin ({{ $staffUsers->total() }})</a>
            <a href="#akun-pelanggan" class="rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-xs font-extrabold text-stone-700 hover:border-amber-300">Akun Pelanggan ({{ $customers->total() }})</a>
        </nav>
        <a href="{{ route('admin.users.create') }}" class="rounded-xl bg-amber-500 px-4 py-3 text-sm font-extrabold text-white hover:bg-amber-600">Tambah akun</a>
    </div>

    <section id="akun-admin" class="mb-8 scroll-mt-6">
        <div class="mb-4">
            <h2 class="font-display text-xl font-extrabold text-stone-900">Akun Admin</h2>
            <p class="mt-1 text-sm text-stone-500">Berisi akun Administrator, Manajemen, dan Operator.</p>
        </div>
        @include('admin.users._table', [
            'users' => $staffUsers,
            'showRole' => true,
            'emptyMessage' => 'Belum ada akun admin.',
        ])
    </section>

    <section id="akun-pelanggan" class="scroll-mt-6">
        <div class="mb-4">
            <h2 class="font-display text-xl font-extrabold text-stone-900">Akun Pelanggan</h2>
            <p class="mt-1 text-sm text-stone-500">Berisi akun pelanggan yang terdaftar di toko.</p>
        </div>
        @include('admin.users._table', [
            'users' => $customers,
            'showRole' => false,
            'emptyMessage' => 'Belum ada akun pelanggan.',
        ])
    </section>
@endsection
