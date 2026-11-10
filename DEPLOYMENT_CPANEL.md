# Deployment cPanel

Repositori Git dapat berada di lokasi mana pun yang dikonfigurasi pada Git Version Control cPanel. Aplikasi Laravel berada di `/home/mamikhev/public_html/mamikhevan`, sedangkan document root domain utama adalah `/home/mamikhev/public_html`. `.cpanel.yml` memakai direktori checkout Git saat deployment, membangun aset, menjalankan migrasi, lalu menyalin front controller dan `.htaccess` dari `cpanel-main-domain/`.

## Persiapan server

1. Pastikan PHP, Composer, dan Node.js/npm tersedia untuk akun cPanel.
2. Siapkan `.env` produksi di `/home/mamikhev/public_html/mamikhevan/.env`. Jika repositori Git berada di folder lain, skrip dapat menyalin `.env` dari direktori checkout pada deployment pertama. `.env` tidak masuk Git.
3. Isi `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-anda` tanpa `/mamikhevan`, dan `APP_KEY`. Konfigurasi MySQL di `.env` harus memuat `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` sesuai database serta pengguna database yang dibuat di cPanel. Pastikan pengguna database memiliki akses ke database tersebut. Jangan masukkan kredensial ini ke Git.
4. Pada instalasi baru yang belum memiliki `APP_KEY`, jalankan `composer install --no-dev --optimize-autoloader --no-interaction` dan `php artisan key:generate` sekali di folder aplikasi. Jangan mengganti kunci pada deployment berikutnya.
5. Bila `mamikhevan/public/storage` adalah folder biasa, pindahkan isinya ke `mamikhevan/storage/app/public` sebelum deployment. Skrip tidak menghapus folder tersebut.

Untuk pemasangan manual `index.php` dan `.htaccess`, ikuti [petunjuk penyalinan](cpanel-main-domain/README.md). Jalankan **Update from Remote**, lalu **Deploy HEAD Commit** melalui Git Version Control cPanel untuk deployment otomatis. Deployment mempertahankan `.env` dan isi `storage/` pada folder aplikasi; migrasi berjalan menggunakan koneksi MySQL di `.env`. Setelah selesai, periksa domain utama dan log Laravel di `mamikhevan/storage/logs/` jika ada masalah.

Jika tombol **Deploy HEAD Commit** nonaktif, periksa direktori repo yang ditampilkan di Git Version Control cPanel dengan `git status --short`, `git branch --show-current`, dan `git ls-files .cpanel.yml`. cPanel memerlukan working tree bersih, branch, dan `.cpanel.yml` yang sudah masuk commit. Jika repo Git berada langsung di `public_html/mamikhevan`, `.htaccess` aplikasi yang disalin secara manual di akar repo akan muncul sebagai file untracked. File lain yang disalin atau dibuat manual di akar repo tetap perlu diperiksa satu per satu; jangan menghapusnya secara massal.
