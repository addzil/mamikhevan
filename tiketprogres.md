# TIKET PROGRES PENGEMBANGAN WEBSITE KATALOG MAMIKHEVANSHOP

**Project:** Website Katalog Produk MamikhevanShop (nama toko: MamikhevanShop)  
**Tech Stack:** Laravel 11, Tailwind CSS, Alpine.js, SQLite  
**Tanggal Mulai:** November 2026  
**Status Keseluruhan:** 🔄 In Progress

---

## FASE 1 — PERANCANGAN & SETUP AWAL

### TKT-001 · Analisis Kebutuhan & Scope
- **Status:** ✅ Selesai
- **Deskripsi:** Mendefinisikan kebutuhan fungsional website katalog produk pakaian anak **MamikhevanShop**. Menentukan fitur utama: katalog produk, keranjang belanja, checkout, manajemen order, halaman kontak, dan panel admin.
- **Catatan:** Nama toko telah dikonfirmasi: **MamikhevanShop** (bukan kids-closet).
- **Kriteria Selesai:** Daftar fitur disetujui, scope dikonfirmasi, nama brand dikonfirmasi.

### TKT-002 · Perancangan Arsitektur Sistem
- **Status:** ✅ Selesai
- **Deskripsi:** Merancang arsitektur MVC dengan Laravel. Menentukan struktur folder, pola Controller-Service-Model, dan alur data antar komponen.
- **Kriteria Selesai:** Struktur folder terbentuk, pola arsitektur konsisten.

### TKT-003 · Perancangan Database Schema
- **Status:** ✅ Selesai
- **Deskripsi:** Merancang skema database dengan tabel: `users`, `categories`, `products`, `orders`, `order_items`, `contact_messages`. Menentukan relasi antar tabel dan kolom yang diperlukan.
- **Kriteria Selesai:** Semua migration file dibuat dan dapat dijalankan tanpa error.
- **File Terkait:**
  - `database/migrations/2026_01_01_000002_create_categories_table.php`
  - `database/migrations/2026_01_01_000003_create_products_table.php`
  - `database/migrations/2026_01_01_000004_create_orders_table.php`
  - `database/migrations/2026_01_01_000005_create_order_items_table.php`
  - `database/migrations/2026_01_01_000006_create_contact_messages_table.php`

### TKT-004 · Perancangan UI/UX Wireframe
- **Status:** ✅ Selesai
- **Deskripsi:** Merancang wireframe untuk halaman utama, halaman produk, detail produk, keranjang, checkout, dan dashboard admin. Menentukan palet warna, tipografi, dan layout responsif.
- **Kriteria Selesai:** Desain konsisten di semua halaman, responsif di mobile dan desktop.

### TKT-005 · Setup Proyek & Konfigurasi Environment
- **Status:** ✅ Selesai
- **Deskripsi:** Instalasi Laravel 11, konfigurasi `.env`, setup database PostgreSQL (DBngin), instalasi Tailwind CSS, Alpine.js, dan Vite. Konfigurasi autentikasi dengan Laravel Breeze.
- **Kriteria Selesai:** Aplikasi berjalan di local, `npm run dev` berfungsi, database termigrasi.
- **Catatan:**
  - Database: PostgreSQL 18.4 via DBngin, port 5432, nama DB `mamikhevan`
  - `DB_CONNECTION` diganti dari `sqlite` ke `pgsql`
  - `MAIL_FROM_ADDRESS` diset ke `halo@mamikhevanshop.id`
  - 14 tabel berhasil termigrasi
- **File Terkait:**
  - `.env`, `composer.json`, `package.json`, `vite.config.js`, `tailwind.config.js`

---

## FASE 2 — PENGEMBANGAN BACKEND

### TKT-006 · Model & Relasi Database
- **Status:** ✅ Selesai — Terverifikasi
- **Deskripsi:** Membuat Eloquent Model untuk semua entitas: `User`, `Category`, `Product`, `Order`, `OrderItem`, `ContactMessage`. Mendefinisikan relasi `hasMany`, `belongsTo`, `belongsToMany`. Implementasi trait `HasUniqueSlug` untuk slug otomatis.
- **Hasil Verifikasi:**
  - Semua model dapat di-instantiate tanpa error
  - `Category`: fillable name, slug, description, icon, is_active + relasi `products()`
  - `Product`: 14 fillable fields, scopes active/featured/inStock, accessor priceFormatted/imageUrl
  - `Order`: 6 status constants, methods statusLabel/statusColor/paymentLabel/isCancellable
  - `OrderItem`: relasi ke Order & Product, accessor priceFormatted/subtotalFormatted
  - `HasUniqueSlug`: auto-generate slug unik saat saving
