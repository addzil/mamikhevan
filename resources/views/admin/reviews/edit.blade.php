@extends('layouts.admin')

@section('title', 'Edit Rating')
@section('heading', 'Edit rating')
@section('subheading', 'Perbarui isi ulasan, sumber, atau visibilitas rating.')

@section('content')
    <form action="{{ route('admin.reviews.update', $review) }}" method="POST" class="mx-auto max-w-4xl space-y-5 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
        @csrf
        @method('PUT')
        @include('admin.reviews._form', ['submitLabel' => 'Simpan perubahan'])
    </form>
@endsection
