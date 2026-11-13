@extends('layouts.admin')

@section('title', 'Tambah Promo')
@section('heading', 'Tambah promo')
@section('subheading', 'Atur manfaat, sasaran, dan jadwal promo.')

@section('content')
    <form action="{{ route('admin.promotions.store') }}" method="POST" class="mx-auto max-w-4xl space-y-5">
        @csrf
        @include('admin.promotions._form')
    </form>
@endsection
