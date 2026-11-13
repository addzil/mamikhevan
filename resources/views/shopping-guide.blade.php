@extends('layouts.storefront')

@section('title', 'Cara Belanja — Mamikhevanshop')
@section('meta_description', 'Panduan memesan produk, memilih pembayaran, mengirim bukti transfer, menerima email, dan melacak pesanan di Mamikhevanshop.')

@section('content')
    <section class="border-b border-yellow-100 bg-gradient-to-b from-yellow-50/80 to-[#FFFDF4]">
        <div class="mx-auto max-w-4xl px-4 py-14 sm:px-6 sm:py-20">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-amber-500">Bantuan pelanggan</p>
            <h1 class="mt-3 font-display text-3xl font-extrabold text-stone-900 sm:text-5xl">Cara belanja</h1>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-500 sm:text-base">Ikuti alur pemesanan dari memilih produk sampai paket diterima. Email dikirim ke alamat yang diisi saat checkout, jadi pastikan alamatnya benar dan periksa folder spam.</p>
        </div>
    </section>

    <section class="mx-auto max-w-4xl space-y-4 px-4 py-12 sm:px-6">
        @php
            $steps = [
                ['title' => 'Pilih produk, ukuran, dan stok', 'image' => '01-pilih-produk.svg', 'alt' => 'Ilustrasi halaman detail untuk memilih ukuran dan menambahkan produk', 'text' => 'Buka Katalog lalu pilih produk untuk melihat detailnya. Setiap ukuran menampilkan jumlah stok yang tersedia. Pilih ukuran yang masih tersedia, kemudian tentukan jumlah yang tidak melebihi stok ukuran tersebut. Ukuran yang habis tidak dapat dipilih. Tekan Tambah ke keranjang untuk melanjutkan.', 'tip' => 'Stok ditampilkan per ukuran, bukan hanya sebagai jumlah total produk. Stok dan harga diperiksa kembali saat pesanan dibuat.'],
                ['title' => 'Periksa keranjang', 'image' => '02-keranjang.svg', 'alt' => 'Ilustrasi halaman keranjang dengan produk dan tombol lanjut checkout', 'text' => 'Pastikan produk, ukuran yang dipilih, jumlah, sisa stok ukuran, dan subtotal sudah benar. Perbarui jumlah atau hapus produk bila perlu, lalu pilih Lanjut ke checkout.', 'tip' => 'Periksa panduan ukuran sebelum melanjutkan. Jumlah tidak dapat melebihi stok yang tersedia untuk ukuran pilihan.'],
                ['title' => 'Isi data penerima dan pengiriman', 'image' => '03-checkout.svg', 'alt' => 'Ilustrasi checkout untuk data penerima dan pilihan ekspedisi', 'text' => 'Isi nama, nomor telepon aktif, email yang dapat dibuka, dan alamat lengkap. Pilih ekspedisi serta layanan yang tersedia, lalu cek ongkir dan ringkasan total.', 'tip' => 'Email checkout digunakan untuk mengirim konfirmasi dan pembaruan pesanan.'],
                ['title' => 'Pilih metode dan rekening pembayaran', 'image' => '04-pembayaran.svg', 'alt' => 'Ilustrasi pilihan pembayaran dan rincian total pesanan', 'text' => 'Sebelum membuat pesanan, wajib pilih metode pembayaran dan rekening/tujuan pembayaran yang tersedia. Pilihan yang dibuat akan tercatat pada pesanan dan dicantumkan dalam arahan pembayaran. COD hanya tampil bila pesanan memenuhi syarat; COD tidak memerlukan transfer. GoSend SameDay hanya mendukung transfer. Terapkan voucher jika ada, lalu periksa kembali total.', 'tip' => 'Gunakan hanya rekening atau QRIS yang ditampilkan untuk pesanan tersebut.'],
                ['title' => 'Buat pesanan dan baca email konfirmasi', 'image' => '04-pembayaran.svg', 'alt' => 'Ilustrasi rincian total dan metode pembayaran', 'text' => 'Tekan Buat Pesanan. Pesanan berstatus Menunggu dan stok untuk ukuran yang dipilih langsung dipesan agar tidak dibeli melebihi persediaan. Email konfirmasi dikirim ke email checkout berisi kode pesanan, produk, ukuran, jumlah, rincian nilai (termasuk ongkir, diskon atau kode unik bila ada), metode/rekening pilihan, serta instruksi pembayaran. Admin juga menerima pemberitahuan pesanan baru pada email notifikasi yang sudah diatur di profil.', 'tip' => 'Simpan kode pesanan. Jika email belum terlihat, periksa folder spam/promosi dan pastikan email checkout benar.'],
                ['title' => 'Bayar dan konfirmasikan pembayaran', 'image' => '04-pembayaran.svg', 'alt' => 'Ilustrasi pilihan pembayaran dan rincian total pesanan', 'text' => 'Untuk transfer, bayar sebesar nominal yang tercantum ke rekening/tujuan terpilih. Dari halaman detail pesanan, unggah bukti pembayaran (JPG, PNG, atau WebP maksimal 5 MB) untuk konfirmasi manual; bukti akan diperiksa admin dan unggahan tidak langsung mengubah status menjadi Dikonfirmasi. Anda juga dapat menghubungi admin melalui WhatsApp. Untuk COD, bayar kepada kurir saat paket diterima dan tidak perlu mengunggah bukti transfer.', 'tip' => 'Admin akan memperbarui status setelah memeriksa pembayaran. Email status dikirim ketika pesanan dikonfirmasi, diproses, dibatalkan, atau dibuka kembali.'],
                ['title' => 'Lacak pesanan hingga diterima', 'image' => '05-lacak-pesanan.svg', 'alt' => 'Ilustrasi formulir lacak pesanan beserta status dan nomor resi', 'text' => 'Buka Lacak Pesanan dan masukkan kode pesanan serta nomor telepon checkout. Setelah pesanan dikirim, nomor resi tampil di detail pesanan dan email pemberitahuan pengiriman dikirim. Setelah paket diterima, konfirmasikan penerimaan di halaman pesanan; pesanan menjadi Selesai dan Anda dapat memberi ulasan produk.', 'tip' => 'Jika pesanan dibatalkan, email pembatalan dikirim. Bila sudah terlanjur transfer, hubungi admin untuk bantuan pengembalian dana.'],
            ];
        @endphp
        @foreach ($steps as $index => $step)
            <article class="overflow-hidden rounded-3xl border border-yellow-100 bg-white shadow-sm">
                <div class="flex gap-4 p-5 sm:gap-5 sm:p-6">
                    <span class="grid h-10 w-10 flex-none place-items-center rounded-2xl bg-amber-100 font-display text-lg font-extrabold text-amber-700">{{ $index + 1 }}</span>
                    <div>
                    <h2 class="font-display text-lg font-bold text-stone-900">{{ $step['title'] }}</h2>
                    <p class="mt-2 text-sm leading-7 text-stone-500">{{ $step['text'] }}</p>
                    <p class="mt-3 rounded-xl bg-amber-50 px-4 py-3 text-xs font-semibold leading-6 text-amber-800"><span class="font-extrabold">Tips:</span> {{ $step['tip'] }}</p>
                    </div>
                </div>
                <figure class="border-t border-yellow-100 bg-[#fffdf6] p-4 sm:p-6">
                    <img src="{{ asset('images/help/'.$step['image']) }}" alt="{{ $step['alt'] }}" loading="lazy" class="mx-auto w-full max-w-3xl rounded-2xl border border-stone-200 shadow-sm">
                    <figcaption class="mt-2 text-center text-[11px] font-medium text-stone-400">Ilustrasi contoh langkah di website Mamikhevanshop</figcaption>
                </figure>
            </article>
        @endforeach
        <div class="flex flex-wrap gap-3 pt-3">
            <a href="{{ route('products.index') }}" class="rounded-full bg-amber-500 px-6 py-3 text-sm font-extrabold text-white hover:bg-amber-600">Mulai belanja</a>
            <a href="{{ route('contact.index') }}" class="rounded-full border border-yellow-200 bg-white px-6 py-3 text-sm font-extrabold text-stone-700 hover:border-amber-300">Hubungi admin</a>
            <a href="{{ route('faq') }}" class="rounded-full border border-yellow-200 bg-white px-6 py-3 text-sm font-extrabold text-stone-700 hover:border-amber-300">Baca FAQ</a>
        </div>
    </section>
@endsection
