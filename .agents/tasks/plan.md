# Implementation Plan — Size Guide Master (TKT-021 Extension)

**Fitur:** Master Panduan Ukuran (Size Guide) dengan dimensi detail per jenis pakaian,
terintegrasi ke form produk admin dan halaman detail produk publik.

**Stack:** Laravel 13 · PHP 8.4 · PostgreSQL · Tailwind CSS v3 · Alpine.js v3

---

## Keputusan Desain

### 1. Dua tabel baru, bukan kolom JSON di products
`size_guide_types` (master jenis: Baju/Kaos, Celana, Sepatu, dst.) dan `size_guide_entries`
(baris ukuran: 1T → {LD: 52, PB: 40, ...}).  
**Alasan:** memungkinkan sharing master antar produk, mudah di-CRUD lewat admin, query
panduan ukuran di halaman publik cukup satu join tanpa parsing JSON bersarang.

### 2. Dimensi ukuran disimpan sebagai JSONB di PostgreSQL
Kolom `size_guide_entries.measurements` bertipe `jsonb`.  
**Alasan:** Jumlah dimensi berbeda per jenis (baju vs celana vs sepatu). JSONB lebih
fleksibel daripada kolom terpisah dan didukung native oleh PostgreSQL dengan cast `array`
di Laravel. Urutan dimensi ikut field `dimension_labels` di `size_guide_types`.

### 3. Kolom `dimension_labels` di size_guide_types
Array berurutan yang mendefinisikan nama dimensi (misal: `["LD", "PB", "LP"]`).
Disimpan sebagai JSONB, cast `array` di model. Urutan ini menentukan tampilan kolom di tabel panduan ukuran.

### 4. Relasi Product → SizeGuideType (nullable many-to-one)
Produk tetap bisa tanpa panduan ukuran (`size_guide_type_id` nullable).  
**Alasan:** Produk seperti topi/aksesori mungkin tidak perlu size guide terstruktur.

### 5. Field `sizes` di products TETAP dipertahankan
`sizes` (array ukuran tersedia: `['1T','2T']`) tetap ada untuk pilihan ukuran di keranjang.
Size guide hanya menambah dimensi detail, bukan menggantikan field `sizes`.

### 6. CRUD Size Guide Types di admin panel
Route resource terpisah `admin.size-guide-types.*`.  
Tidak digabung dengan kategori agar concern tetap terpisah.

---

## Urutan Implementasi

- [ ] 1. **Migration: buat tabel `size_guide_types`**
      Buat file migration baru. Tabel berisi: `id`, `name` (string 100), `slug` (string unique),
      `dimension_labels` (jsonb — array nama dimensi berurutan, misal `["LD cm","PB cm","LP cm"]`),
      `description` (text nullable), `is_active` (boolean default true), `timestamps`.
      Files: `database/migrations/2026_11_03_000001_create_size_guide_types_table.php`
      Verify: `php artisan migrate` — tidak ada error, tabel terbuat di DB `mamikhevan`.

- [ ] 2. **Migration: buat tabel `size_guide_entries`**
      Tabel berisi: `id`, `size_guide_type_id` (FK → size_guide_types, cascade delete),
      `size_label` (string 20 — misal `1T`, `2T`, `22`), `measurements` (jsonb — key=nama dimensi,
      value=angka/string), `sort_order` (unsignedSmallInteger default 0), `timestamps`.
      Index: `[size_guide_type_id, sort_order]`.
      Files: `database/migrations/2026_11_03_000002_create_size_guide_entries_table.php`
      Verify: `php artisan migrate` — tidak ada error.

- [ ] 3. **Migration: tambah kolom `size_guide_type_id` ke tabel `products`**
      Tambah kolom nullable FK: `size_guide_type_id` (foreignId nullable, constrained ke
      `size_guide_types`, nullOnDelete). Letakkan setelah kolom `sizes`.
      Files: `database/migrations/2026_11_03_000003_add_size_guide_type_id_to_products_table.php`
      Verify: `php artisan migrate` — kolom terbuat, produk lama tidak terpengaruh (nilai null).