- **Kriteria Selesai:** ✅ Semua relasi berfungsi, query tidak menghasilkan error.
- **File Terkait:**
  - `app/Models/Category.php`
  - `app/Models/Product.php`
  - `app/Models/Order.php`
  - `app/Models/OrderItem.php`
  - `app/Models/ContactMessage.php`
  - `app/Models/Concerns/HasUniqueSlug.php`

### TKT-007 · Sistem Autentikasi & Otorisasi
- **Status:** ✅ Selesai — Terverifikasi
- **Deskripsi:** Implementasi login, register, logout menggunakan Laravel Breeze. Menambahkan kolom `is_admin` pada tabel `users`. Membuat middleware `EnsureUserIsAdmin` untuk proteksi route admin.
- **Hasil Verifikasi:**
  - `User` model: fillable ['name','email','password','is_admin'], cast is_admin → boolean
  - `EnsureUserIsAdmin`: abort(403) jika bukan admin, alias `admin` terdaftar di bootstrap/app.php
  - Semua route `/admin/*` terlindungi middleware `auth` + `admin`
  - 53 route terdaftar total (auth, storefront, admin, profile)
- **Kriteria Selesai:** ✅ Login/register berfungsi, halaman admin tidak bisa diakses user biasa.
- **File Terkait:**
  - `app/Http/Middleware/EnsureUserIsAdmin.php`
  - `app/Http/Controllers/Auth/`

### TKT-008 · CartService — Logika Keranjang Belanja
- **Status:** ✅ Selesai — Terverifikasi
- **Deskripsi:** Membuat `CartService` berbasis session untuk mengelola keranjang belanja: tambah item, hapus item, update kuantitas, hitung total harga, dan bersihkan keranjang setelah checkout.
- **Hasil Verifikasi:**
  - Method: `contents()`, `items()`, `count()`, `subtotal()`, `shipping()`, `total()`, `add()`, `update()`, `remove()`, `clear()`
  - Key keranjang: `{product_id}|{size}`
  - Free shipping threshold: Rp300.000, ongkir default: Rp20.000
  - Validasi stok saat add & update
- **Kriteria Selesai:** ✅ Semua operasi keranjang berfungsi, data persisten dalam session.
- **File Terkait:**
  - `app/Services/CartService.php`
  - `app/Http/Controllers/CartController.php`

### TKT-009 · Checkout & Order Processing
- **Status:** ✅ Selesai — Terverifikasi
- **Deskripsi:** Implementasi alur checkout: validasi form `CheckoutRequest`, pembuatan record `Order` dan `OrderItem`, pengurangan stok produk, pembersihan keranjang, dan redirect ke halaman konfirmasi order.
- **Hasil Verifikasi:**
  - DB transaction dengan `lockForUpdate()` untuk cegah race condition stok
  - Validasi stok per item sebelum order dibuat
  - Order code auto-generate format: `KK-YYMMDD-XXXXX`
  - Redirect ke `orders.show` dengan kode order setelah sukses
- **Kriteria Selesai:** ✅ Order tersimpan ke database, stok berkurang, keranjang bersih setelah checkout.
- **File Terkait:**
  - `app/Http/Controllers/CheckoutController.php`
  - `app/Http/Requests/CheckoutRequest.php`

### TKT-010 · Sistem Kontak & Pesan
- **Status:** ✅ Selesai — Terverifikasi
- **Deskripsi:** Implementasi form kontak dengan validasi `ContactRequest`. Menyimpan pesan ke tabel `contact_messages` dan menandai status pesan (baru/sudah dibaca) di panel admin.
- **Hasil Verifikasi:**
  - `ContactController@store` menyimpan via `ContactMessage::create($request->validated())`
  - Model fillable: name, email, phone, subject, message, is_read (cast boolean)
  - Route: GET/POST `/kontak` → `contact.index` / `contact.store`
- **Kriteria Selesai:** ✅ Pesan tersimpan, admin bisa melihat dan menandai pesan.
- **File Terkait:**
  - `app/Http/Controllers/ContactController.php`
  - `app/Http/Requests/ContactRequest.php`

