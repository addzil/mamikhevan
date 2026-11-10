@extends('layouts.admin')

@section('title', 'Tambah Panduan Ukuran')
@section('heading', 'Tambah Panduan Ukuran')
@section('subheading', 'Buat tipe panduan ukuran baru beserta entri ukurannya.')

@section('content')
    <div class="mx-auto max-w-4xl">
        <form action="{{ route('admin.size-guides.store') }}" method="POST">
            @csrf
            @include('admin.size-guides._form')
        </form>
    </div>
@endsection
