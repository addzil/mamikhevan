<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeBannerSetting extends Model
{
    protected $fillable = ['eyebrow', 'headline', 'description', 'design', 'background', 'cards', 'is_active'];

    protected function casts(): array
    {
        return ['cards' => 'array', 'is_active' => 'boolean'];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'eyebrow' => 'Koleksi baru',
            'headline' => 'Baju anak lucu, nyaman, siap dipakai.',
            'description' => 'Katalog pakaian anak dengan bahan lembut dan desain ceria. Temukan ukuran yang pas untuk si kecil.',
            'design' => 'collage',
            'background' => 'warm',
            'cards' => [
                ['name' => 'Dress Bunga Cherry', 'label' => 'Best seller', 'image' => 'images/products/dress.svg'],
                ['name' => 'Kaos Little Dino', 'label' => 'Favorit', 'image' => 'images/products/tshirt.svg'],
                ['name' => 'Onesie Cloud Soft', 'label' => 'Newborn', 'image' => 'images/products/onesie.svg'],
            ],
            'is_active' => true,
        ]);
    }
}
