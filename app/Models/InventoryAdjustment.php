<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'product_name',
        'size',
        'system_stock',
        'counted_stock',
        'difference',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'system_stock' => 'integer',
            'counted_stock' => 'integer',
            'difference' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
