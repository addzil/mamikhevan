@php
    $category = $category ?? null;
@endphp

@if ($errors->any())
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
        <p class="text-sm font-extrabold text-amber-700">Ada data yang perlu diperbaiki:</p>
        <ul class="mt-2 list-inside list-disc space-y-1 text-xs font-semibold text-yellow-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-5 sm:grid-cols-[1fr_140px]">
    <div>
        <label for="name" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Nama kategori <span class="text-yellow-700">*</span></label>
        <input id="name" type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required
               class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        @error('name') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="icon" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Ikon</label>
        <input id="icon" type="text" name="icon" value="{{ old('icon', $category->icon ?? '') }}" placeholder="👕" maxlength="10"
               class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-center text-lg focus:border-amber-300 focus:ring-amber-200">
        @error('icon') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
    </div>
</div>

<div>
    <label for="slug" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Slug <span class="font-semibold text-stone-400">(opsional)</span></label>
    <input id="slug" type="text" name="slug" value="{{ old('slug', $category->slug ?? '') }}" placeholder="otomatis-dari-nama"
           class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
    <p class="mt-1.5 text-[11px] font-semibold text-stone-400">Digunakan untuk URL filter katalog. Biarkan kosong untuk dibuat otomatis.</p>
    @error('slug') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
</div>

<div>
    <label for="description" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Deskripsi</label>
    <textarea id="description" name="description" rows="3" maxlength="500"
              class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">{{ old('description', $category->description ?? '') }}</textarea>
    @error('description') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
</div>

<div>
    <input type="hidden" name="is_active" value="0">
    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-stone-200 p-4">
        <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $category->is_active ?? true))
               class="mt-0.5 rounded border-stone-300 text-yellow-700 focus:ring-amber-200">
        <span>
            <span class="block text-sm font-extrabold text-stone-800">Tampilkan kategori di toko</span>
            <span class="mt-0.5 block text-xs font-semibold text-stone-400">Kategori nonaktif tidak muncul di filter katalog publik.</span>
        </span>
    </label>
</div>

<div class="flex flex-col-reverse gap-3 border-t border-stone-100 pt-5 sm:flex-row sm:items-center sm:justify-end">
    <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center justify-center rounded-xl border border-stone-200 px-4 py-2.5 text-xs font-extrabold text-stone-600 transition hover:bg-stone-50">
        Batal
    </a>
    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-amber-600">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
        {{ $submitLabel ?? 'Simpan kategori' }}
    </button>
</div>
