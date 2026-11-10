# Size Guide Master — Implementasi Sistem Panduan Ukuran Produk Anak

Fitur ini menambahkan sistem panduan ukuran bertingkat (Size Guide Master) ke katalog mamikhevanshop. Tiga migrasi baru membuat tabel `size_guide_types`, `size_guide_entries`, dan kolom FK `size_guide_type_id` di `products`. Dua model baru dengan relasi lengkap, empat controller views, sebuah seeder dengan 48 baris data (4 tipe × 12 ukuran), dan integrasi ke form produk admin maupun halaman detail produk publik diimplementasikan secara keseluruhan dalam satu pass.

Watch for: (1) **slug bypass di `store()`** — `$request->input('slug')` dibaca langsung bukan dari `$data` validated, sehingga slug bisa diisi bebas jika validasi gagal di path lain — *confirmed*. (2) **`sizes_text` field tidak ditangani** oleh `ProductRequest`, artinya entri ukuran teks-bebas hanya terparsing lewat Alpine.js hidden inputs tapi tidak ada fallback server-side jika JS nonaktif — *confirmed*. (3) **N+1 laten di `getEntriesForLabels`** dipanggil dari view dengan query tambahan setelah eager load — *confirmed*.

**Verdict**: NEEDS_CHANGES

---

## High-level view

Skema tiga-tabel dirancang dengan baik: `size_guide_types` menyimpan konfigurasi medan pengukuran sebagai JSON, `size_guide_entries` menyimpan nilai per-ukuran per-tipe, dan `products` mendapat FK nullable sehingga produk lama tidak terdampak. Cascade delete dari entries ke type sudah dikonfigurasikan di tingkat database (FK `cascadeOnDelete`), dan `nullOnDelete` di sisi products juga tepat — menghapus tipe guide tidak menghapus produk.

`MEASUREMENT_LABELS` sebagai konstanta di `SizeGuideEntry` adalah keputusan desain yang solid: sumber kebenaran tunggal untuk label bahasa Indonesia, dipakai konsisten di form admin, halaman publik, dan validasi request. Set enam medan (`chest_cm`, `waist_cm`, `hip_cm`, `thigh_cm`, `length_cm`, `sleeve_cm`) sudah cukup untuk pakaian anak usia 0–12 tahun.

Controller `SizeGuideController` menjalankan pola delete-then-insert saat update — mudah dipahami dan aman dari stale entries, tapi memakai `SizeGuideEntry::insert()` (mass insert tanpa model events). Di halaman produk publik, controller sudah eager load `sizeGuideType.entries`, namun view memanggil `getEntriesForLabels()` sebagai query tambahan di atas relasi yang sudah di-load — ini N+1 yang bisa dihindari.

Slug di `SizeGuideRequest` di-generate otomatis via `prepareForValidation` jika field kosong, namun `store()` membaca `$request->input('slug')` langsung ketimbang `$data['slug']`. Karena `prepareForValidation` sudah menyuntikkan slug ke dalam request payload sebelum validasi berjalan, pengambilan via `$request->input` akan mendapat nilai yang benar dalam kondisi normal — tapi pola ini tidak konsisten dengan pengambilan field lain dari `$data` dan membuka celah jika routing berubah.

Form produk memiliki dua jalur pengiriman ukuran: saat size guide type dipilih, checkbox Alpine.js menghasilkan `sizes[]` hidden inputs; saat tidak ada type yang dipilih, kolom teks `sizes_text` diparse Alpine dan mengisi hidden inputs juga. Jalur teks-bebas ini bergantung penuh pada JavaScript — jika JS nonaktif, `sizes_text` terkirim tapi `ProductRequest` tidak memiliki logika untuk mem-parse-nya.

---

<details>
<summary>Issues (3)</summary>

1. **Slug bypass di `store()` dan `update()`** — `store()` mengambil slug via `$request->input('slug')` bukan dari `$data` (hasil `$request->validated()`). Ganti ke `$data['slug'] ?? null` agar konsisten. Di `update()`, pola `$request->input('slug') ?: $sizeGuide->slug` sama kasusnya — gunakan `$data['slug'] ?? $sizeGuide->slug`.

2. **N+1 laten di `getEntriesForLabels()`** — `ProductController::show()` sudah eager load `sizeGuideType.entries`, tapi view memanggil `$guideType->getEntriesForLabels($product->sizeList())` yang menjalankan query SQL baru. Filter per-label seharusnya dilakukan di PHP dari koleksi yang sudah di-load: `$guideType->entries->whereIn('label', $product->sizeList())`. Pada traffic tinggi ini menjadi query tambahan untuk setiap page view produk yang punya size guide.

