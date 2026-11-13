@extends('layouts.storefront')

@section('title', 'FAQ — Mamikhevanshop')
@section('meta_description', 'Jawaban pertanyaan umum tentang pemesanan, pembayaran, bukti transfer, pengiriman, dan status pesanan Mamikhevanshop.')

@section('content')
    @php
        $questions = [
            ['question' => 'Bagaimana cara membuat pesanan?', 'answer' => 'Pilih produk dan ukuran, masukkan ke keranjang, lalu isi data penerima dan pengiriman. Di checkout, pilih metode pembayaran serta rekening/tujuan yang tersedia sebelum menekan Buat Pesanan.'],
            ['question' => 'Email apa yang saya terima setelah memesan?', 'answer' => 'Email konfirmasi pesanan berisi kode pesanan, rincian produk dan nilai, metode serta tujuan pembayaran yang dipilih, dan instruksi pembayaran. Email pembaruan juga dikirim saat pesanan dikonfirmasi, diproses, dikirim, dibatalkan, atau dibuka kembali. Pastikan alamat email checkout benar dan periksa folder spam/promosi.'],
            ['question' => 'Bagaimana cara membayar dengan transfer?', 'answer' => 'Transfer sesuai total yang tertera pada detail pesanan ke rekening atau QRIS pilihan yang tercatat di pesanan. Ikuti nominal termasuk kode unik jika ditampilkan. Jangan menggunakan tujuan pembayaran dari pesanan lain.'],
            ['question' => 'Bagaimana mengirim bukti pembayaran?', 'answer' => 'Buka tautan/detail pesanan, lalu unggah bukti pembayaran untuk konfirmasi manual. Format yang diterima JPG, PNG, atau WebP dengan ukuran maksimal 5 MB. Anda juga dapat menghubungi admin melalui WhatsApp. Bukti yang diunggah akan diperiksa admin dan tidak otomatis mengubah status pesanan.'],
            ['question' => 'Berapa lama sampai pembayaran dikonfirmasi?', 'answer' => 'Pembayaran diperiksa secara manual oleh admin. Status tetap Menunggu sampai admin memperbarui pesanan. Email pemberitahuan dikirim ketika status pesanan berubah. Hubungi admin dengan menyertakan kode pesanan bila perlu bantuan.'],
            ['question' => 'Apakah saya dapat membayar saat barang diterima?', 'answer' => 'COD hanya tersedia untuk pesanan yang memenuhi syarat dan akan muncul sebagai pilihan di checkout. Pembayaran diberikan kepada kurir saat paket diterima. COD tidak memerlukan transfer atau unggah bukti pembayaran.'],
            ['question' => 'Di mana saya bisa melihat status dan nomor resi?', 'answer' => 'Gunakan menu Lacak Pesanan, kemudian masukkan kode pesanan dan nomor telepon yang digunakan saat checkout. Nomor resi tampil setelah admin mencatatnya dan pesanan berstatus Dikirim; email pemberitahuan pengiriman juga dikirim.'],
            ['question' => 'Bagaimana jika pesanan dibatalkan setelah saya transfer?', 'answer' => 'Email pembatalan akan dikirim ke alamat checkout. Pembayaran tidak dikembalikan otomatis; hubungi admin dengan kode pesanan dan informasi pembayaran untuk meminta bantuan pengembalian dana.'],
            ['question' => 'Bagaimana cara memastikan pesanan selesai?', 'answer' => 'Setelah paket diterima, buka detail pesanan dan tekan Konfirmasi barang diterima. Status berubah menjadi Selesai dan Anda dapat memberikan penilaian untuk produk.'],
            ['question' => 'Bagaimana jika saya tidak menerima email?', 'answer' => 'Periksa folder spam/promosi dan pastikan email yang dimasukkan saat checkout benar. Kode pesanan dan nomor telepon checkout juga dapat digunakan untuk melacak pesanan. Jika masih membutuhkan bantuan, hubungi admin.'],
        ];
    @endphp

    <section class="border-b border-yellow-100 bg-gradient-to-b from-yellow-50/80 to-[#FFFDF4]">
        <div class="mx-auto max-w-4xl px-4 py-14 sm:px-6 sm:py-20">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-amber-500">Bantuan pelanggan</p>
            <h1 class="mt-3 font-display text-3xl font-extrabold text-stone-900 sm:text-5xl">Pertanyaan umum</h1>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-500 sm:text-base">Temukan jawaban tentang pemesanan, pembayaran, konfirmasi, dan pengiriman.</p>
        </div>
    </section>

    <section class="mx-auto max-w-4xl space-y-3 px-4 py-12 sm:px-6">
        @foreach ($questions as $item)
            <details class="group rounded-2xl border border-yellow-100 bg-white p-5 shadow-sm" @if ($loop->first) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-display text-base font-bold text-stone-900">
                    {{ $item['question'] }}
                    <span class="text-xl text-amber-500 transition group-open:rotate-45" aria-hidden="true">+</span>
                </summary>
                <p class="mt-3 pr-8 text-sm leading-7 text-stone-500">{{ $item['answer'] }}</p>
            </details>
        @endforeach

        <div class="flex flex-wrap gap-3 pt-4">
            <a href="{{ route('shopping-guide') }}" class="rounded-full bg-amber-500 px-6 py-3 text-sm font-extrabold text-white hover:bg-amber-600">Lihat cara belanja</a>
            <a href="{{ route('contact.index') }}" class="rounded-full border border-yellow-200 bg-white px-6 py-3 text-sm font-extrabold text-stone-700 hover:border-amber-300">Hubungi admin</a>
        </div>
    </section>
@endsection