- [ ] 4. **Model: SizeGuideType**
      Namespace: `App\Models`. Trait: `HasFactory`, `HasUniqueSlug` (sudah ada di
      `app/Models/Concerns/HasUniqueSlug.php`).
      Fillable: `name`, `slug`, `dimension_labels`, `description`, `is_active`.
      Casts: `dimension_labels` → `array`, `is_active` → `boolean`.
      Relasi: `entries(): HasMany` → `SizeGuideEntry` (ordered by `sort_order`).
      Scope: `scopeActive($query)` → filter `is_active = true`.
      Files: `app/Models/SizeGuideType.php`
      Verify: `php artisan tinker` → `App\Models\SizeGuideType::count()` mengembalikan 0 tanpa error.

- [ ] 5. **Model: SizeGuideEntry**
      Namespace: `App\Models`. Trait: `HasFactory`.
      Fillable: `size_guide_type_id`, `size_label`, `measurements`, `sort_order`.
      Casts: `measurements` → `array`, `sort_order` → `integer`.
      Relasi: `type(): BelongsTo` → `SizeGuideType`.
      Files: `app/Models/SizeGuideEntry.php`
      Verify: `php artisan tinker` → tidak ada error saat di-instantiate.

- [ ] 6. **Update Model: Product**
      Tambahkan `size_guide_type_id` ke `$fillable`.
      Tambahkan cast: `size_guide_type_id` → `integer` (nullable, gunakan cast `'integer'` bukan
      kustom — Laravel handles nullable otomatis).
      Tambahkan relasi: `sizeGuideType(): BelongsTo` → `SizeGuideType`.
      Files: `app/Models/Product.php`
      Verify: `php artisan tinker` → `App\Models\Product::first()->sizeGuideType` tidak throw error.

- [ ] 7. **Seeder: SizeGuideTypeSeeder**
      Seed minimal 4 tipe default:
      - **Baju/Kaos** (slug: `baju-kaos`): dimension_labels `["LD (cm)","PB (cm)","LB (cm)"]`,
        entries: 1T/2T/3T/4T/5T/6T dengan nilai realistis anak.
      - **Celana** (slug: `celana`): dimension_labels `["LP (cm)","PB (cm)","LL (cm)"]`,
        entries: 1T/2T/3T/4T/5T/6T/7T.
      - **Dress/Rok** (slug: `dress-rok`): dimension_labels `["LD (cm)","Pjg Dress (cm)","LP (cm)"]`,
        entries: 2T/3T/4T/5T/6T.
      - **Sepatu** (slug: `sepatu`): dimension_labels `["Panjang Kaki (cm)","No. Sepatu"]`,
        entries: 22/23/24/25/26/27.
      Gunakan `updateOrCreate(['slug' => ...], [...])` supaya idempotent.
      Daftarkan di `DatabaseSeeder::run()` sebelum `ProductSeeder`.
      Files:
      - `database/seeders/SizeGuideTypeSeeder.php`
      - `database/seeders/DatabaseSeeder.php` (tambah panggilan ke SizeGuideTypeSeeder)
      Verify: `php artisan db:seed --class=SizeGuideTypeSeeder` — tidak ada error, 4 tipe + entries masuk DB.

- [ ] 8. **FormRequest: SizeGuideTypeRequest**
      Namespace: `App\Http\Requests\Admin`. Method `authorize()` cek `$this->user()?->is_admin`.
      Rules:
      - `name`: required|string|max:100
      - `slug`: nullable|string|max:120|alpha_dash|unique:size_guide_types,slug (ignore saat edit)
      - `dimension_labels`: required|array|min:1
      - `dimension_labels.*`: required|string|max:30
      - `description`: nullable|string|max:500
      - `is_active`: boolean
      `prepareForValidation()`: auto-slug dari name (sama seperti CategoryRequest), cast is_active boolean.
      Files: `app/Http/Requests/Admin/SizeGuideTypeRequest.php`
      Verify: Buat tipe baru via form admin — validasi error muncul dengan benar saat field kosong.

- [ ] 9. **Controller: SizeGuideTypeController (Admin)**
      Namespace: `App\Http\Controllers\Admin`. Extends `Controller`.
      Methods:
      - `index(): View` — paginate 15, withCount('entries'), search by name via `?q=`
      - `create(): View` — return view `admin.size-guide-types.create`
      - `store(SizeGuideTypeRequest $request): RedirectResponse`
      - `edit(SizeGuideType $sizeGuideType): View` — eager load `entries()->orderBy('sort_order')`
      - `update(SizeGuideTypeRequest $request, SizeGuideType $sizeGuideType): RedirectResponse`
      - `destroy(SizeGuideType $sizeGuideType): RedirectResponse` — cek apakah ada produk terhubung
        (`$sizeGuideType->products()->exists()`), jika ada redirect back with error.
      Perhatian: Method `destroy` harus cek relasi ke products SEBELUM delete untuk cegah orphan.
      Files: `app/Http/Controllers/Admin/SizeGuideTypeController.php`
      Verify: Route `admin.size-guide-types.index` bisa diakses admin tanpa error 500.

