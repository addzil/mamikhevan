@extends('layouts.admin')

@section('title', 'Ubah Akun')
@section('heading', 'Ubah Akun')
@section('subheading', 'Perbarui data login atau peran akses akun.')

@section('content')
    @if (session('error'))
        <div class="mb-5 rounded-2xl bg-red-50 p-4 text-sm font-bold text-red-700">{{ session('error') }}</div>
    @endif
    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="mx-auto max-w-3xl space-y-5 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('admin.users._form', ['submitLabel' => 'Simpan perubahan'])
    </form>
@endsection
