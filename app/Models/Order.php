<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public const COURIER_JNE = 'jne';

    public const COURIER_JNT = 'jnt';

    public const COURIER_GOSEND = 'gosend';

    public const COURIER_LABELS = [
        self::COURIER_JNE => 'JNE',
        self::COURIER_JNT => 'J&T Express',
        self::COURIER_GOSEND => 'GoSend',
    ];

    protected $fillable = [
        'code',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'shipping_province',
        'shipping_city',
        'shipping_district',
        'shipping_subdistrict',
        'shipping_postal_code',
        'shipping_courier',
        'shipping_service',
        'shipping_tracking_number',
        'shipping_quote_source',
        'note',
        'payment_method',
        'payment_destination',
        'payment_proof_path',
        'payment_proof_uploaded_at',
        'subtotal',
        'shipping_cost',
        'promotion_id',
        'promotion_name',
        'voucher_code',
        'promotion_discount',
        'shipping_discount',
        'payment_unique_code',
        'total',
        'status',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'shipping_cost' => 'integer',
            'promotion_discount' => 'integer',
            'shipping_discount' => 'integer',
            'payment_unique_code' => 'integer',
            'payment_destination' => 'array',
            'payment_proof_uploaded_at' => 'datetime',
            'total' => 'integer',
            'delivered_at' => 'datetime',
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

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
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
        return match (true) {
            $this->payment_method === self::PAYMENT_COD => 'Bayar di Tempat (COD)',
            ($this->payment_destination['type'] ?? null) === 'qris' => 'QRIS',
            default => 'Transfer Bank',
        };
    }

    public function shippingLabel(): string
    {
        if ($this->shipping_quote_source === 'komerce' && filled($this->shipping_service)) {
            return ($this->shipping_courier === self::COURIER_JNE ? 'JNE ' : 'J&T ').$this->shipping_service;
        }

        return match ($this->shipping_courier) {
            self::COURIER_JNE => 'JNE Regular',
            self::COURIER_JNT => 'J&T Express',
            self::COURIER_GOSEND => 'GoSend SameDay',
            default => 'Pengiriman toko',
        };
    }

    public function courierLabel(): string
    {
        return self::COURIER_LABELS[$this->shipping_courier] ?? 'Ekspedisi';
    }

    public function courierBadgeClasses(): string
    {
        return match ($this->shipping_courier) {
            self::COURIER_JNE => 'bg-red-600 text-white',
            self::COURIER_JNT => 'bg-orange-500 text-white',
            self::COURIER_GOSEND => 'bg-emerald-600 text-white',
            default => 'bg-stone-700 text-white',
        };
    }

    public function shippingServiceLabel(): string
    {
        $service = Str::upper(trim((string) $this->shipping_service));

        if ($service === 'SAME_DAY') {
            return 'SAME DAY';
        }

        if (in_array($service, ['', 'REGULAR'], true)) {
            return match ($this->shipping_courier) {
                self::COURIER_JNE => 'REG',
                self::COURIER_JNT => 'EZ',
                self::COURIER_GOSEND => 'SAME DAY',
                default => 'REGULER',
            };
        }

        return $service;
    }

    public function courierHandlingNote(): string
    {
        return match ($this->shipping_courier) {
            self::COURIER_GOSEND => 'Kurir GoSend menjemput paket ke toko. Siapkan paket saat kurir tiba.',
            self::COURIER_JNE => 'Serahkan paket ke agen/counter JNE atau tunggu penjemputan kurir.',
            self::COURIER_JNT => 'Serahkan paket ke agen/counter J&T atau tunggu penjemputan kurir.',
            default => 'Pilih jasa ekspedisi pada checkout untuk menampilkan instruksi pengiriman.',
        };
    }

    public function estimatedWeightGrams(): int
    {
        $this->loadMissing('items.product');

        return (int) $this->items->sum(function (OrderItem $item): int {
            $unitWeight = $item->product?->weight_grams ?? (int) config('shipping.assumed_item_weight_grams');

            return $unitWeight * $item->quantity;
        });
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * @return array<int, string>
     */
    public function adminNextStatuses(): array
    {
        return match ($this->status) {
            self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
            self::STATUS_CONFIRMED => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
            self::STATUS_PROCESSING => [self::STATUS_SHIPPED, self::STATUS_CANCELLED],
            self::STATUS_CANCELLED => [self::STATUS_PENDING],
            default => [],
        };
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
