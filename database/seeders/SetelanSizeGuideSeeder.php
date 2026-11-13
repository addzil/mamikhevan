<?php

namespace Database\Seeders;

use App\Models\SizeGuideType;
use Illuminate\Database\Seeder;

class SetelanSizeGuideSeeder extends Seeder
{
    public function run(): void
    {
        $setelan = SizeGuideType::firstOrCreate(
            ['slug' => 'setelan'],
            [
                'name' => 'Setelan',
                'description' => 'Panduan ukuran untuk setelan atasan dan bawahan anak. Isi ukuran produk sebelum ditampilkan kepada pelanggan.',
                'measurement_fields' => ['chest_cm', 'waist_cm'],
            ]
        );

        foreach (['1T', '2T', '3T', '4T'] as $index => $label) {
            $setelan->entries()->firstOrCreate(
                ['label' => $label],
                ['sort_order' => $index + 1]
            );
        }
    }
}