- [ ] 10. **Controller: SizeGuideEntryController (Admin)**
       Namespace: `App\Http\Controllers\Admin`. Extends `Controller`.
       Ini adalah sub-resource dari SizeGuideType. Methods:
       - `store(Request $request, SizeGuideType $sizeGuideType): RedirectResponse`
         Validasi: `size_label` required|string|max:20, `measurements` array,
         `measurements.*` nullable|string|max:30, `sort_order` integer|min:0.
         Simpan entry baru, redirect ke `admin.size-guide-types.edit`.
       - `update(Request $request, SizeGuideType $sizeGuideType, SizeGuideEntry $entry): RedirectResponse`
         Validasi sama. Pastikan `$entry->size_guide_type_id === $sizeGuideType->id` (cegah
         akses cross-type). Redirect kembali ke edit page.
       - `destroy(SizeGuideType $sizeGuideType, SizeGuideEntry $entry): RedirectResponse`
         Sama, validasi kepemilikan entry. Redirect ke edit page.
       Files: `app/Http/Controllers/Admin/SizeGuideEntryController.php`
       Verify: Tambah entry baru via form di halaman edit tipe ukuran → entry muncul di tabel.

- [ ] 11. **Routes: tambah ke web.php**
       Di dalam group admin (middleware `['auth','admin']`, prefix `admin`, name `admin.`):
       ```php
       Route::resource('size-guide-types', AdminSizeGuideTypeController::class)->except('show');
       Route::post('size-guide-types/{sizeGuideType}/entries', [AdminSizeGuideEntryController::class, 'store'])
           ->name('size-guide-types.entries.store');
       Route::patch('size-guide-types/{sizeGuideType}/entries/{entry}', [AdminSizeGuideEntryController::class, 'update'])
           ->name('size-guide-types.entries.update');
       Route::delete('size-guide-types/{sizeGuideType}/entries/{entry}', [AdminSizeGuideEntryController::class, 'destroy'])
           ->name('size-guide-types.entries.destroy');
       ```
       Tambahkan `use` statements untuk kedua controller baru di bagian atas `web.php`.
       Files: `routes/web.php`
       Verify: `php artisan route:list --name=admin.size-guide` — semua route tampil.

- [ ] 12. **Views Admin: Size Guide Types (index, create, edit, _form)**
       Struktur: `resources/views/admin/size-guide-types/`
       Ikuti persis pola dari `resources/views/admin/categories/` (layout, heading, breadcrumb, flash).

       **index.blade.php** — tabel list tipe ukuran: nama, jumlah dimensi, jumlah entries, status
       aktif/nonaktif, tombol edit + hapus (hapus pakai form POST method DELETE).

       **create.blade.php** — form wrapper, action `route('admin.size-guide-types.store')`.

       **edit.blade.php** — DUA bagian:
       1. Form edit metadata tipe (nama, dimension_labels, description, is_active).
          Form action: `route('admin.size-guide-types.update', $sizeGuideType)`, method PATCH.
       2. Tabel entries ukuran yang sudah ada + form inline tambah entry baru.
          Setiap baris entry punya form kecil untuk edit (PATCH) dan tombol hapus (DELETE).
          Kolom tabel sesuai `$sizeGuideType->dimension_labels`.

       **_form.blade.php** — form fields metadata saja (untuk create/edit metadata).
       Field `dimension_labels`: tampilkan sebagai daftar input dinamis dengan Alpine.js.
       Alpine data: `{ labels: @js($sizeGuideType?->dimension_labels ?? ['']), ... }`
       Tombol "Tambah dimensi" menambah satu input baru. Tombol "×" hapus dimensi.
       Input dikirim sebagai `dimension_labels[]` (array).

       Files:
       - `resources/views/admin/size-guide-types/index.blade.php`
       - `resources/views/admin/size-guide-types/create.blade.php`
       - `resources/views/admin/size-guide-types/edit.blade.php`
       - `resources/views/admin/size-guide-types/_form.blade.php`
       Verify: Buka `/admin/size-guide-types` — halaman tampil tanpa error. Tambah tipe baru dengan
       2 dimensi → tersimpan. Buka edit → tabel entries kosong tampil.