### TKT-011 · Security Headers Middleware
- **Status:** ✅ Selesai — Terverifikasi
- **Deskripsi:** Implementasi `SecurityHeaders` middleware untuk menambahkan HTTP security headers pada setiap response.
- **Hasil Verifikasi:**
  - Headers yang diterapkan: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, `X-XSS-Protection`
  - HSTS (`Strict-Transport-Security`) hanya aktif di production
  - Terdaftar sebagai global web middleware di `bootstrap/app.php`
- **Kriteria Selesai:** ✅ Headers muncul di setiap response HTTP.
- **File Terkait:**
  - `app/Http/Middleware/SecurityHeaders.php`

---

## FASE 3 — PENGEMBANGAN FRONTEND & HALAMAN

### TKT-012 · Layout Utama & Komponen Bersama
- **Status:** ✅ Selesai
- **Deskripsi:** Membuat `AppLayout` dan `GuestLayout` sebagai layout utama. Membuat komponen reusable: navbar, footer, tombol, input, dll. Implementasi desain responsif dengan Tailwind CSS.
- **Kriteria Selesai:** Layout konsisten di semua halaman, responsif di semua ukuran layar.
- **File Terkait:**
  - `resources/views/layouts/`
  - `resources/views/components/`
  - `app/View/Components/AppLayout.php`

### TKT-013 · Halaman Beranda (Home)
- **Status:** ✅ Selesai
- **Deskripsi:** Membuat halaman utama dengan: hero section, produk unggulan/terbaru, kategori populer, dan call-to-action. Menampilkan data dinamis dari database.
- **Kriteria Selesai:** Halaman tampil produk dan kategori terbaru, layout menarik dan responsif.
- **File Terkait:**
  - `resources/views/home.blade.php`
  - `app/Http/Controllers/HomeController.php`

### TKT-014 · Halaman Katalog & Detail Produk
- **Status:** ✅ Selesai
- **Deskripsi:** Halaman daftar produk dengan filter kategori dan pencarian. Halaman detail produk dengan galeri gambar, deskripsi, harga, stok, dan tombol tambah ke keranjang.
- **Kriteria Selesai:** Filter dan pencarian berfungsi, detail produk lengkap, tombol cart berfungsi.
- **File Terkait:**
  - `resources/views/products/`
  - `app/Http/Controllers/ProductController.php`

### TKT-015 · Halaman Keranjang Belanja
- **Status:** ✅ Selesai
- **Deskripsi:** Tampilan keranjang belanja dengan daftar item, update kuantitas, hapus item, ringkasan total harga, dan tombol lanjut ke checkout.
- **Kriteria Selesai:** Semua operasi keranjang terefleksi di UI secara real-time.
- **File Terkait:**
  - `resources/views/cart/`

### TKT-016 · Halaman Checkout
- **Status:** ✅ Selesai
- **Deskripsi:** Form checkout dengan field: nama, alamat pengiriman, nomor telepon, metode pembayaran. Validasi sisi server. Tampilan ringkasan order sebelum konfirmasi.
- **Kriteria Selesai:** Form tervalidasi, order dibuat, user diarahkan ke halaman konfirmasi.
- **File Terkait:**
  - `resources/views/checkout/`

### TKT-017 · Halaman Riwayat & Detail Order
- **Status:** ✅ Selesai
- **Deskripsi:** Halaman daftar order milik user yang login beserta status. Halaman detail order dengan item, total, dan informasi pengiriman.
- **Kriteria Selesai:** User hanya bisa melihat order miliknya sendiri.
- **File Terkait:**
  - `resources/views/orders/`
  - `app/Http/Controllers/OrderController.php`

### TKT-018 · Halaman Tentang & Kontak
- **Status:** ✅ Selesai
- **Deskripsi:** Halaman `about.blade.php` berisi informasi toko. Halaman kontak dengan form pengiriman pesan.
- **Kriteria Selesai:** Form kontak terkirim, pesan tersimpan di database.
- **File Terkait:**
  - `resources/views/about.blade.php`
  - `resources/views/contact.blade.php`

### TKT-019 · Halaman Profil Pengguna
- **Status:** ✅ Selesai
- **Deskripsi:** Halaman edit profil user: update nama, email, dan ganti password. Validasi unik email.
- **Kriteria Selesai:** Update profil tersimpan, validasi berjalan.
- **File Terkait:**
  - `resources/views/profile/`
  - `app/Http/Controllers/ProfileController.php`

---

## FASE 4 — PANEL ADMIN

