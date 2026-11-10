@php
    $sizeGuide = $sizeGuide ?? null;
    $allFieldLabels = \App\Models\SizeGuideEntry::MEASUREMENT_LABELS;

    // Selected measurement fields (old input → existing model → empty)
    $selectedFieldsInit = old('measurement_fields', $sizeGuide?->measurement_fields ?? []);

    // Existing entries for edit (old input → model entries as plain arrays → empty)
    $existingEntries = old('entries');
    if ($existingEntries === null) {
        $existingEntries = $sizeGuide
            ? $sizeGuide->entries->map(fn ($e) => $e->toArray())->toArray()
            : [];
    }
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4">
        <p class="text-sm font-extrabold text-amber-700">Ada data yang perlu diperbaiki:</p>
        <ul class="mt-2 list-inside list-disc space-y-1 text-xs font-semibold text-yellow-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div
    x-data="sizeGuideForm(
        @js(array_keys($allFieldLabels)),
        @js($allFieldLabels),
        @js($selectedFieldsInit),
        @js($existingEntries)
    )"
    class="space-y-6">

    {{-- === SECTION A: Info Tipe === --}}
    <div class="rounded-3xl border border-stone-200 bg-white p-6">
        <h2 class="mb-5 text-sm font-extrabold uppercase tracking-wider text-stone-500">Informasi Tipe Panduan Ukuran</h2>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="name" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Nama tipe <span class="text-yellow-700">*</span></label>
                <input id="name" type="text" name="name"
                       value="{{ old('name', $sizeGuide?->name ?? '') }}"
                       required maxlength="80"
                       class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200"
                       placeholder="Contoh: Atasan/Kaos">
                @error('name') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="slug" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Slug <span class="font-semibold text-stone-400">(opsional)</span></label>
                <input id="slug" type="text" name="slug"
                       value="{{ old('slug', $sizeGuide?->slug ?? '') }}"
                       placeholder="otomatis-dari-nama"
                       class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                @error('slug') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Deskripsi <span class="font-semibold text-stone-400">(opsional)</span></label>
                <input id="description" type="text" name="description"
                       value="{{ old('description', $sizeGuide?->description ?? '') }}"
                       maxlength="300"
                       class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200"
                       placeholder="Deskripsi singkat jenis pakaian ini">
                @error('description') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Measurement Fields <span class="text-yellow-700">*</span></label>
                <p class="mt-1 text-[11px] font-semibold text-stone-400">Pilih kolom dimensi yang relevan untuk tipe pakaian ini. Kolom yang dipilih akan muncul di tabel entri.</p>
                <div class="mt-3 flex flex-wrap gap-3">
                    @foreach ($allFieldLabels as $fieldKey => $fieldLabel)
                        <label class="cursor-pointer" x-data="{}">
                            <input type="checkbox"
                                   name="measurement_fields[]"
                                   value="{{ $fieldKey }}"
                                   x-model="selectedFields"
                                   class="peer sr-only">
                            <span class="inline-flex items-center rounded-xl border px-3.5 py-2 text-xs font-bold transition"
                                  :class="selectedFields.includes('{{ $fieldKey }}')
                                    ? 'border-amber-500 bg-amber-50 text-yellow-700'
                                    : 'border-stone-200 text-stone-500 hover:border-amber-300'">
                                {{ $fieldLabel }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('measurement_fields') <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- === SECTION B: Entri Ukuran === --}}
    <div class="rounded-3xl border border-stone-200 bg-white p-6">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-stone-500">Entri Ukuran</h2>
                <p class="mt-1 text-[11px] font-semibold text-stone-400">Tambahkan baris ukuran. Kolom berubah mengikuti pilihan measurement fields di atas.</p>
            </div>
            <button type="button" @click="addEntry()"
                    class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2 text-xs font-extrabold text-yellow-700 transition hover:bg-amber-100">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Tambah Baris
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-stone-100">
                        <th class="pb-3 pr-3 text-left font-extrabold uppercase tracking-wider text-stone-400">Label <span class="text-yellow-700">*</span></th>
                        <th class="pb-3 pr-3 text-left font-extrabold uppercase tracking-wider text-stone-400">Usia</th>
                        <th class="pb-3 pr-3 text-left font-extrabold uppercase tracking-wider text-stone-400">BB (kg)</th>
                        <th class="pb-3 pr-3 text-left font-extrabold uppercase tracking-wider text-stone-400">TB (cm)</th>
                        <template x-for="field in selectedFields" :key="field">
                            <th class="pb-3 pr-3 text-left font-extrabold uppercase tracking-wider text-stone-400" x-text="fieldLabels[field]"></th>
                        </template>
                        <th class="pb-3 text-left font-extrabold uppercase tracking-wider text-stone-400">Urut</th>
                        <th class="pb-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    <template x-for="(entry, index) in entries" :key="index">
                        <tr class="transition hover:bg-stone-50/40">
                            <td class="py-2 pr-2">
                                <input type="text" :name="`entries[${index}][label]`"
                                       x-model="entry.label" required maxlength="20"
                                       placeholder="1T"
                                       class="w-20 rounded-xl border-stone-200 py-1.5 text-xs focus:border-amber-300 focus:ring-amber-200">
                            </td>
                            <td class="py-2 pr-2">
                                <input type="text" :name="`entries[${index}][age_range]`"
                                       x-model="entry.age_range" maxlength="40"
                                       placeholder="1 tahun"
                                       class="w-24 rounded-xl border-stone-200 py-1.5 text-xs focus:border-amber-300 focus:ring-amber-200">
                            </td>
                            <td class="py-2 pr-2">
                                <input type="text" :name="`entries[${index}][weight_kg]`"
                                       x-model="entry.weight_kg" maxlength="20"
                                       placeholder="10-12 kg"
                                       class="w-24 rounded-xl border-stone-200 py-1.5 text-xs focus:border-amber-300 focus:ring-amber-200">
                            </td>
                            <td class="py-2 pr-2">
                                <input type="text" :name="`entries[${index}][height_cm]`"
                                       x-model="entry.height_cm" maxlength="20"
                                       placeholder="76-84 cm"
                                       class="w-24 rounded-xl border-stone-200 py-1.5 text-xs focus:border-amber-300 focus:ring-amber-200">
                            </td>
                            <template x-for="field in selectedFields" :key="field">
                                <td class="py-2 pr-2">
                                    <input type="text" :name="`entries[${index}][${field}]`"
                                           x-model="entry[field]" maxlength="20"
                                           placeholder="cm"
                                           class="w-20 rounded-xl border-stone-200 py-1.5 text-xs focus:border-amber-300 focus:ring-amber-200">
                                </td>
                            </template>
                            <td class="py-2 pr-2">
                                <input type="number" :name="`entries[${index}][sort_order]`"
                                       x-model.number="entry.sort_order" min="0"
                                       class="w-16 rounded-xl border-stone-200 py-1.5 text-xs focus:border-amber-300 focus:ring-amber-200">
                            </td>
                            <td class="py-2">
                                <button type="button" @click="removeEntry(index)"
                                        class="grid h-7 w-7 place-items-center rounded-xl text-stone-300 transition hover:bg-amber-50 hover:text-yellow-700"
                                        aria-label="Hapus baris">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div x-show="entries.length === 0" class="py-8 text-center text-xs font-semibold text-stone-400">
            Belum ada entri. Klik "Tambah Baris" untuk menambahkan ukuran.
        </div>

        @error('entries') <p class="mt-2 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
        @error('entries.*') <p class="mt-2 text-xs font-bold text-yellow-700">{{ $message }}</p> @enderror
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-stone-100 pt-2 sm:flex-row sm:items-center sm:justify-end">
        <a href="{{ route('admin.size-guides.index') }}"
           class="inline-flex items-center justify-center rounded-xl border border-stone-200 px-4 py-2.5 text-xs font-extrabold text-stone-600 transition hover:bg-stone-50">
            Batal
        </a>
        <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-amber-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            {{ $sizeGuide ? 'Perbarui Panduan Ukuran' : 'Simpan Panduan Ukuran' }}
        </button>
    </div>
</div>

@push('scripts')
<script>
function sizeGuideForm(allFields, fieldLabels, selectedFields, existingEntries) {
    return {
        allFields: allFields,
        fieldLabels: fieldLabels,
        selectedFields: selectedFields || [],
        entries: existingEntries || [],

        addEntry() {
            this.entries.push({
                label: '',
                age_range: '',
                weight_kg: '',
                height_cm: '',
                chest_cm: '',
                waist_cm: '',
                hip_cm: '',
                thigh_cm: '',
                length_cm: '',
                sleeve_cm: '',
                sort_order: this.entries.length,
            });
        },

        removeEntry(index) {
            this.entries.splice(index, 1);
        },
    };
}
</script>
@endpush
