@extends('layouts.admin')

@section('title', 'Logo Toko')
@section('heading', 'Logo toko')
@section('subheading', 'Ganti logo yang tampil di header, footer, dan tab browser web publik.')

@section('content')
    <div class="mx-auto max-w-3xl">
        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                <p class="font-extrabold">Logo belum tersimpan. Periksa berkas yang dipilih.</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.site-settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            @method('PUT')

            <div>
                <h2 class="font-display text-lg font-bold text-stone-900">Logo saat ini</h2>
                @if ($settings->logo_path)
                    <div class="mt-3 rounded-2xl border border-stone-200 bg-stone-50 p-5">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings->logo_path) }}" alt="Logo toko saat ini" class="max-h-32 max-w-full object-contain">
                    </div>
                @else
                    <p class="mt-2 text-sm text-stone-500">Belum ada gambar logo. Web masih memakai ikon dan tulisan MamikhevanShop bawaan.</p>
                @endif
            </div>

            <div>
                <label for="logo" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Unggah logo baru</label>
                <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp"
                       class="mt-2 block w-full rounded-xl border border-stone-200 p-2.5 text-sm text-stone-700 file:mr-4 file:rounded-lg file:border-0 file:bg-amber-50 file:px-3 file:py-2 file:font-bold file:text-amber-700">
                <p class="mt-2 text-xs text-stone-500">PNG, JPG, atau WebP, maksimal 2 MB. Gunakan gambar dengan latar transparan agar cocok di header dan footer.</p>
                @error('logo') <p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
            </div>

            @if ($settings->logo_path)
                <label class="flex items-start gap-3 rounded-2xl border border-stone-200 p-4">
                    <input type="checkbox" name="remove_logo" value="1" class="mt-0.5 rounded border-stone-300 text-amber-600 focus:ring-amber-200">
                    <span class="text-sm font-semibold text-stone-700">Hapus logo dan gunakan tampilan bawaan</span>
                </label>
            @endif

            <div class="flex justify-end border-t border-stone-100 pt-5">
                <button type="submit" class="rounded-xl bg-amber-500 px-5 py-2.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-amber-600">Simpan logo</button>
            </div>
        </form>
    </div>
@endsection
