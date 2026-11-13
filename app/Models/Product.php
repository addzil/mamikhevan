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
        'size_stocks',
        'unallocated_stock',
        'weight_grams',
        'sizes',
        'size_guide_type_id',
        'gender',
        'age_group',
        'material',
        'image',
        'is_featured',
        'is_active',
        'cod_available',
        'free_shipping',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'cost_price' => 'integer',
            'compare_price' => 'integer',
            'stock' => 'integer',
            'size_stocks' => 'array',
            'unallocated_stock' => 'integer',
            'weight_grams' => 'integer',
            'sizes' => 'array',
            'size_guide_type_id' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'cod_available' => 'boolean',
            'free_shipping' => 'boolean',
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

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryAdjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function visibleReviews(): HasMany
    {
        return $this->reviews()->where('is_visible', true);
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
        if ($this->sizeList() !== []) {
            return collect($this->sizeStockMap())->contains(fn (int $stock): bool => $stock > 0);
        }

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

    /** @return array<string, int> */
    public function sizeStockMap(): array
    {
        return collect($this->sizeList())
            ->mapWithKeys(fn (string $size): array => [$size => (int) ($this->size_stocks[$size] ?? 0)])
            ->all();
    }

    public function stockForSize(?string $size): int
    {
        if ($this->sizeList() === []) {
            return $size === null ? (int) $this->stock : 0;
        }

        if ($size === null || ! in_array($size, $this->sizeList(), true)) {
            return 0;
        }

        return (int) ($this->size_stocks[$size] ?? 0);
    }

    public function changeStockForSize(?string $size, int $difference): void
    {
        if ($this->sizeList() === []) {
            if ($size !== null) {
                return;
            }

            $this->stock += $difference;
            $this->save();

            return;
        }

        if ($size === null) {
            if ($difference > 0) {
                $this->unallocated_stock += $difference;
                $this->stock += $difference;
                $this->save();
            }

            return;
        }

        if (! in_array($size, $this->sizeList(), true)) {
            return;
        }

        $stocks = $this->sizeStockMap();
        $stocks[$size] = max(0, $stocks[$size] + $difference);
        $this->size_stocks = $stocks;
        $this->stock = array_sum($stocks) + (int) $this->unallocated_stock;
        $this->save();
    }

    /** @param array<string, int> $stocks */
    public function replaceSizeStocks(array $stocks, bool $clearUnallocated = false): void
    {
        $currentSizes = $this->sizeList();
        $this->size_stocks = collect($currentSizes)
            ->mapWithKeys(fn (string $size): array => [$size => max(0, (int) ($stocks[$size] ?? 0))])
            ->all();

        if ($clearUnallocated) {
            $this->unallocated_stock = 0;
        }

        $this->stock = array_sum($this->size_stocks) + (int) $this->unallocated_stock;
        $this->save();
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
