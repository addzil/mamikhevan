@php
    $review = $review ?? null;
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

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="reviewer_name" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Nama pemberi ulasan <span class="text-yellow-700">*</span></label>
        <input id="reviewer_name" type="text" name="reviewer_name" value="{{ old('reviewer_name', $review->reviewer_name ?? '') }}" required maxlength="100"
               class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        @error('reviewer_name') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="reviewer_city" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Kota <span class="font-semibold text-stone-400">(opsional)</span></label>
        <input id="reviewer_city" type="text" name="reviewer_city" value="{{ old('reviewer_city', $review->reviewer_city ?? '') }}" maxlength="100"
               class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        @error('reviewer_city') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
    </div>
</div>

<div class="grid gap-5 sm:grid-cols-3">
    <div>
        <label for="rating" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Rating <span class="text-yellow-700">*</span></label>
        <select id="rating" name="rating" required
                class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
            @for ($star = 5; $star >= 1; $star--)
                <option value="{{ $star }}" @selected((int) old('rating', $review->rating ?? 5) === $star)>{{ $star }} bintang</option>
            @endfor
        </select>
        @error('rating') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="source" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Sumber <span class="text-yellow-700">*</span></label>
        <select id="source" name="source" required
                class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
            @foreach (\App\Models\Review::SOURCE_LABELS as $value => $label)
                <option value="{{ $value }}" @selected(old('source', $review->source ?? \App\Models\Review::SOURCE_MANUAL) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('source') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="reviewed_at" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Tanggal ulasan</label>
        <input id="reviewed_at" type="date" name="reviewed_at" value="{{ old('reviewed_at', optional($review?->reviewed_at)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
               class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        @error('reviewed_at') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
    </div>
</div>

<div>
    <label for="product_id" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Produk terkait <span class="font-semibold text-stone-400">(opsional)</span></label>
    <select id="product_id" name="product_id"
            class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        <option value="">Tanpa produk (ulasan toko)</option>
        @foreach ($products as $product)
            <option value="{{ $product->id }}" @selected((int) old('product_id', $review->product_id ?? 0) === $product->id)>{{ $product->name }}</option>
        @endforeach
    </select>
    <p class="mt-1.5 text-[11px] font-semibold text-stone-400">Pilih produk jika ulasan ini akan tampil di halaman detail produk.</p>
    @error('product_id') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
</div>

<div class="grid gap-5 sm:grid-cols-3">
    @foreach (\App\Models\Review::ASPECTS as $field => $label)
        <div>
            <label for="{{ $field }}" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">{{ $label }}</label>
            <select id="{{ $field }}" name="{{ $field }}"
                    class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                <option value="">Tidak diisi</option>
                @for ($star = 5; $star >= 1; $star--)
                    <option value="{{ $star }}" @selected((string) old($field, $review->{$field} ?? '') === (string) $star)>{{ $star }} bintang</option>
                @endfor
            </select>
            @error($field) <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
        </div>
    @endforeach
</div>

<div>
    <label for="comment" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Ulasan</label>
    <textarea id="comment" name="comment" rows="4" maxlength="1000"
              class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">{{ old('comment', $review->comment ?? '') }}</textarea>
    @error('comment') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
</div>

<div>
    <label for="admin_reply" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Balasan toko <span class="font-semibold text-stone-400">(opsional)</span></label>
    <textarea id="admin_reply" name="admin_reply" rows="3" maxlength="2000"
              class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">{{ old('admin_reply', $review->admin_reply ?? '') }}</textarea>
    <p class="mt-1.5 text-[11px] font-semibold text-stone-400">Balasan akan tampil di bawah ulasan pada halaman toko.</p>
    @error('admin_reply') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
</div>

<div>
    <input type="hidden" name="is_visible" value="0">
    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-stone-200 p-4">
        <input type="checkbox" name="is_visible" value="1" @checked((bool) old('is_visible', $review->is_visible ?? true))
               class="mt-0.5 rounded border-stone-300 text-yellow-700 focus:ring-amber-200">
        <span>
            <span class="block text-sm font-extrabold text-stone-800">Tampilkan rating di toko</span>
            <span class="mt-0.5 block text-xs font-semibold text-stone-400">Rating tersembunyi tetap tersimpan di admin, tetapi tidak tampil di beranda maupun halaman produk.</span>
        </span>
    </label>
</div>

<div class="flex flex-col-reverse gap-3 border-t border-stone-100 pt-5 sm:flex-row sm:items-center sm:justify-end">
    <a href="{{ route('admin.reviews.index') }}" class="inline-flex items-center justify-center rounded-xl border border-stone-200 px-4 py-2.5 text-xs font-extrabold text-stone-600 transition hover:bg-stone-50">
        Batal
    </a>
    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-amber-600">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
        {{ $submitLabel ?? 'Simpan rating' }}
    </button>
</div>
