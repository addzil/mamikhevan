# Deployment cPanel

Source Git berada di `/home/mamikhev/repositories/mamikhevan`. Aplikasi Laravel berjalan di `/home/mamikhev/public_html/mamikhevan_shop`, sedangkan domain utama memakai `/home/mamikhev/public_html`. `.cpanel.yml` menyalin source ke folder aplikasi, membangun aset, menjalankan migrasi, dan memasang front controller serta `.htaccess` domain utama dari `cpanel-main-domain/`.

## Persiapan server

1. Pastikan PHP, Composer, dan Node.js/npm tersedia untuk akun cPanel.
2. Siapkan `.env` produksi di `/home/mamikhev/public_html/mamikhevan_shop/.env`. Pada deployment pertama, skrip dapat menyalinnya sekali dari `/home/mamikhev/repositories/mamikhevan/.env` jika file di folder aplikasi belum ada. `.env` tidak masuk Git.
3. Isi `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-anda` tanpa `/mamikhevan_shop`, `APP_KEY`, dan koneksi database. Untuk SQLite, hapus baris `DB_DATABASE` atau isi dengan path absolut ke `database/database.sqlite` pada folder aplikasi. Jangan arahkan ke database lama di folder repo kecuali memang ingin memakai database tersebut.
4. Pada instalasi baru yang belum memiliki `APP_KEY`, jalankan `composer install --no-dev --optimize-autoloader --no-interaction` dan `php artisan key:generate` sekali di folder aplikasi. Jangan mengganti kunci pada deployment berikutnya.
5. Bila `mamikhevan_shop/public/storage` adalah folder biasa, pindahkan isinya ke `mamikhevan_shop/storage/app/public` sebelum deployment. Skrip tidak menghapus folder tersebut.

Jalankan **Update from Remote**, lalu **Deploy HEAD Commit** melalui Git Version Control cPanel. Deployment mempertahankan `.env`, `database/database.sqlite`, dan isi `storage/` pada folder aplikasi. Setelah selesai, periksa domain utama dan log Laravel di `mamikhevan_shop/storage/logs/` jika ada masalah.
