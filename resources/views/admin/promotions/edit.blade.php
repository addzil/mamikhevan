@extends('layouts.admin')

@section('title', 'Edit Promo')
@section('heading', 'Edit promo')
@section('subheading', $promotion->name)

@section('content')
    <form action="{{ route('admin.promotions.update', $promotion) }}" method="POST" class="mx-auto max-w-4xl space-y-5">
        @csrf
        @method('PUT')
        @include('admin.promotions._form')
    </form>
@endsection
