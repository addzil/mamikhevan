# Main domain cPanel

Susunan file di server:

```text
/home/mamikhev/public_html/
  .htaccess                         <- dari public_html/.htaccess
  index.php                         <- dari public_html/index.php
  mamikhevan_shop/
    .htaccess                       <- dari mamikhevan_shop/.htaccess
    app/ bootstrap/ public/ vendor/ ...
```

Saat **Deploy HEAD Commit**, `.cpanel.yml` menyalin dua file dari folder `public_html/` ke `/home/mamikhev/public_html/`, dan file dari folder `mamikhevan_shop/` ke `/home/mamikhev/public_html/mamikhevan_shop/`. Untuk pemasangan manual, salin file ke lokasi yang sama. Jangan menimpa `mamikhevan_shop/public/index.php` atau `mamikhevan_shop/public/.htaccess`.

Pastikan `vendor/autoload.php` dan `public/build/manifest.json` ada di folder proyek. Deployment membuat symlink `public/storage` ke `storage/app/public` untuk gambar hasil unggahan. Atur `APP_URL` pada `.env` ke URL main domain tanpa `/mamikhevan_shop`.

Source Git tetap berada di `/home/mamikhev/repositories/mamikhevan`; folder aplikasi di `public_html/mamikhevan_shop` menerima salinan kode dari source saat deployment.
