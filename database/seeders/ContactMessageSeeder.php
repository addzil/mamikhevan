<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use Illuminate\Database\Seeder;

class ContactMessageSeeder extends Seeder
{
    public function run(): void
    {
        if (ContactMessage::query()->exists()) {
            return;
        }

        ContactMessage::create([
            'name' => 'Maya Puspita',
            'email' => 'maya@example.com',
            'phone' => '081234567811',
            'subject' => 'Tanya ukuran untuk anak 18 bulan',
            'message' => 'Halo, anak saya usia 18 bulan dengan berat 11 kg. Untuk Onesie Cloud Soft sebaiknya ambil ukuran berapa ya? Terima kasih.',
            'is_read' => false,
        ]);

        ContactMessage::create([
            'name' => 'Fajar Nugroho',
            'email' => 'fajar@example.com',
            'phone' => null,
            'subject' => 'Ketersediaan jaket denim',
            'message' => 'Apakah Jaket Denim Ceria ukuran 6T akan restock lagi? Saya ingin membeli dua untuk kakak beradik.',
            'is_read' => false,
        ]);

        ContactMessage::create([
            'name' => 'Citra Dewi',
            'email' => 'citra@example.com',
            'phone' => '081234567812',
            'subject' => 'Terima kasih pesanan cepat',
            'message' => 'Paket sudah sampai dengan rapi dan anak saya suka sekali. Bahan lembut dan sesuai deskripsi. Sukses terus Mamikhevanshop!',
            'is_read' => true,
        ]);
    }
}
