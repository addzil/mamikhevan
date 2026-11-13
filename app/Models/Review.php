<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    public const SOURCE_CUSTOMER = 'customer';

    public const SOURCE_GOOGLE = 'google';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCES = [
        self::SOURCE_CUSTOMER,
        self::SOURCE_GOOGLE,
        self::SOURCE_MANUAL,
    ];

    public const SOURCE_LABELS = [
        self::SOURCE_CUSTOMER => 'Pelanggan',
        self::SOURCE_GOOGLE => 'Google Maps',
        self::SOURCE_MANUAL => 'Manual',
    ];

    public const ASPECTS = [
        'product_quality' => 'Kualitas produk',
        'shipping_speed' => 'Kecepatan pengiriman',
        'service' => 'Pelayanan',
    ];

    protected $fillable = [
        'order_id',
        'order_item_id',
        'product_id',
        'user_id',
        'reviewer_name',
        'reviewer_city',
        'rating',
        'product_quality',
        'shipping_speed',
        'service',
        'comment',
        'source',
        'is_visible',
        'admin_reply',
        'replied_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'product_quality' => 'integer',
            'shipping_speed' => 'integer',
            'service' => 'integer',
            'is_visible' => 'boolean',
            'replied_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function sourceLabel(): string
    {
        return self::SOURCE_LABELS[$this->source] ?? ucfirst($this->source);
    }

    public function isGoogleReview(): bool
    {
        return $this->source === self::SOURCE_GOOGLE;
    }
}
