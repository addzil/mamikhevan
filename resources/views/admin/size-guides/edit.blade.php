@extends('layouts.admin')

@section('title', 'Edit Panduan Ukuran — '.$sizeGuide->name)
@section('heading', 'Edit Panduan Ukuran')
@section('subheading', 'Perbarui tipe dan entri ukuran: '.$sizeGuide->name)

@section('content')
    <div class="mx-auto max-w-4xl">
        <form action="{{ route('admin.size-guides.update', $sizeGuide) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.size-guides._form')
        </form>
    </div>
@endsection