- [ ] 13. **Update: Admin Layout — tambah menu Size Guide**
       Di `resources/views/layouts/admin.blade.php`, tambah item ke array `$adminNav`:
       ```php
       ['label' => 'Panduan Ukuran', 'route' => 'admin.size-guide-types.index',
        'active' => 'admin.size-guide-types.*', 'icon' => 'size-guide', 'badge' => null],
       ```
       Posisi: setelah menu Kategori, sebelum Pesanan (logis karena berkaitan dengan produk).
       Di blok `@switch($item['icon'])`, tambah `@case('size-guide')` dengan SVG ikon ruler/measure
       (pakai outline icon `M9 4.5v15m6-15v15M3.75 9h16.5M3.75 15h16.5` — table/grid icon).
       Files: `resources/views/layouts/admin.blade.php`
       Verify: Sidebar admin menampilkan menu "Panduan Ukuran" dan active state bekerja.

- [ ] 14. **Update: ProductRequest — tambah validasi `size_guide_type_id`**
       Tambah rule baru ke method `rules()`:
       ```php
       'size_guide_type_id' => ['nullable', 'integer', 'exists:size_guide_types,id'],
       ```
       Files: `app/Http/Requests/Admin/ProductRequest.php`
       Verify: Submit form produk tanpa size_guide_type_id → tidak ada error validasi (nullable).

- [ ] 15. **Update: ProductController — pass `$sizeGuideTypes` ke view**
       Di method `create()` dan `edit()`, tambah query:
       ```php
       $sizeGuideTypes = SizeGuideType::query()->active()->orderBy('name')->get();
       ```
       Sertakan di `compact(...)` untuk kedua method.
       Import: `use App\Models\SizeGuideType;` di bagian atas controller.
       Files: `app/Http/Controllers/Admin/ProductController.php`
       Verify: View produk create/edit bisa menerima variabel `$sizeGuideTypes` tanpa error.

- [ ] 16. **Update: Product Admin Form — dropdown size_guide_type_id**
       Di `resources/views/admin/products/_form.blade.php`, **ganti** field `sizes` (text input)
       dengan dua bagian baru, letakkan di area yang sama (setelah field stok):

       **Bagian A — dropdown Jenis Panduan Ukuran:**
       ```html
       <select name="size_guide_type_id" id="size_guide_type_id" x-model="selectedTypeId"
               @change="fetchEntries()">
           <option value="">Tanpa panduan ukuran</option>
           @foreach ($sizeGuideTypes as $type)
               <option value="{{ $type->id }}" ...>{{ $type->name }}</option>
           @endforeach
       </select>
       ```
       Pilihan default: nilai `old('size_guide_type_id', $product?->size_guide_type_id)`.

       **Bagian B — tag ukuran tersedia (sizes[]):**
       Tetap ada field `sizes` tapi sekarang berupa tag/checkbox interaktif dengan Alpine.js.
       Jika ada size_guide_type_id dipilih, fetch entries via AJAX dan tampilkan sebagai
       checkbox tags. Jika tidak ada tipe dipilih, fallback ke input teks bebas (opsional).

       **Alpine.js component** `x-data="sizeGuideSelector({{ ... }})"`:
       - `selectedTypeId`: id tipe yang dipilih
       - `availableSizes`: array dari server
       - `selectedSizes`: array ukuran yang dipilih (dari `old()` atau `$product->sizes`)
       - `fetchEntries()`: `fetch('/api/size-guide-types/' + id + '/entries')` → isi `availableSizes`

       **GOTCHA:** Alpine.js `fetch` ke route API — perlu route publik baru (lihat item 17).

       Files: `resources/views/admin/products/_form.blade.php`
       Verify: Pilih tipe "Baju/Kaos" di dropdown → ukuran (1T, 2T, dst.) muncul sebagai checkbox.
       Centang beberapa → simpan produk → `sizes` tersimpan sebagai JSON array.

