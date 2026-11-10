# File untuk domain utama cPanel

Aplikasi Laravel berada di `/home/mamikhev/public_html/mamikhevan` dan document root domain utama berada di `/home/mamikhev/public_html`.

Untuk pemasangan manual, salin file berikut dengan nama yang sama. Tampilkan file tersembunyi di File Manager cPanel agar `.htaccess` terlihat.

| File di repositori | Lokasi di server |
| --- | --- |
| `public_html/index.php` | `/home/mamikhev/public_html/index.php` |
| `public_html/.htaccess` | `/home/mamikhev/public_html/.htaccess` |
| `mamikhevan/.htaccess` | `/home/mamikhev/public_html/mamikhevan/.htaccess` |

Jangan menimpa `mamikhevan/public/index.php` atau `mamikhevan/public/.htaccess`. File tersebut adalah bagian dari Laravel. Pastikan `mamikhevan/vendor/autoload.php` dan `mamikhevan/public/build/manifest.json` sudah ada. Untuk gambar hasil unggahan, `mamikhevan/public/storage` harus berupa symlink ke `mamikhevan/storage/app/public`.

Atur `APP_URL` di `mamikhevan/.env` ke URL domain utama tanpa `/mamikhevan`. Saat deployment otomatis, `.cpanel.yml` menyalin tiga file di atas ke lokasi yang sama.
