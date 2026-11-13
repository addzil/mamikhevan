<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Bayi & Newborn',
                'slug' => 'bayi-newborn',
                'icon' => '👶',
                'description' => 'Pakaian lembut dan aman untuk si kecil baru lahir.',
            ],
            [
                'name' => 'Atasan',
                'slug' => 'atasan',
                'icon' => '👕',
                'description' => 'Kaos, kemeja, dan polo nyaman untuk bermain seharian.',
            ],
            [
                'name' => 'Bawahan',
                'slug' => 'bawahan',
                'icon' => '👖',
                'description' => 'Celana dan jogger lentur untuk anak aktif.',
            ],
            [
                'name' => 'Setelan',
                'slug' => 'setelan',
                'icon' => '🧸',
                'description' => 'Paduan atasan dan bawahan siap pakai untuk anak.',
            ],
            [
                'name' => 'Dress & Rok',
                'slug' => 'dress-rok',
                'icon' => '👗',
                'description' => 'Gaun dan rok cantik untuk momen spesial.',
            ],
            [
                'name' => 'Jaket & Outer',
                'slug' => 'jaket-outer',
                'icon' => '🧥',
                'description' => 'Jaket hangat dengan gaya yang tetap lucu.',
            ],
            [
                'name' => 'Sepatu & Aksesori',
                'slug' => 'sepatu-aksesori',
                'icon' => '👟',
                'description' => 'Sepatu, topi, dan pelengkap gaya si kecil.',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category + ['is_active' => true]
            );
        }
    }
}