### TKT-020 · Dashboard Admin
- **Status:** ✅ Selesai
- **Deskripsi:** Halaman dashboard admin dengan ringkasan statistik: total produk, total order, total pendapatan, dan pesan baru. Dilindungi middleware `EnsureUserIsAdmin`.
- **Kriteria Selesai:** Data statistik akurat, akses terbatas hanya admin.
- **File Terkait:**
  - `resources/views/admin/dashboard.blade.php`
  - `app/Http/Controllers/Admin/DashboardController.php`

### TKT-021 · Manajemen Produk (Admin)
- **Status:** ✅ Selesai
- **Deskripsi:** CRUD produk: tambah, edit, hapus, dan lihat daftar produk. Upload gambar produk. Validasi input dengan `ProductRequest`. Slug otomatis dari nama produk.
- **Kriteria Selesai:** CRUD berfungsi penuh, gambar terupload dan tampil, slug unik.
- **File Terkait:**
  - `resources/views/admin/products/`
  - `app/Http/Controllers/Admin/ProductController.php`
  - `app/Http/Requests/Admin/ProductRequest.php`

### TKT-022 · Manajemen Kategori (Admin)
- **Status:** ✅ Selesai
- **Deskripsi:** CRUD kategori produk. Validasi input dengan `CategoryRequest`. Slug otomatis.
- **Kriteria Selesai:** CRUD berfungsi, kategori tampil di filter produk.
- **File Terkait:**
  - `resources/views/admin/categories/`
  - `app/Http/Controllers/Admin/CategoryController.php`
  - `app/Http/Requests/Admin/CategoryRequest.php`

### TKT-023 · Manajemen Order (Admin)
- **Status:** ✅ Selesai
- **Deskripsi:** Daftar semua order dari semua user. Update status order (pending, processing, shipped, delivered, cancelled). Detail order dengan item dan informasi customer.
- **Kriteria Selesai:** Status order bisa diupdate, daftar order tampil lengkap.
- **File Terkait:**
  - `resources/views/admin/orders/`
  - `app/Http/Controllers/Admin/OrderController.php`

### TKT-024 · Manajemen Pesan Kontak (Admin)
- **Status:** ✅ Selesai
- **Deskripsi:** Daftar semua pesan kontak dari pengunjung. Tandai pesan sebagai sudah dibaca. Hapus pesan.
- **Kriteria Selesai:** Pesan tampil, status bisa diubah, penanda "baru" muncul di dashboard.
- **File Terkait:**
  - `resources/views/admin/messages/`
  - `app/Http/Controllers/Admin/MessageController.php`

---

## FASE 5 — PENGUJIAN TEKNIS

### TKT-025 · Setup Testing Environment
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Konfigurasi PHPUnit untuk testing. Setup database testing (SQLite in-memory). Membuat factory untuk semua model.
- **Kriteria Selesai:** `php artisan test` berjalan tanpa error setup.
- **File Terkait:**
  - `phpunit.xml`
  - `database/factories/`

### TKT-026 · Unit Test — Model & Service
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Unit test untuk `CartService` (tambah, hapus, update kuantitas, kalkulasi total). Unit test untuk model: scope, relasi, dan accessor. Unit test `HasUniqueSlug`.
- **Kriteria Selesai:** Coverage minimal 80% untuk service dan model.
- **File Terkait:**
  - `tests/Unit/`

### TKT-027 · Feature Test — Autentikasi
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Test register user baru, login dengan kredensial valid/invalid, logout, reset password. Test proteksi route yang memerlukan login.
- **Kriteria Selesai:** Semua skenario autentikasi lulus test.
- **File Terkait:**
  - `tests/Feature/Auth/`

### TKT-028 · Feature Test — Alur Pembelian
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Test end-to-end alur: lihat produk → tambah ke keranjang → checkout → order tersimpan → stok berkurang.
- **Kriteria Selesai:** Alur pembelian lengkap lulus test tanpa error.
- **File Terkait:**
  - `tests/Feature/`

### TKT-029 · Feature Test — Panel Admin
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Test akses admin: user biasa tidak bisa akses `/admin`. Test CRUD produk, kategori, update status order via HTTP request.
- **Kriteria Selesai:** Otorisasi berfungsi, CRUD admin lulus test.

### TKT-030 · Validasi Form & Error Handling
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Test validasi semua form: checkout, kontak, produk (admin), kategori (admin), profil. Pastikan pesan error tampil dengan benar di UI.
- **Kriteria Selesai:** Semua form validasi menampilkan error yang tepat.

