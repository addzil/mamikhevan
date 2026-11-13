@extends('layouts.admin')

@section('title', 'Tambah Akun')
@section('heading', 'Tambah Akun')
@section('subheading', 'Buat akun pelanggan atau admin dengan peran yang sesuai.')

@section('content')
    <form action="{{ route('admin.users.store') }}" method="POST" class="mx-auto max-w-3xl space-y-5 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
        @csrf
        @include('admin.users._form', ['submitLabel' => 'Buat akun'])
    </form>
@endsection
