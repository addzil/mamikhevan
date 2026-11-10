# Deployment cPanel

Repository dan aplikasi Laravel berada di `/home/mamikhev/repositories/mamikhevan`. Document root domain berada di `/home/mamikhev/public_html`. `.cpanel.yml` membangun aplikasi di repository, lalu menyalin isi `public/` ke `public_html` dan mengarahkan `index.php` ke aplikasi.

## Persiapan sekali di server

1. Pastikan PHP, Composer, dan Node.js/npm tersedia untuk akun cPanel.
2. Buat `/home/mamikhev/repositories/mamikhevan/.env` dari `.env.example`, lalu isi konfigurasi produksi. File ini tidak masuk Git.
3. Atur minimal `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-anda`, dan koneksi database yang benar. Untuk SQLite, pastikan direktori `database/` dapat ditulis oleh aplikasi. Untuk MySQL, isi `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`.
4. Pada instalasi baru, jalankan `composer install --no-dev --optimize-autoloader --no-interaction`, lalu `php artisan key:generate` dari direktori repository untuk mengisi `APP_KEY` di `.env`. Jangan mengganti kunci ini pada deployment berikutnya karena sesi dan data terenkripsi yang lama bisa menjadi tidak terbaca.
5. Jika `public_html/storage` sudah berupa direktori biasa, pindahkan isinya secara manual ke `storage/app/public` di repository sebelum deployment. Deployment akan berhenti dan tidak menghapus direktori tersebut.

Setelah persiapan, jalankan **Deploy HEAD Commit** melalui Git Version Control cPanel. Deployment berhenti dengan pesan kesalahan jika `.env` tidak ada atau nilai `APP_ENV`, `APP_DEBUG`, dan `APP_KEY` belum siap untuk produksi. Setelah deployment selesai, periksa halaman utama dan log di `storage/logs/` bila ada kesalahan.