---

## FASE 6 — OPTIMASI & KEAMANAN

### TKT-031 · Optimasi Query Database
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Audit query N+1 menggunakan Laravel Debugbar atau Telescope. Tambahkan eager loading (`with()`) yang diperlukan. Tambahkan index pada kolom yang sering dicari (slug, status, user_id).
- **Kriteria Selesai:** Tidak ada N+1 query, halaman beban < 500ms.

### TKT-032 · Optimasi Aset & Performa Frontend
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Build aset production (`npm run build`). Optimasi gambar produk (resize, kompresi). Lazy loading gambar. Pastikan Tailwind CSS purge berfungsi (file CSS kecil di production).
- **Kriteria Selesai:** Build berhasil, ukuran CSS < 50KB setelah purge.

### TKT-033 · Audit Keamanan
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Verifikasi proteksi CSRF pada semua form POST. Validasi input untuk mencegah XSS. Pastikan user tidak bisa mengakses order milik user lain. Cek authorization pada semua route sensitif.
- **Kriteria Selesai:** Tidak ada celah CSRF, XSS, atau IDOR yang ditemukan.

### TKT-034 · Responsivitas & Cross-Browser
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Test tampilan di semua breakpoint (mobile 375px, tablet 768px, desktop 1280px+). Test di Chrome, Firefox, Edge, dan Safari. Perbaiki layout yang rusak.
- **Kriteria Selesai:** Tampilan konsisten di semua browser dan ukuran layar.

---

## FASE 7 — UJI PENGALAMAN PENGGUNA (END USER TESTING)

### TKT-035 · Penyiapan Data Demo & Seeding
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Membuat database seeder dengan data realistis: minimal 3 kategori, 20 produk dengan gambar, 1 akun admin, dan 2 akun user biasa untuk keperluan testing oleh end user.
- **Kriteria Selesai:** `php artisan db:seed` berjalan, data demo tersedia.
- **File Terkait:**
  - `database/seeders/`

### TKT-036 · Skenario Uji — Pengunjung (Guest)
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Verifikasi pengalaman pengunjung yang belum login:
  - [ ] Bisa melihat halaman beranda
  - [ ] Bisa browse katalog produk
  - [ ] Bisa filter produk berdasarkan kategori
  - [ ] Bisa melihat detail produk
  - [ ] Diarahkan ke halaman login saat mencoba checkout
  - [ ] Bisa mengirim pesan kontak
- **Kriteria Selesai:** Semua skenario berjalan mulus tanpa error.

### TKT-037 · Skenario Uji — User Terdaftar
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Verifikasi alur lengkap user yang sudah login:
  - [ ] Register akun baru berhasil
  - [ ] Login berhasil dan diarahkan ke halaman yang tepat
  - [ ] Tambah produk ke keranjang
  - [ ] Update kuantitas dan hapus item di keranjang
  - [ ] Proses checkout dengan mengisi form
  - [ ] Order muncul di riwayat pemesanan
  - [ ] Bisa melihat detail order
  - [ ] Bisa update profil dan ganti password
  - [ ] Logout berhasil
- **Kriteria Selesai:** Alur lengkap dari register hingga order berjalan tanpa hambatan.

### TKT-038 · Skenario Uji — Admin
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Verifikasi fungsionalitas panel admin:
  - [ ] Login sebagai admin dan masuk ke dashboard
  - [ ] Dashboard menampilkan statistik yang akurat
  - [ ] Tambah produk baru dengan gambar
  - [ ] Edit dan hapus produk
  - [ ] Tambah, edit, dan hapus kategori
  - [ ] Lihat daftar order dan update status order
  - [ ] Lihat dan kelola pesan kontak
  - [ ] Akun non-admin tidak bisa mengakses halaman admin
- **Kriteria Selesai:** Semua fitur admin berfungsi, otorisasi ketat.

### TKT-039 · Uji Usability — Kemudahan Penggunaan
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Evaluasi kemudahan penggunaan oleh pengguna nyata (minimal 3 orang berbeda). Poin yang dinilai:
  - Apakah navigasi mudah dipahami?
  - Apakah proses pembelian terasa intuitif?
  - Apakah pesan error/sukses jelas?
  - Apakah tampilan mobile nyaman digunakan?
- **Metode:** Sesi observasi langsung atau remote (5-10 menit per pengguna).
- **Kriteria Selesai:** Minimal 80% tugas diselesaikan tanpa bantuan, tidak ada kebingungan mayor.