- [ ] 17. **Route API: endpoint publik untuk entries per tipe**
       Tambah di `routes/web.php` (di luar grup admin, bebas akses):
       ```php
       Route::get('/api/size-guide-types/{sizeGuideType}/entries',
           [SizeGuideTypeApiController::class, 'entries'])
           ->name('api.size-guide-types.entries');
       ```
       Buat controller ringkas:
       `App\Http\Controllers\SizeGuideTypeApiController` dengan method `entries(SizeGuideType $sizeGuideType)`.
       Return: `response()->json($sizeGuideType->entries()->orderBy('sort_order')->get(['size_label','measurements']))`.
       Tidak perlu auth — data ini tidak sensitif, hanya referensi ukuran.
       Files:
       - `app/Http/Controllers/SizeGuideTypeApiController.php`
       - `routes/web.php`
       Verify: `GET /api/size-guide-types/1/entries` → JSON array entries.

- [ ] 18. **Update: halaman detail produk publik — tampilkan tabel panduan ukuran**
       Di `resources/views/products/show.blade.php`, setelah form tambah ke keranjang
       (setelah section "Tanya via WA"), tambah section baru kondisional:
       ```blade
       @if ($product->sizeGuideType && $product->sizeGuideType->entries->isNotEmpty())
           <div class="mt-6 ...">
               <h3>Panduan Ukuran — {{ $product->sizeGuideType->name }}</h3>
               <table>
                   <thead>
                       <tr>
                           <th>Ukuran</th>
                           @foreach ($product->sizeGuideType->dimension_labels as $label)
                               <th>{{ $label }}</th>
                           @endforeach
                       </tr>
                   </thead>
                   <tbody>
                       @foreach ($product->sizeGuideType->entries as $entry)
                           <tr class="{{ ... baris ukuran yang sedang dipilih user di-highlight ... }}">
                               <td>{{ $entry->size_label }}</td>
                               @foreach ($product->sizeGuideType->dimension_labels as $label)
                                   <td>{{ $entry->measurements[$label] ?? '—' }}</td>
                               @endforeach
                           </tr>
                       @endforeach
                   </tbody>
               </table>
           </div>
       @endif
       ```
       Highlight baris: gunakan Alpine.js `x-bind:class="{ 'bg-rose-50': selectedSize === entry.size_label }"`.
       Perlu akses `selectedSize` dari Alpine.js scope form di atasnya (gunakan `$store` Alpine atau
       letakkan dalam satu `x-data` wrapper yang sama).

       **GOTCHA:** `$product->sizeGuideType->entries` harus di-eager-load di controller publik
       (`ProductController@show`). Perlu update:
       ```php
       $product->load('sizeGuideType.entries');
       ```
       atau ganti query `Product::with(['category','sizeGuideType.entries'])`.

       Files:
       - `resources/views/products/show.blade.php`
       - `app/Http/Controllers/ProductController.php` (method `show`)
       Verify: Buka halaman detail produk yang memiliki size guide → tabel dimensi tampil.
       Pilih ukuran di form → baris yang sesuai di tabel ter-highlight.

- [ ] 19. **Update: tiketprogres.md — tandai progres dan tambah entri TKT**
       Tambah tiket baru TKT-021b di bagian Fase 4 untuk Size Guide Master.
       Update tabel ringkasan status.
       Files: `tiketprogres.md`
       Verify: File terbuka tanpa error, status baru terefleksi.

---

## Potensi Gotcha & Catatan Penting

### PostgreSQL & JSONB
- Kolom `dimension_labels` dan `measurements` harus type `jsonb` di migration, bukan `json`.
  PostgreSQL memiliki kedua tipe — `jsonb` lebih cepat untuk indexing dan komparasi.
  Di Blueprint: `$table->jsonb('dimension_labels')`.
  Cast di model: `'dimension_labels' => 'array'` — Laravel handles jsonb ↔ PHP array otomatis.
- Saat akses `$entry->measurements[$label]`, pastikan key di PHP array cocok persis dengan
  string di `dimension_labels`. Konsisten gunakan string yang sama (contoh: `"LD (cm)"` harus
  persis sama di keduanya).

### Alpine.js Fetch di Form Admin
- `fetch()` native JS bekerja di Alpine.js v3. Tidak perlu Axios.
- CSRF tidak diperlukan untuk GET request, jadi endpoint entries bisa tanpa `@csrf`.
- Saat old() ada (form validation gagal), `selectedTypeId` harus di-init dari PHP:
  `x-init="selectedTypeId = {{ old('size_guide_type_id', $product?->size_guide_type_id ?? 'null') }};
  if (selectedTypeId) fetchEntries();"` — ini memastikan checkbox ukuran muncul kembali
  setelah form gagal validasi.

