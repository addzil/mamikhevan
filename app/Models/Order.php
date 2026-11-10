<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Menunggu Konfirmasi',
        self::STATUS_CONFIRMED => 'Dikonfirmasi',
        self::STATUS_PROCESSING => 'Diproses',
        self::STATUS_SHIPPED => 'Dikirim',
        self::STATUS_COMPLETED => 'Selesai',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    public const PAYMENT_TRANSFER = 'transfer';
    public const PAYMENT_COD = 'cod';

    protected $fillable = [
        'code',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'note',
        'payment_method',
        'subtotal',
        'shipping_cost',
        'total',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'shipping_cost' => 'integer',
            'total' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $order) {
            if (blank($order->code)) {
                $order->code = 'KK-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-amber-100 text-amber-700 ring-amber-200',
            self::STATUS_CONFIRMED => 'bg-sky-100 text-sky-700 ring-sky-200',
            self::STATUS_PROCESSING => 'bg-indigo-100 text-indigo-700 ring-indigo-200',
            self::STATUS_SHIPPED => 'bg-violet-100 text-violet-700 ring-violet-200',
            self::STATUS_COMPLETED => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-700 ring-red-200',
            default => 'bg-gray-100 text-gray-700 ring-gray-200',
        };
    }

    public function paymentLabel(): string
    {
        return $this->payment_method === self::PAYMENT_COD ? 'Bayar di Tempat (COD)' : 'Transfer Bank';
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    protected function totalFormatted(): Attribute
    {
        return Attribute::get(fn () => 'Rp '.number_format($this->total, 0, ',', '.'));
    }

    protected function subtotalFormatted(): Attribute
    {
        return Attribute::get(fn () => 'Rp '.number_format($this->subtotal, 0, ',', '.'));
    }

    protected function shippingFormatted(): Attribute
    {
        return Attribute::get(
            fn () => $this->shipping_cost > 0 ? 'Rp '.number_format($this->shipping_cost, 0, ',', '.') : 'Gratis'
        );
    }
}