### TKT-040 · Uji Performa dari Perspektif Pengguna
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Mengukur waktu muat halaman dari perspektif pengguna menggunakan browser DevTools atau Lighthouse. Target:
  - First Contentful Paint (FCP) < 1.5 detik
  - Largest Contentful Paint (LCP) < 2.5 detik
  - Skor Lighthouse Performance ≥ 80
- **Kriteria Selesai:** Semua target performa terpenuhi.

### TKT-041 · Uji Aksesibilitas
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Verifikasi aksesibilitas dasar:
  - [ ] Semua gambar memiliki atribut `alt`
  - [ ] Form memiliki label yang benar
  - [ ] Navigasi keyboard berfungsi
  - [ ] Kontras warna mencukupi (WCAG AA)
  - [ ] Struktur heading logis (H1, H2, H3)
- **Kriteria Selesai:** Tidak ada error aksesibilitas kritikal dari audit Lighthouse atau axe.

### TKT-042 · Pengumpulan Feedback & Iterasi Final
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Mengumpulkan semua feedback dari sesi end user testing. Memprioritaskan dan memperbaiki isu yang ditemukan. Melakukan putaran perbaikan terakhir sebelum deploy.
- **Kriteria Selesai:** Semua isu kritikal dan mayor telah diperbaiki dan diverifikasi ulang.

---

## FASE 8 — DEPLOYMENT & LAUNCHING

### TKT-043 · Persiapan Production Build
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Menjalankan `npm run build`, mengoptimasi autoloader composer (`composer install --optimize-autoloader --no-dev`), caching konfigurasi Laravel (`php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`).
- **Kriteria Selesai:** Aplikasi berjalan dengan baik di mode production.

### TKT-044 · Setup Environment Production
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Konfigurasi `.env` production: database production, mail driver, URL aplikasi, mode `APP_DEBUG=false`, `APP_ENV=production`. Setup storage link (`php artisan storage:link`).
- **Kriteria Selesai:** Aplikasi berjalan stabil di server production.

### TKT-045 · Smoke Test Post-Deploy
- **Status:** 🔄 Perlu Dikerjakan
- **Deskripsi:** Setelah deploy, lakukan smoke test cepat di production:
  - [ ] Halaman beranda terbuka
  - [ ] Bisa login/register
  - [ ] Bisa melihat dan beli produk
  - [ ] Panel admin bisa diakses
  - [ ] Tidak ada error 500 di log
- **Kriteria Selesai:** Website berjalan normal di production tanpa error kritikal.

---

## RINGKASAN STATUS

| Fase | Total Tiket | Selesai | Perlu Dikerjakan |
|------|:-----------:|:-------:|:----------------:|
| 1 · Perancangan & Setup | 5 | ✅ 5 | — |
| 2 · Backend | 6 | ✅ 6 | — |
| 3 · Frontend & Halaman | 8 | ✅ 8 | — |
| 4 · Panel Admin | 5 | ✅ 5 | — |
| 5 · Pengujian Teknis | 6 | — | 🔄 6 |
| 6 · Optimasi & Keamanan | 4 | — | 🔄 4 |
| 7 · End User Testing | 8 | — | 🔄 8 |
| 8 · Deployment | 3 | — | 🔄 3 |
| **TOTAL** | **45** | **24** | **21** |

---

## CATATAN & KEPUTUSAN PENTING

| Tanggal | Keputusan |
|---------|-----------|
| Nov 2026 | Nama toko dikonfirmasi: **MamikhevanShop** (folder project: kids-closet) |
| Nov 2026 | Database diganti ke PostgreSQL 18.4 via DBngin — port 5432, DB: `mamikhevan`, user: `postgres` |
| Nov 2026 | `MAIL_FROM_ADDRESS` diset ke `halo@mamikhevanshop.id` (dummy) |
| Nov 2026 | Keranjang belanja berbasis session (tidak perlu login untuk menambah ke keranjang) |
| Nov 2026 | Upload gambar produk disimpan di `storage/app/public/products` |
| Nov 2026 | Slug produk dan kategori di-generate otomatis dari nama menggunakan trait `HasUniqueSlug` |
| Nov 2026 | Middleware `EnsureUserIsAdmin` melindungi semua route `/admin/*` |

---

*File ini diperbarui setiap ada perubahan status tiket. Tandai tiket sebagai ✅ Selesai setelah kriteria selesai terpenuhi.*
