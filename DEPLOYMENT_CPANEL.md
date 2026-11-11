# Deployment cPanel

Repositori Git dapat berada di lokasi mana pun yang dikonfigurasi pada Git Version Control cPanel. Aplikasi Laravel berada di `/home/mamikhev/public_html/mamikhevan`, sedangkan document root domain utama adalah `/home/mamikhev/public_html`. `.cpanel.yml` memakai direktori checkout Git saat deployment, mengekstrak aset yang sudah dibangun, menjalankan migrasi, lalu menyalin front controller dan `.htaccess` domain utama dari `cpanel-main-domain/`. `.htaccess` aplikasi berada di akar repositori.

## Persiapan server

1. Pilih PHP 8.4.1 atau lebih baru untuk situs dan PHP CLI di akun cPanel. Composer serta Node.js/npm tidak diperlukan di server untuk alur ini.
2. Arsip `cpanel-runtime-no-terminal.zip` sudah masuk repositori Git dan ikut diambil saat **Update from Remote**. Skrip deploy mengekstraknya langsung ke `/home/mamikhev/public_html/mamikhevan` jika `unzip` tersedia. Jika log menyatakan `unzip` tidak tersedia, buka arsip tersebut di direktori repo melalui File Manager, pilih **Extract**, lalu isi tujuan `/home/mamikhev/public_html/mamikhevan`. Arsip hanya berisi `vendor/` dan `public/build/`; keduanya harus berada langsung di folder aplikasi. Jangan unggah `node_modules` atau mengekstrak arsip ke direktori repo bila repo berada di lokasi lain.
3. Siapkan `.env` produksi di `/home/mamikhev/public_html/mamikhevan/.env`. Isi `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-anda` tanpa `/mamikhevan`, dan `APP_KEY`. Konfigurasi MySQL harus memuat `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`. Jangan masukkan kredensial ini ke Git.
4. Bila `APP_KEY` server masih kosong, buat kunci di komputer lokal dengan `php artisan key:generate --show`, lalu salin hasilnya ke `.env` server. Jangan mengganti kunci yang sudah digunakan di produksi.
5. Bila `mamikhevan/public/storage` adalah folder biasa, pindahkan isinya ke `mamikhevan/storage/app/public` sebelum deployment. Skrip tidak menghapus folder tersebut.
6. Pastikan domain dalam `APP_URL` memiliki record A ke **Shared IP Address** atau **Dedicated IP Address** akun cPanel. Record DNS harus dibuat pada pengelola nameserver aktif. Setelah DNS mengarah ke server, aktifkan sertifikat SSL untuk domain tersebut.

## Alur pembaruan dari lokal

1. Lakukan perubahan dan uji di Herd. `.env` lokal memakai PostgreSQL `mamikhevan`, sedangkan `.env` production di cPanel tetap memakai MySQL. Jangan menyalin `.env` lokal atau `.env prod` ke server maupun Git.
2. Bila `composer.lock` berubah, jalankan `composer install` di lokal. Bila `package-lock.json` berubah, jalankan `npm ci` di lokal.
3. Dari akar proyek, jalankan `powershell -NoProfile -ExecutionPolicy Bypass -File .\prepare-cpanel-deploy.ps1`. Perintah ini membangun aset Vite dan memperbarui `cpanel-runtime-no-terminal.zip` dengan `vendor/` serta `public/build/` terbaru.
4. Periksa `git status --short` dan `git diff`. Stage hanya perubahan aplikasi yang memang akan dirilis beserta `cpanel-runtime-no-terminal.zip`, lalu periksa `git diff --cached --stat` dan `git diff --cached --check` sebelum commit. Berkas `.env*` selain `.env.example`, arsip cadangan lokal, serta ZIP/RAR lain di akar proyek diabaikan Git.
5. Commit dan push ke `origin/main`. Di cPanel Git Version Control, jalankan **Update from Remote** lalu **Deploy HEAD Commit**. Push Git saja tidak menjalankan deploy cPanel secara otomatis.
6. Buka halaman yang berubah di domain utama dan periksa CSS/JS serta log Laravel di `mamikhevan/storage/logs/`. Jika ada migrasi baru, deployment akan menjalankan `migrate --force` pada database production; cadangkan database production sebelum merilis migrasi yang mengubah data.

Untuk pemasangan manual `index.php` dan `.htaccess`, ikuti [petunjuk penyalinan](cpanel-main-domain/README.md). Skrip deploy mengekstrak runtime, memeriksa `vendor/autoload.php` dan `public/build/manifest.json`, lalu menjalankan penemuan paket, migrasi, cache, dan symlink penyimpanan. Deployment mempertahankan `.env` production dan isi `storage/`.

Jika tombol **Deploy HEAD Commit** nonaktif setelah **Extract**, lihat **Repository Path** pada Git Version Control, lalu buka path tersebut di File Manager. cPanel memerlukan working tree bersih, branch, dan `.cpanel.yml` yang sudah masuk commit. Bila arsip versi lama diekstrak di dalam repo dan muncul folder `public_html/` baru di akar repo, pindahkan hanya folder hasil ekstraksi yang tidak disengaja itu ke luar repo setelah memeriksa isinya. Pindahkan juga arsip ZIP/RAR yang diunggah manual ke luar repo jika muncul di sana. Jika repo berada langsung di `public_html/mamikhevan`, folder `vendor/` dan `public/build/` diabaikan oleh Git dan dapat tetap berada di sana. Jika masih nonaktif, minta penyedia hosting memeriksa `git status --short`, `git branch --show-current`, dan `git ls-files .cpanel.yml` pada Repository Path. Jangan menjalankan `git clean` atau menghapus file secara massal.
