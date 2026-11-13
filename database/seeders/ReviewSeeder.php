<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        if (Review::query()->where('source', Review::SOURCE_GOOGLE)->exists()) {
            return;
        }

        $reviews = [
            [
                'reviewed_at' => '2025-07-29 12:00:00',
                'comment' => 'suka banget sama bahan bajunya yang super adem, udah gitu hrgnya murah terjangkau. anak2 jd kece pake baju branded dr mami khevan ini. buat kaos2 dirumah atau setelan sehari - hari jg banyak. jgn ragu utk beli disini ga akan kecwa.. sekali coba ketagihan beliiii',
            ],
            [
                'reviewed_at' => '2025-07-20 12:00:00',
                'comment' => 'sangat nyaman di pakainya semua baju anaku dari toko mamikhevan semua🥳🥳',
            ],
            [
                'reviewed_at' => '2025-07-20 11:00:00',
                'comment' => 'Recomended! Ga usah diragukan lagi kalo butuh dan cari baju anak yg lucu dan murah. Dijamin ga nyesel. Lemari auto penuh karena beli terus.',
            ],
            [
                'reviewed_at' => '2025-07-20 10:00:00',
                'comment' => 'Toko baju anak langganan akuuu... luculucu, kualitasnya ga diragukan lagi. Suka kasih hargaaaaaaaa promo juga😍 sukses mami khevan tokonyaaa. Ditunggu seris lainnyaa😍😍😍',
            ],
            [
                'reviewed_at' => '2025-07-20 09:00:00',
                'comment' => 'Bahannya bagus,buat ibu2 yang suka shoping baju anak dan bikin ketagihan terus 😍😍🫰',
            ],
            [
                'reviewed_at' => '2025-07-20 08:00:00',
                'comment' => 'Baju2 anak cewe dan cowo harga terjangkau dan bahannya juga bagussss poll untuk dipakai sehari hari dan pergi 😍😍😍',
            ],
            [
                'reviewed_at' => '2025-03-29 12:00:00',
                'comment' => 'Bajunya lucu, bahannya adem. Very recommended✨',
            ],
            [
                'reviewed_at' => '2024-10-14 12:00:00',
                'comment' => 'Toko baju anak termurah dan bagus Mantap',
            ],
        ];

        foreach ($reviews as $review) {
            Review::create([
                'reviewer_name' => 'Pelanggan Google Maps',
                'reviewer_city' => 'Tambun Selatan',
                'rating' => 5,
                'comment' => $review['comment'],
                'source' => Review::SOURCE_GOOGLE,
                'is_visible' => true,
                'reviewed_at' => $review['reviewed_at'],
            ]);
        }
    }
}
