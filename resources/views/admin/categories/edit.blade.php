@extends('layouts.admin')

@section('title', 'Edit Kategori')
@section('heading', 'Edit kategori')
@section('subheading', 'Perbarui informasi kategori "'.$category->name.'".')

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-2 text-xs font-extrabold text-stone-500 transition hover:text-yellow-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke daftar kategori
        </a>

        <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="mt-4 space-y-6 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            @method('PATCH')
            @include('admin.categories._form', ['submitLabel' => 'Simpan perubahan'])
        </form>
    </div>
@endsection