### sizes[] sebagai Checkbox vs Text Input
- Form sekarang submit `sizes[]` (multiple checkbox, nilai string masing-masing).
  `ProductRequest::prepareForValidation()` sudah handle jika `sizes` berupa array langsung —
  tidak perlu ubah logika explode koma. Hanya hapus bagian `is_string($sizes)` check atau biarkan
  keduanya (text input fallback jika tidak ada tipe dipilih).

### Scope Alpine.js — Satu x-data Wrapper
- Di `show.blade.php`, form tambah keranjang dan tabel size guide harus dalam `x-data` yang sama
  agar `selectedSize` bisa dishare. Saat ini form keranjang sudah punya `x-data="{ qty: 1, max: ... }"`.
  Gabungkan: `x-data="{ qty: 1, max: ..., selectedSize: '' }"` di wrapper luar.
  Setiap radio button size: `x-model="selectedSize"`.

### Urutan Migrasi
File migration harus timestamp lebih baru dari migrasi products terakhir
(`2026_11_02_000001_add_cost_price_to_products_table.php`) agar FK constraint
`size_guide_type_id` tidak gagal karena tabel `size_guide_types` belum ada.
Urutan yang benar:
1. `2026_11_03_000001_create_size_guide_types_table.php`
2. `2026_11_03_000002_create_size_guide_entries_table.php`
3. `2026_11_03_000003_add_size_guide_type_id_to_products_table.php`

### SizeGuideType::destroy — Cek Relasi Produk
Controller harus cek:
```php
if ($sizeGuideType->products()->exists()) {
    return back()->with('error', 'Tidak bisa dihapus: ada produk yang menggunakan panduan ukuran ini.');
}
```
Tambahkan relasi `products(): HasMany` ke model `SizeGuideType` untuk query ini.

### HasUniqueSlug Trait
Trait ini sudah ada di `app/Models/Concerns/HasUniqueSlug.php` dan dipakai `Product` & `Category`.
`SizeGuideType` bisa pakai trait yang sama. Pastikan model memiliki kolom `slug` dan trait
digunakan di class.

---

## Ringkasan File yang Dibuat/Dimodifikasi

| File | Aksi |
|------|------|
| `database/migrations/2026_11_03_000001_create_size_guide_types_table.php` | BUAT |
| `database/migrations/2026_11_03_000002_create_size_guide_entries_table.php` | BUAT |
| `database/migrations/2026_11_03_000003_add_size_guide_type_id_to_products_table.php` | BUAT |
| `app/Models/SizeGuideType.php` | BUAT |
| `app/Models/SizeGuideEntry.php` | BUAT |
| `app/Models/Product.php` | MODIFIKASI (fillable + cast + relasi) |
| `database/seeders/SizeGuideTypeSeeder.php` | BUAT |
| `database/seeders/DatabaseSeeder.php` | MODIFIKASI (tambah seeder call) |
| `app/Http/Requests/Admin/SizeGuideTypeRequest.php` | BUAT |
| `app/Http/Controllers/Admin/SizeGuideTypeController.php` | BUAT |
| `app/Http/Controllers/Admin/SizeGuideEntryController.php` | BUAT |
| `app/Http/Controllers/SizeGuideTypeApiController.php` | BUAT |
| `routes/web.php` | MODIFIKASI (2 route baru: resource + API) |
| `resources/views/admin/size-guide-types/index.blade.php` | BUAT |
| `resources/views/admin/size-guide-types/create.blade.php` | BUAT |
| `resources/views/admin/size-guide-types/edit.blade.php` | BUAT |
| `resources/views/admin/size-guide-types/_form.blade.php` | BUAT |
| `resources/views/layouts/admin.blade.php` | MODIFIKASI (tambah menu nav) |
| `app/Http/Requests/Admin/ProductRequest.php` | MODIFIKASI (tambah rule) |
| `app/Http/Controllers/Admin/ProductController.php` | MODIFIKASI (tambah sizeGuideTypes ke view) |
| `resources/views/admin/products/_form.blade.php` | MODIFIKASI (sizes → checkbox + dropdown) |
| `resources/views/products/show.blade.php` | MODIFIKASI (tambah tabel panduan ukuran) |
| `app/Http/Controllers/ProductController.php` | MODIFIKASI (eager load sizeGuideType.entries) |
| `tiketprogres.md` | MODIFIKASI (update status) |

**Total: 7 file baru, 8 file dimodifikasi**
