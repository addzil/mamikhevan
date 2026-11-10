<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'path',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(function () {
            if (blank($this->path)) {
                return null;
            }

            if (str_starts_with($this->path, 'http')) {
                return $this->path;
            }

            if (str_starts_with($this->path, 'images/')) {
                return asset($this->path);
            }

            return Storage::disk('public')->url($this->path);
        });
    }
}
