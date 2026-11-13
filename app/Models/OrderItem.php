<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'category_name',
        'size',
        'price',
        'quantity',
        'subtotal',
        'cost_price',
        'is_promo_gift',
        'original_price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'quantity' => 'integer',
            'subtotal' => 'integer',
            'cost_price' => 'integer',
            'is_promo_gift' => 'boolean',
            'original_price' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function productDisplayName(): string
    {
        return $this->size !== null
            ? $this->product_name.' (Ukuran '.$this->size.')'
            : $this->product_name;
    }

    protected function priceFormatted(): Attribute
    {
        return Attribute::get(fn () => 'Rp '.number_format($this->price, 0, ',', '.'));
    }

    protected function subtotalFormatted(): Attribute
    {
        return Attribute::get(fn () => 'Rp '.number_format($this->subtotal, 0, ',', '.'));
    }
}
