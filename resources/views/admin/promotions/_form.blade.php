@php
    $promotion = $promotion ?? null;
    $selectedType = old('type', $promotion?->type ?? 'price');
    $selectedTarget = old('target_type', $promotion?->target_type ?? 'all');
    $selectedTiers = old('tiers', $promotion?->tiers ?? []);
    $selectedTiers = is_array($selectedTiers) ? array_values($selectedTiers) : [];
@endphp

@if ($errors->any())
    <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <p class="font-extrabold">Periksa kembali data promo:</p>
        <ul class="mt-2 list-inside list-disc space-y-1 text-xs">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div x-data="{
        type: {{ \Illuminate\Support\Js::from($selectedType) }},
        target: {{ \Illuminate\Support\Js::from($selectedTarget) }},
        tiers: {{ \Illuminate\Support\Js::from($selectedTiers) }},
        addTier() {
            this.tiers.push({ min_quantity: '', discount_kind: 'percent', discount_value: '', maximum_discount: '' });
        },
        removeTier(index) {
            this.tiers.splice(index, 1);
        },
    }" class="space-y-5">
    <div class="grid gap-5 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:grid-cols-2">
        <div>
            <label for="name" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Nama promo *</label>
            <input id="name" name="name" type="text" required maxlength="120" value="{{ old('name', $promotion?->name) }}" placeholder="Diskon akhir pekan"
                   class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
        </div>
        <div>
            <label for="event_name" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Nama event / hari sale</label>
            <input id="event_name" name="event_name" type="text" maxlength="120" value="{{ old('event_name', $promotion?->event_name) }}" placeholder="Harbolnas 12.12"
                   class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
        </div>
        <div>
            <label for="voucher_code" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Kode voucher (opsional)</label>
            <input id="voucher_code" name="voucher_code" type="text" maxlength="40" value="{{ old('voucher_code', $promotion?->voucher_code) }}" placeholder="HEMAT10"
                   class="mt-2 w-full rounded-xl border-stone-200 text-sm uppercase focus:border-amber-300 focus:ring-amber-200">
            <p class="mt-1 text-xs text-stone-500">Kosongkan bila promo berlaku otomatis. Jika diisi, promo hanya aktif saat pelanggan memasukkan kode ini.</p>
            <p x-show="type === 'giveaway'" x-cloak class="mt-1 text-xs font-bold text-amber-700">Kode voucher wajib untuk promo giveaway.</p>
        </div>
        <div>
            <label for="starts_at" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Mulai (WIB) *</label>
            <input id="starts_at" name="starts_at" type="datetime-local" required value="{{ old('starts_at', $promotion?->starts_at?->timezone(\App\Models\Promotion::TIMEZONE)->format('Y-m-d\TH:i')) }}"
                   class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
        </div>
        <div>
            <label for="ends_at" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Selesai (WIB) *</label>
            <input id="ends_at" name="ends_at" type="datetime-local" required value="{{ old('ends_at', $promotion?->ends_at?->timezone(\App\Models\Promotion::TIMEZONE)->format('Y-m-d\TH:i')) }}"
                   class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
        </div>
    </div>

    <div class="grid gap-5 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:grid-cols-2">
        <div>
            <label for="type" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Jenis promo *</label>
            <select id="type" name="type" x-model="type" class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                <option value="price">Diskon harga produk</option>
                <option value="buy_get">Beli item, gratis item</option>
                <option value="shipping_discount">Diskon ongkir</option>
                <option value="shipping_subsidy">Subsidi ongkir tetap</option>
                <option value="quantity_tier">Diskon bertingkat jumlah item</option>
                <option value="giveaway">Giveaway / hadiah gratis</option>
            </select>
        </div>
        <div x-show="type !== 'giveaway'">
            <label for="target_type" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Sasaran promo *</label>
            <select id="target_type" name="target_type" x-model="target" class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                <option value="all">Semua produk dalam pesanan</option>
                <option value="product">Produk tertentu</option>
                <option value="category">Kategori tertentu</option>
            </select>
        </div>
        <div x-show="target === 'product'" x-cloak>
            <label for="product_id" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Produk sasaran</label>
            <select id="product_id" name="product_id" class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                <option value="">Pilih produk</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected((string) old('product_id', $promotion?->product_id) === (string) $product->id)>{{ $product->name }}</option>
                @endforeach
            </select>
        </div>
        <div x-show="target === 'category'" x-cloak>
            <label for="category_id" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Kategori sasaran</label>
            <select id="category_id" name="category_id" class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                <option value="">Pilih kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $promotion?->category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="minimum_subtotal" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Minimal belanja (Rp)</label>
            <input id="minimum_subtotal" name="minimum_subtotal" type="number" min="0" value="{{ old('minimum_subtotal', $promotion?->minimum_subtotal ?? 0) }}"
                   class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
        </div>
    </div>

    <div x-show="type === 'price' || type === 'shipping_discount' || type === 'shipping_subsidy'" class="grid gap-5 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:grid-cols-2">
        <div x-show="type !== 'shipping_subsidy'" x-cloak>
            <label for="discount_kind" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Bentuk diskon</label>
            <select id="discount_kind" name="discount_kind" class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                <option value="percent" @selected(old('discount_kind', $promotion?->discount_kind ?? 'percent') === 'percent')>Persentase (%)</option>
                <option value="fixed" @selected(old('discount_kind', $promotion?->discount_kind) === 'fixed')>Nominal tetap (Rp)</option>
            </select>
        </div>
        <div>
            <label for="discount_value" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Nilai diskon / subsidi *</label>
            <input id="discount_value" name="discount_value" type="number" min="1" value="{{ old('discount_value', $promotion?->discount_value) }}" placeholder="10 atau 20000"
                   class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
        </div>
        <div x-show="type !== 'shipping_subsidy'" x-cloak>
            <label for="maximum_discount" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Batas diskon (Rp, opsional)</label>
            <input id="maximum_discount" name="maximum_discount" type="number" min="1" value="{{ old('maximum_discount', $promotion?->maximum_discount) }}"
                   class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
        </div>
    </div>

    <div x-show="type === 'buy_get' || type === 'giveaway'" x-cloak class="grid gap-5 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:grid-cols-2">
        <p x-show="type === 'giveaway'" class="text-xs font-semibold leading-5 text-amber-700 sm:col-span-2">Hadiah giveaway diberikan gratis tanpa syarat pembelian. Pelanggan hanya membayar ongkos kirim (dan item lain di keranjang bila ada).</p>
        <div x-show="type === 'buy_get'">
            <label for="buy_quantity" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Jumlah item yang dibeli *</label>
            <input id="buy_quantity" name="buy_quantity" type="number" min="1" max="99" value="{{ old('buy_quantity', $promotion?->buy_quantity) }}"
                   class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
        </div>
        <div>
            <label for="gift_quantity" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Jumlah item gratis *</label>
            <input id="gift_quantity" name="gift_quantity" type="number" min="1" max="99" value="{{ old('gift_quantity', $promotion?->gift_quantity) }}"
                   class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
        </div>
        <div>
            <label for="gift_product_id" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Produk hadiah *</label>
            <select id="gift_product_id" name="gift_product_id" @change="$refs.giftPrice.value = $event.target.selectedOptions[0].dataset.price || ''" class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                <option value="">Pilih produk hadiah</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" @selected((string) old('gift_product_id', $promotion?->gift_product_id) === (string) $product->id)>{{ $product->name }} — Rp{{ number_format($product->price, 0, ',', '.') }} ({{ implode(', ', $product->sizeList()) ?: 'tanpa ukuran' }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="gift_size" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Ukuran hadiah</label>
            <input id="gift_size" name="gift_size" type="text" maxlength="20" value="{{ old('gift_size', $promotion?->gift_size) }}" placeholder="Contoh: 2T"
                   class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
            <p class="mt-1 text-xs text-stone-500">Isi salah satu ukuran produk hadiah jika produk memiliki ukuran.</p>
        </div>
        <div>
            <label for="gift_unit_price" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Harga normal per hadiah (Rp) *</label>
            <input id="gift_unit_price" name="gift_unit_price" x-ref="giftPrice" type="number" min="1" value="{{ old('gift_unit_price', $promotion?->gift_unit_price) }}"
                   class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
            <p class="mt-1 text-xs text-stone-500">Nilai ini ditampilkan sebagai harga normal. Pelanggan membayar Rp0 untuk item hadiah.</p>
        </div>
    </div>

    <div x-show="type === 'quantity_tier'" x-cloak class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-extrabold text-stone-800">Tingkat diskon berdasarkan jumlah item</h3>
                <p class="mt-1 text-xs text-stone-500">Diskon diambil dari tingkat tertinggi yang jumlah itemnya terpenuhi. Tingkat dihitung dari jumlah item yang sesuai sasaran promo.</p>
            </div>
            <button type="button" @click="addTier()" class="rounded-xl border border-amber-200 px-3 py-2 text-xs font-extrabold text-amber-700 hover:bg-amber-50">Tambah tingkat</button>
        </div>

        <div class="mt-4 space-y-3">
            <template x-for="(tier, index) in tiers" :key="index">
                <div class="grid gap-3 rounded-2xl border border-stone-200 bg-stone-50/60 p-4 sm:grid-cols-4">
                    <div>
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-stone-500">Minimal item *</label>
                        <input type="number" min="1" max="999" :name="`tiers[${index}][min_quantity]`" x-model="tier.min_quantity"
                               class="mt-1 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                    </div>
                    <div>
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-stone-500">Bentuk diskon *</label>
                        <select :name="`tiers[${index}][discount_kind]`" x-model="tier.discount_kind"
                                class="mt-1 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                            <option value="percent">Persentase (%)</option>
                            <option value="fixed">Nominal tetap (Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-stone-500">Nilai diskon *</label>
                        <input type="number" min="1" :name="`tiers[${index}][discount_value]`" x-model="tier.discount_value"
                               class="mt-1 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                    </div>
                    <div>
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-stone-500">Batas diskon (Rp)</label>
                        <div class="mt-1 flex gap-2">
                            <input type="number" min="1" :name="`tiers[${index}][maximum_discount]`" x-model="tier.maximum_discount" placeholder="Opsional"
                                   class="w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                            <button type="button" @click="removeTier(index)" class="rounded-xl border border-red-200 px-2.5 text-xs font-extrabold text-red-700 hover:bg-red-50" title="Hapus tingkat">×</button>
                        </div>
                    </div>
                </div>
            </template>

            <p x-show="tiers.length === 0" class="rounded-2xl border border-dashed border-stone-300 px-4 py-6 text-center text-xs font-semibold text-stone-500">
                Belum ada tingkat diskon. Tambahkan minimal satu tingkat.
            </p>
        </div>
    </div>

    <div class="flex flex-col gap-4 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <label class="flex items-center gap-3 text-sm font-bold text-stone-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $promotion?->is_active ?? true)) class="rounded border-stone-300 text-amber-600 focus:ring-amber-200">
            Promo aktif sesuai jadwal
        </label>
        <div class="flex gap-2">
            <a href="{{ route('admin.promotions.index') }}" class="rounded-xl border border-stone-200 px-4 py-2.5 text-xs font-extrabold text-stone-600">Batal</a>
            <button type="submit" class="rounded-xl bg-amber-500 px-5 py-2.5 text-xs font-extrabold text-white hover:bg-amber-600">Simpan promo</button>
        </div>
    </div>
</div>
