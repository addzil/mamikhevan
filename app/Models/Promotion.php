<?php

namespace App\Models;

use Database\Factories\PromotionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    /** @use HasFactory<PromotionFactory> */
    use HasFactory;

    public const TYPE_PRICE = 'price';

    public const TYPE_BUY_GET = 'buy_get';

    public const TYPE_SHIPPING_DISCOUNT = 'shipping_discount';

    public const TYPE_SHIPPING_SUBSIDY = 'shipping_subsidy';

    public const TYPE_QUANTITY_TIER = 'quantity_tier';

    public const TYPE_GIVEAWAY = 'giveaway';

    public const TYPES = [self::TYPE_PRICE, self::TYPE_BUY_GET, self::TYPE_SHIPPING_DISCOUNT, self::TYPE_SHIPPING_SUBSIDY, self::TYPE_QUANTITY_TIER, self::TYPE_GIVEAWAY];

    public const TARGET_ALL = 'all';

    public const TARGET_PRODUCT = 'product';

    public const TARGET_CATEGORY = 'category';

    public const TARGETS = [self::TARGET_ALL, self::TARGET_PRODUCT, self::TARGET_CATEGORY];

    public const KIND_PERCENT = 'percent';

    public const KIND_FIXED = 'fixed';

    public const TIMEZONE = 'Asia/Jakarta';

    protected $fillable = [
        'name', 'event_name', 'voucher_code', 'type', 'target_type', 'product_id', 'category_id',
        'discount_kind', 'discount_value', 'maximum_discount', 'minimum_subtotal',
        'buy_quantity', 'gift_product_id', 'gift_size', 'gift_quantity', 'gift_unit_price',
        'tiers', 'starts_at', 'ends_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'integer',
            'maximum_discount' => 'integer',
            'minimum_subtotal' => 'integer',
            'buy_quantity' => 'integer',
            'gift_quantity' => 'integer',
            'gift_unit_price' => 'integer',
            'tiers' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function giftProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'gift_product_id');
    }

    public function displayName(): string
    {
        return filled($this->event_name) ? $this->event_name.' — '.$this->name : $this->name;
    }

    public function hasVoucher(): bool
    {
        return filled($this->voucher_code);
    }

    public function matchesVoucher(?string $code): bool
    {
        return $this->hasVoucher()
            && filled($code)
            && mb_strtoupper(trim($code)) === mb_strtoupper($this->voucher_code);
    }
}
