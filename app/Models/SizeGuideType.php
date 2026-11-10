<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SizeGuideType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'measurement_fields',
    ];

    protected function casts(): array
    {
        return [
            'measurement_fields' => 'array',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(SizeGuideEntry::class)->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getEntriesForLabels(array $labels)
    {
        return $this->entries()
            ->whereIn('label', $labels)
            ->orderBy('sort_order')
            ->get();
    }
}
