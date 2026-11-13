@php
    $product = $product ?? null;
    $sizesValue = old('sizes', $product?->sizes ?? []);
    if (is_array($sizesValue)) {
        $sizesValue = implode(', ', $sizesValue);
    }
    $sizeGuideTypes = $sizeGuideTypes ?? collect();
    $selectedSizeGuideTypeId = old('size_guide_type_id', $product?->size_guide_type_id ?? '');
    $selectedSizes = old('sizes', $product?->sizes ?? []);
    if (is_string($selectedSizes)) {
        $selectedSizes = collect(explode(',', $selectedSizes))
            ->map(fn($s) => trim($s))
            ->filter()
            ->values()
            ->all();
    }
    $initialSizeStocks = collect($selectedSizes)
        ->mapWithKeys(fn (string $size): array => [$size => (int) ($product?->size_stocks[$size] ?? 0)])
        ->all();
    $oldSizeStocks = old('size_stocks');
    if (is_array($oldSizeStocks)) {
        foreach ($selectedSizes as $index => $size) {
            $initialSizeStocks[$size] = (int) ($oldSizeStocks[$index] ?? 0);
        }
    }
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

<div class="grid gap-5 sm:grid-cols-2"
     x-data="sizeGuideSelector({{ json_encode($sizeGuideTypes->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'entries' => $t->entries->map(fn($e) => ['label' => $e->label])->values()->all()])->values()->all()) }}, {{ (int) ($selectedSizeGuideTypeId ?? 0) }}, {{ json_encode($selectedSizes ?? []) }}, {{ json_encode($initialSizeStocks) }})">
    <div class="sm:col-span-2">
        <label for="name"
               class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Nama produk
            <span class="text-yellow-700">*</span></label>
        <input id="name" type="text" name="name"
               value="{{ old('name', $product->name ?? '') }}" required maxlength="120"
               class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        @error('name')
            <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="slug"
               class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Slug <span
                  class="font-semibold text-stone-400">(opsional)</span></label>
        <input id="slug" type="text" name="slug"
               value="{{ old('slug', $product->slug ?? '') }}" placeholder="otomatis-dari-nama"
               class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        @error('slug')
            <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="category_id"
               class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Kategori</label>
        <select id="category_id" name="category_id"
                class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
            <option value="">Tanpa kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id ?? 0) === $category->id)>
                    {{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')
            <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="price"
               class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Harga jual
            (Rp) <span class="text-yellow-700">*</span></label>
        <input id="price" type="number" name="price"
               value="{{ old('price', $product->price ?? '') }}" required min="1000"
               max="100000000" step="500"
               class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        @error('price')
            <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="cost_price"
               class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Harga modal
            (Rp) <span class="font-semibold text-stone-400">(opsional)</span></label>
        <input id="cost_price" type="number" name="cost_price"
               value="{{ old('cost_price', $product->cost_price ?? '') }}" min="0"
               max="100000000" step="500"
               class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        <p class="mt-1.5 text-[11px] font-semibold text-stone-400">Dipakai untuk menghitung margin
            keuntungan di dashboard. Kosongkan jika belum diketahui.</p>
        @error('cost_price')
            <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="compare_price"
               class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Harga sebelum
            diskon <span class="font-semibold text-stone-400">(opsional)</span></label>
        <input id="compare_price" type="number" name="compare_price"
               value="{{ old('compare_price', $product->compare_price ?? '') }}" min="0"
               max="100000000" step="500"
               class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        <p class="mt-1.5 text-[11px] font-semibold text-stone-400">Isi lebih tinggi dari harga jual
            untuk menampilkan badge diskon.</p>
        @error('compare_price')
            <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="stock"
               class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Stok <span
                  class="text-yellow-700">*</span></label>
        @if ($product)
            <input id="stock" type="number" value="{{ $product->stock }}" disabled
                   class="mt-2 w-full rounded-xl border-stone-200 bg-stone-100 py-2.5 text-sm text-stone-500">
            @if ($product->sizeList() !== [])
                <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($product->sizeStockMap() as $size => $quantity)
                        <div class="rounded-xl border border-stone-200 bg-stone-50 px-3 py-2 text-xs">
                            <span class="font-bold text-stone-500">Ukuran {{ $size }}</span>
                            <span class="float-right font-extrabold text-stone-800">{{ number_format($quantity) }} unit</span>
                        </div>
                    @endforeach
                </div>
                @if ($product->unallocated_stock > 0)
                    <p class="mt-2 rounded-xl bg-amber-50 px-3 py-2 text-xs font-bold leading-5 text-amber-800">{{ number_format($product->unallocated_stock) }} unit stok lama belum dialokasikan ke ukuran. Selesaikan opname ukuran sebelum produk dapat dibeli.</p>
                @endif
            @endif
            <p class="mt-2 text-[11px] font-semibold text-stone-400">Sesuaikan jumlah per ukuran melalui <a href="{{ route('admin.stock-opname.index') }}" class="font-extrabold text-amber-700 hover:underline">menu Opname Stok</a>.</p>
        @else
            <div x-show="selectedSizes.length === 0">
                <input id="stock" type="number" name="stock"
                       value="{{ old('stock', 0) }}" min="0" max="99999"
                       :required="selectedSizes.length === 0"
                       class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                <p class="mt-1.5 text-[11px] font-semibold text-stone-400">Produk tanpa pilihan ukuran menggunakan satu stok total.</p>
                @error('stock')
                    <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
                @enderror
            </div>
            <div x-show="selectedSizes.length > 0" x-cloak class="mt-3">
                <p class="text-[11px] font-semibold text-stone-400">Masukkan stok awal satu per satu untuk ukuran yang dipilih. Total stok akan dihitung otomatis.</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <template x-for="(size, index) in selectedSizes" :key="size">
                        <label class="rounded-xl border border-stone-200 p-3">
                            <span class="text-xs font-extrabold text-stone-600" x-text="'Ukuran ' + size"></span>
                            <input type="number" min="0" max="99999" required
                                   :name="'size_stocks[' + index + ']'"
                                   :value="sizeStocks[size] ?? 0"
                                   @input="sizeStocks[size] = Number($event.target.value)"
                                   class="mt-2 w-full rounded-lg border-stone-200 py-2 text-sm focus:border-amber-300 focus:ring-amber-200">
                        </label>
                    </template>
                </div>
                @error('size_stocks')
                    <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
                @enderror
                @error('size_stocks.*')
                    <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
                @enderror
            </div>
        @endif
    </div>

    <div>
        <label for="weight_grams" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Berat per item (gram)</label>
        <input id="weight_grams" type="number" name="weight_grams"
               value="{{ old('weight_grams', $product->weight_grams ?? '') }}" min="1" max="30000"
               class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        <p class="mt-1.5 text-[11px] font-semibold text-stone-400">Isi berat produk bersama kemasan untuk perhitungan ongkir.</p>
        @error('weight_grams')
            <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
        @enderror
    </div>

    <div
         class="grid gap-5 sm:col-span-2 sm:grid-cols-2">

        <div>
            <label for="size_guide_type_id"
                   class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Tipe
                Panduan Ukuran</label>
            <select id="size_guide_type_id" name="size_guide_type_id"
                    x-model="selectedTypeId"
                    @change="onTypeChange()"
                    class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                <option value="">Tanpa panduan ukuran</option>
                <template x-for="type in allTypes" :key="type.id">
                    <option :value="type.id" :selected="selectedTypeId == type.id"
                            x-text="type.name"></option>
                </template>
            </select>
            @error('size_guide_type_id')
                <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="selectedTypeId" x-cloak>
            <label class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Ukuran
                Tersedia <span class="text-yellow-700">*</span></label>
            <div class="mt-2 flex flex-wrap gap-2">
                <template x-for="entry in currentEntries" :key="entry.label">
                    <label class="cursor-pointer">
                        <input type="checkbox" name="sizes[]"
                               :value="entry.label"
                               :checked="selectedSizes.includes(entry.label)"
                               @change="toggleSize(entry.label)"
                               class="peer sr-only">
                        <span class="inline-flex cursor-pointer items-center rounded-xl border px-3 py-1.5 text-xs font-bold transition"
                              :class="selectedSizes.includes(entry.label) ?
                                  'border-amber-500 bg-amber-50 text-yellow-700' :
                                  'border-stone-200 text-stone-500 hover:border-amber-300'"
                              x-text="entry.label"></span>
                    </label>
                </template>
            </div>
            <p class="mt-1.5 text-[11px] font-semibold text-stone-400">Centang ukuran yang tersedia
                untuk produk ini.</p>
            @error('sizes')
                <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="!selectedTypeId">
            <label class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Ukuran
                (teks bebas)</label>
            <input type="text" name="sizes_text"
                   :value="selectedSizes.join(', ')"
                   @input="parseSizesText($event.target.value)"
                   :disabled="!!selectedTypeId"
                   placeholder="1T, 2T, 3T"
                   class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
            <template x-for="sz in selectedSizes" :key="sz">
                <input type="hidden" name="sizes[]" :value="sz">
            </template>
            <p class="mt-1.5 text-[11px] font-semibold text-stone-400">Pisahkan dengan koma jika
                tidak pakai panduan ukuran.</p>
        </div>
    </div>

    <div>
        <label for="gender"
               class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Ditujukan
            untuk <span class="text-yellow-700">*</span></label>
        <select id="gender" name="gender"
                class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
            <option value="uniseks" @selected(old('gender', $product->gender ?? 'uniseks') === 'uniseks')>Uniseks</option>
            <option value="laki-laki" @selected(old('gender', $product->gender ?? '') === 'laki-laki')>Anak laki-laki</option>
            <option value="perempuan" @selected(old('gender', $product->gender ?? '') === 'perempuan')>Anak perempuan</option>
        </select>
        @error('gender')
            <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="age_group"
               class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Kelompok
            usia</label>
        <input id="age_group" type="text" name="age_group"
               value="{{ old('age_group', $product->age_group ?? '') }}" placeholder="2-6 tahun"
               class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        @error('age_group')
            <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="material"
               class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Bahan</label>
        <input id="material" type="text" name="material"
               value="{{ old('material', $product->material ?? '') }}"
               placeholder="Katun organik 100%"
               class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        @error('material')
            <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="description"
               class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Deskripsi
            <span class="text-yellow-700">*</span></label>
        <textarea id="description" name="description" rows="5" required minlength="20"
                  maxlength="3000"
                  class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
        @enderror
    </div>

    @php
        $existingGallery = $product?->images ?? collect();
    @endphp

    <div class="sm:col-span-2"
         x-data="{
            files: [],
            previews: [],
            handleFiles(event) {
                this.files = this.files.concat(Array.from(event.target.files)).slice(0, 8);
                event.target.value = '';
                this.refreshPreviews();
                this.syncInput();
            },
            removeFile(index) {
                URL.revokeObjectURL(this.previews[index].url);
                this.files.splice(index, 1);
                this.refreshPreviews();
                this.syncInput();
            },
            refreshPreviews() {
                this.previews = this.files.map((file) => ({ name: file.name, url: URL.createObjectURL(file) }));
            },
            syncInput() {
                const dataTransfer = new DataTransfer();
                this.files.forEach((file) => dataTransfer.items.add(file));
                this.$refs.imagesInput.files = dataTransfer.files;
            }
         }">
        <label class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Foto
            produk</label>
        <p class="mt-1 text-[11px] font-semibold text-stone-400">Bisa unggah beberapa foto
            sekaligus. Foto pertama yang diunggah akan menjadi foto utama jika produk belum
            punya foto.</p>
        <div
             class="mt-2 rounded-2xl border border-dashed border-stone-300 bg-stone-50/60 p-4">
            <input x-ref="imagesInput" type="file" name="images[]" multiple
                   accept=".jpg,.jpeg,.png,.webp,image/*" @change="handleFiles($event)"
                   class="block w-full text-xs font-semibold text-stone-500 file:mr-3 file:rounded-xl file:border-0 file:bg-amber-500 file:px-4 file:py-2.5 file:text-xs file:font-extrabold file:text-white hover:file:bg-amber-600">
            <p class="mt-2 text-[11px] font-semibold text-stone-400">Format JPG, PNG, atau
                WEBP. Maksimal 2 MB per foto dan 8 foto sekali unggah. Rasio persegi paling
                optimal.</p>

            <div
                 class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4"
                 x-show="{{ $product?->image_url || $existingGallery->isNotEmpty() ? 'true' : 'false' }} || previews.length > 0">
                @if ($product?->image_url)
                    <label
                           class="group relative block aspect-square cursor-pointer overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm">
                        <img src="{{ $product->image_url }}" alt="Foto utama {{ $product->name }}"
                             class="h-full w-full object-cover">
                        <span
                              class="absolute left-2 top-2 rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-white shadow-sm">Utama</span>
                        <input type="checkbox" name="remove_image" value="1" class="peer sr-only">
                        <span
                              class="absolute inset-x-2 bottom-2 rounded-lg bg-white/95 py-1 text-center text-[10px] font-extrabold text-stone-600 shadow-sm transition peer-checked:bg-red-500 peer-checked:text-white">Hapus</span>
                    </label>
                @endif

                @foreach ($existingGallery as $image)
                    <label
                           class="group relative block aspect-square cursor-pointer overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                        <img src="{{ $image->url }}" alt="Foto tambahan {{ $product->name }}"
                             class="h-full w-full object-cover">
                        <input type="checkbox" name="removed_images[]" value="{{ $image->id }}"
                               class="peer sr-only">
                        <span
                              class="absolute inset-x-2 bottom-2 rounded-lg bg-white/95 py-1 text-center text-[10px] font-extrabold text-stone-600 shadow-sm transition peer-checked:bg-red-500 peer-checked:text-white">Hapus</span>
                    </label>
                @endforeach

                <template x-for="(preview, index) in previews" :key="preview.url">
                    <div
                         class="group relative aspect-square overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm">
                        <img :src="preview.url" :alt="preview.name"
                             class="h-full w-full object-cover">
                        <span
                              class="absolute left-2 top-2 rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-white shadow-sm">Baru</span>
                        <button type="button" @click="removeFile(index)"
                                class="absolute right-2 top-2 grid h-6 w-6 place-items-center rounded-full bg-white/95 text-sm font-extrabold text-stone-600 shadow-sm transition hover:bg-red-500 hover:text-white"
                                aria-label="Batalkan foto ini">&times;</button>
                    </div>
                </template>
            </div>

            @error('images')
                <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
            @enderror
            @error('images.*')
                <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
            @enderror
            @error('removed_images.*')
                <p class="mt-1.5 text-xs font-bold text-yellow-700">{{ $message }}</p>
            @enderror
        </div>
        @if ($product?->image)
            <p class="mt-2 text-[11px] font-semibold text-stone-400">Centang "Hapus" untuk
                menghapus foto. Biarkan kosong jika tidak ingin mengubah foto saat ini.</p>
        @endif
    </div>

    <div>
        <input type="hidden" name="is_featured" value="0">
        <label
               class="flex h-full cursor-pointer items-start gap-3 rounded-2xl border border-stone-200 p-4">
            <input type="checkbox" name="is_featured" value="1" @checked((bool) old('is_featured', $product->is_featured ?? false))
                   class="mt-0.5 rounded border-stone-300 text-yellow-700 focus:ring-amber-200">
            <span>
                <span class="block text-sm font-extrabold text-stone-800">Produk favorit</span>
                <span class="mt-0.5 block text-xs font-semibold text-stone-400">Tampil di bagian
                    produk favorit pada beranda.</span>
            </span>
        </label>
    </div>

    <div>
        <input type="hidden" name="is_active" value="0">
        <label
               class="flex h-full cursor-pointer items-start gap-3 rounded-2xl border border-stone-200 p-4">
            <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $product->is_active ?? true))
                   class="mt-0.5 rounded border-stone-300 text-yellow-700 focus:ring-amber-200">
            <span>
                <span class="block text-sm font-extrabold text-stone-800">Aktif dijual</span>
                <span class="mt-0.5 block text-xs font-semibold text-stone-400">Produk nonaktif
                    tidak tampil di katalog publik.</span>
            </span>
        </label>
    </div>

    <div>
        <input type="hidden" name="cod_available" value="0">
        <label class="flex h-full cursor-pointer items-start gap-3 rounded-2xl border border-stone-200 p-4">
            <input type="checkbox" name="cod_available" value="1" @checked((bool) old('cod_available', $product->cod_available ?? true)) class="mt-0.5 rounded border-stone-300 text-yellow-700 focus:ring-amber-200">
            <span>
                <span class="block text-sm font-extrabold text-stone-800">Tersedia untuk COD</span>
                <span class="mt-0.5 block text-xs font-semibold text-stone-400">Jika tidak dicentang, pelanggan harus memilih transfer saat produk ini ada di keranjang.</span>
            </span>
        </label>
    </div>

    <div>
        <input type="hidden" name="free_shipping" value="0">
        <label class="flex h-full cursor-pointer items-start gap-3 rounded-2xl border border-stone-200 p-4">
            <input type="checkbox" name="free_shipping" value="1" @checked((bool) old('free_shipping', $product->free_shipping ?? false)) class="mt-0.5 rounded border-stone-300 text-yellow-700 focus:ring-amber-200">
            <span>
                <span class="block text-sm font-extrabold text-stone-800">Bebas ongkir untuk produk ini</span>
                <span class="mt-0.5 block text-xs font-semibold text-stone-400">Jika keranjang berisi produk lain yang tidak bebas ongkir, ongkir tetap dihitung dari produk tersebut.</span>
            </span>
        </label>
    </div>
