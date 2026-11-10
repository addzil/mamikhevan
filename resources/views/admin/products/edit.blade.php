@extends('layouts.admin')

@section('title', 'Edit Produk')
@section('heading', 'Edit produk')
@section('subheading', 'Perbarui informasi produk "'.$product->name.'".')

@section('content')
    <div class="mx-auto max-w-4xl">
        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 text-xs font-extrabold text-stone-500 transition hover:text-yellow-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke daftar produk
        </a>

        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-6 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            @method('PATCH')
            @include('admin.products._form', ['submitLabel' => 'Simpan perubahan'])
        </form>
    </div>
@endsection
