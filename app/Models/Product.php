<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, HasUniqueSlug;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'cost_price',
        'compare_price',
        'stock',
        'sizes',
        'size_guide_type_id',
        'gender',
        'age_group',
        'material',
        'image',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'cost_price' => 'integer',
            'compare_price' => 'integer',
            'stock' => 'integer',
            'sizes' => 'array',
            'size_guide_type_id' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sizeGuideType(): BelongsTo
    {
        return $this->belongsTo(SizeGuideType::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    public function hasDiscount(): bool
    {
        return $this->compare_price !== null && $this->compare_price > $this->price;
    }

    public function discountPercent(): int
    {
        if (! $this->hasDiscount()) {
            return 0;
        }

        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    protected function priceFormatted(): Attribute
    {
        return Attribute::get(fn () => 'Rp '.number_format($this->price, 0, ',', '.'));
    }

    protected function costPriceFormatted(): Attribute
    {
        return Attribute::get(
            fn () => $this->cost_price !== null ? 'Rp '.number_format($this->cost_price, 0, ',', '.') : null
        );
    }

    protected function margin(): Attribute
    {
        return Attribute::get(
            fn () => $this->cost_price !== null ? $this->price - $this->cost_price : null
        );
    }

    protected function marginPercent(): Attribute
    {
        return Attribute::get(function () {
            if ($this->cost_price === null || $this->price <= 0) {
                return null;
            }

            return (int) round((($this->price - $this->cost_price) / $this->price) * 100);
        });
    }

    protected function comparePriceFormatted(): Attribute
    {
        return Attribute::get(
            fn () => $this->compare_price ? 'Rp '.number_format($this->compare_price, 0, ',', '.') : null
        );
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            if (blank($this->image)) {
                $firstImage = $this->relationLoaded('images')
                    ? $this->images->first()
                    : $this->images()->first();

                return $firstImage?->url;
            }

            if (str_starts_with($this->image, 'http')) {
                return $this->image;
            }

            if (str_starts_with($this->image, 'images/')) {
                return asset($this->image);
            }

            return Storage::disk('public')->url($this->image);
        });
    }

    public function galleryImageUrls(): array
    {
        return collect([$this->image_url])
            ->merge($this->images->map(fn (ProductImage $image) => $image->url))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function sizeList(): array
    {
        return collect($this->sizes ?? [])->filter()->values()->all();
    }

    public function genderLabel(): string
    {
        return match ($this->gender) {
            'laki-laki' => 'Anak Laki-laki',
            'perempuan' => 'Anak Perempuan',
            default => 'Uniseks',
        };
    }
}