3. **`sizes_text` tidak ada fallback server-side** — Ketika `selectedTypeId` kosong, form mengirim `name="sizes_text"` (teks) bukan `name="sizes[]"`. Alpine.js memang mengisi hidden `sizes[]` dari parse teks tersebut, tapi `ProductRequest::prepareForValidation()` hanya menangani `sizes` array. Jika browser tidak menjalankan JS, produk disimpan tanpa ukuran tanpa pesan error. Tambahkan fallback di `prepareForValidation`: jika `sizes` kosong dan `sizes_text` terisi, parse dan masukkan ke `sizes`.

</details>

<details>
<summary>Details</summary>

### Migrasi: urutan dan tipe data

Urutan file (`000007` → `000008` → `000009`) benar: `size_guide_types` dibuat sebelum `size_guide_entries` yang memegang FK-nya, dan sebelum kolom FK di `products`. Semua kolom measurement di `size_guide_entries` bertipe `string(20)` — ini tepat untuk PostgreSQL karena nilainya berformat range seperti `"10-12 kg"` atau `"76-84 cm"`, bukan angka presisi. `measurement_fields` bertipe `json` di PostgreSQL menggunakan jsonb di balik layar ketika tipe kolom `json` dipakai via Eloquent cast — kompatibel.

`nullOnDelete` di FK `products.size_guide_type_id` tepat: menghapus tipe guide tidak merusak produk yang sudah ada, hanya melepas referensinya.

### Slug bypass di store() dan update()

Di `SizeGuideController::store()`:

```php
$sizeGuide = SizeGuideType::create([
    'name'  => $data['name'],
    'slug'  => $request->input('slug'),  // ← bukan dari $data
    ...
]);
```

`SizeGuideRequest::prepareForValidation()` menyuntikkan slug ke dalam request input sebelum validasi, jadi `$request->input('slug')` akan memiliki nilai yang benar dalam kondisi normal. Namun slug ada dalam `$rules` dan akan masuk ke `$data` dari `$request->validated()` — pengambilan via `$request->input` melewati envelope validated dan tidak konsisten. Ganti ke `$data['slug'] ?? null`.

Di `update()`, pola `$request->input('slug') ?: $sizeGuide->slug` bermaksud mempertahankan slug lama jika field dikosongkan. Tapi karena `prepareForValidation` sudah men-generate slug dari nama jika field kosong, blank slug hampir tidak mungkin mencapai controller — kecuali admin secara eksplisit mengetik nama yang menghasilkan slug kosong (karakter non-latin semua). Pola fallback ke `$sizeGuide->slug` tetap aman, tapi konsisten lebih baik dengan `$data['slug'] ?? $sizeGuide->slug`.

### N+1 laten di halaman detail produk

`ProductController::show()` eager load dengan benar:

```php
$product->load(['category', 'sizeGuideType.entries']);
```

Tapi view kemudian memanggil:

```php
$guideEntries = $guideType->getEntriesForLabels($product->sizeList());
```

`getEntriesForLabels()` adalah:

```php
public function getEntriesForLabels(array $labels)
{
    return $this->entries()
        ->whereIn('label', $labels)
        ->orderBy('sort_order')
        ->get();
}
```

Ini memanggil `$this->entries()` sebagai *query builder* (bukan `$this->entries` sebagai koleksi), menghasilkan query SQL baru meski entries sudah di-load. Solusi: filter koleksi di PHP.

```php
// Di view atau di SizeGuideType:
$guideEntries = $guideType->entries->whereIn('label', $product->sizeList())->sortBy('sort_order');
```

Atau tambahkan helper alternatif di model yang bekerja pada koleksi yang sudah di-load.

### `sizes_text` tanpa fallback server-side

Di `_form.blade.php` produk, blok `x-show="!selectedTypeId"` merender:

```html
<input type="text" name="sizes_text" ...>
<template x-for="sz in selectedSizes" :key="sz">
    <input type="hidden" name="sizes[]" :value="sz">
</template>
```

Alpine.js memastikan setiap perubahan teks langsung mengisi hidden `sizes[]`. Tapi jika JS dinonaktifkan, hanya `sizes_text` yang terkirim. `ProductRequest::prepareForValidation()` tidak pernah melihat `sizes_text`:

```php
$sizes = $this->input('sizes');  // akan null jika hanya sizes_text yang terkirim
```

Produk akan tersimpan dengan `sizes = null` tanpa pesan error ke user. Tambahkan ke `prepareForValidation`:

```php
if (blank($sizes) && filled($this->input('sizes_text'))) {
    $sizes = collect(explode(',', $this->input('sizes_text')))
        ->map(fn ($s) => trim($s))
        ->filter()
        ->unique()
        ->values()
        ->all();
}
```

### Form Alpine.js dan name attributes

Di `_form.blade.php` size guide, checkbox measurement fields menggunakan `x-model="selectedFields"` — ini menjaga state Alpine sinkron. Tapi untuk submission HTML, checkbox hanya mengirim nilai jika `checked`. Karena `x-model` dan `name="measurement_fields[]"` bekerja bersama (Alpine mengubah checked state, browser mengirim berdasarkan checked), ini benar.

Input entries menggunakan `:name="\`entries[${index}][label]\`"` dengan template literals Alpine — menghasilkan name attributes yang benar saat form disubmit. Validasi backend di `SizeGuideRequest` dengan pola `entries.*.label` akan menangkap ini dengan benar.

### Test coverage

Tidak ada test untuk fitur ini. Kasus yang paling berisiko tidak ter-cover: (a) store tanpa entries (harus gagal validasi), (b) update dengan measurement_fields yang dikurangi — apakah entries lama yang punya nilai untuk field yang dihapus masih aman tersimpan (ya, karena kolom tetap ada di DB tapi tidak ditampilkan), (c) produk dengan size guide yang di-delete (FK null, produk tetap aktif tapi tanpa panduan ukuran).

</details>

---

<details>
<summary>File map</summary>

| File | Perubahan |
|------|-----------|
| `database/migrations/2026_01_01_000007_create_size_guide_types_table.php` | Tabel baru: name, slug, description, measurement_fields (json) |
| `database/migrations/2026_01_01_000008_create_size_guide_entries_table.php` | Tabel baru: FK ke size_guide_types (cascade), kolom measurement semua string nullable |
| `database/migrations/2026_01_01_000009_add_size_guide_type_id_to_products_table.php` | Tambah FK nullable ke products, nullOnDelete |
| `app/Models/SizeGuideType.php` | Model baru: fillable, cast json, relasi entries + products, helper getEntriesForLabels |
| `app/Models/SizeGuideEntry.php` | Model baru: MEASUREMENT_LABELS constant, fillable, relasi ke type |
| `app/Models/Product.php` | Tambah size_guide_type_id ke fillable dan casts, tambah relasi sizeGuideType |
| `app/Http/Controllers/Admin/SizeGuideController.php` | CRUD controller: index/create/store/edit/update/destroy + syncEntries helper |
| `app/Http/Controllers/Admin/ProductController.php` | create/edit sekarang load sizeGuideTypes dengan entries |
| `app/Http/Controllers/ProductController.php` | show() eager load sizeGuideType.entries |
| `app/Http/Requests/Admin/SizeGuideRequest.php` | Form request: validasi name/slug unique, measurement_fields in-array, entries nested |
| `app/Http/Requests/Admin/ProductRequest.php` | Tambah size_guide_type_id ke rules dan prepareForValidation |
| `database/seeders/SizeGuideSeeder.php` | 4 tipe × 12 ukuran: Atasan/Kaos, Celana/Bawahan, Dress/Rok, Jaket/Outer |
| `database/seeders/DatabaseSeeder.php` | SizeGuideSeeder didaftarkan sebelum ProductSeeder |
| `resources/views/admin/size-guides/index.blade.php` | Listing tipe dengan badge jumlah entries dan measurement field tags |
| `resources/views/admin/size-guides/create.blade.php` | Wrapper form create |
| `resources/views/admin/size-guides/edit.blade.php` | Wrapper form edit dengan load entries |
| `resources/views/admin/size-guides/_form.blade.php` | Form Alpine.js: checkbox measurement fields, tabel entri dinamis |
| `resources/views/admin/products/_form.blade.php` | Tambah size guide type selector + size checkbox/teks bebas dengan Alpine |
| `resources/views/products/show.blade.php` | Tabel panduan ukuran kondisional, kolom dinamis dari measurement_fields |
| `resources/views/layouts/admin.blade.php` | Menu "Panduan Ukuran" ditambah setelah "Kategori" |
| `routes/web.php` | `Route::resource('size-guides', ...)` dengan except('show') di group admin |

</details>
