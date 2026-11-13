@extends('layouts.admin')

@section('title', 'Tambah Rating')
@section('heading', 'Tambah rating')
@section('subheading', 'Tambahkan ulasan manual atau salinan ulasan Google Maps.')

@section('content')
    <form action="{{ route('admin.reviews.store') }}" method="POST" class="mx-auto max-w-4xl space-y-5 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
        @csrf
        @include('admin.reviews._form', ['submitLabel' => 'Simpan rating'])
    </form>
@endsection