</div>

<div
     class="flex flex-col-reverse gap-3 border-t border-stone-100 pt-5 sm:flex-row sm:items-center sm:justify-end">
    <a href="{{ route('admin.products.index') }}"
       class="inline-flex items-center justify-center rounded-xl border border-stone-200 px-4 py-2.5 text-xs font-extrabold text-stone-600 transition hover:bg-stone-50">
        Batal
    </a>
    <button type="submit"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-amber-600">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2"
             stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
        {{ $submitLabel ?? 'Simpan produk' }}
    </button>
</div>

@push('scripts')
    <script>
        function sizeGuideSelector(allTypes, initialTypeId, initialSizes, initialStocks) {
            return {
                allTypes: allTypes,
                selectedTypeId: initialTypeId || '',
                selectedSizes: initialSizes || [],
                sizeStocks: initialStocks || {},
                get currentEntries() {
                    if (!this.selectedTypeId) return [];
                    const type = this.allTypes.find(t => t.id == this.selectedTypeId);
                    return type ? (type.entries || []) : [];
                },
                onTypeChange() {
                    this.selectedSizes = [];
                },
                toggleSize(label) {
                    const idx = this.selectedSizes.indexOf(label);
                    if (idx > -1) {
                        this.selectedSizes.splice(idx, 1);
                    } else {
                        this.selectedSizes.push(label);
                    }
                },
                parseSizesText(text) {
                    this.selectedSizes = text.split(',').map(s => s.trim()).filter(s => s.length > 0);
                }
            };
        }
    </script>
@endpush
