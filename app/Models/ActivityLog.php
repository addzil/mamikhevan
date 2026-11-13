<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'actor_user_id',
        'actor_name',
        'actor_email',
        'actor_role',
        'session_hash',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function actionLabel(): string
    {
        return self::labelForAction($this->action);
    }

    public static function labelForAction(string $action): string
    {
        return match ($action) {
            'auth.login.succeeded' => 'Login berhasil',
            'auth.login.failed' => 'Login gagal',
            'auth.logout' => 'Logout',
            'auth.password_reset' => 'Reset kata sandi',
            'auth.email_verified' => 'Email diverifikasi',
            'store.cart.item_added' => 'Produk ditambahkan ke keranjang',
            'store.cart.item_updated' => 'Jumlah keranjang diubah',
            'store.cart.item_removed' => 'Produk dihapus dari keranjang',
            'store.cart.cleared' => 'Keranjang dikosongkan',
            'size_guide_type.entries_added' => 'Detail panduan ukuran ditambahkan',
            'size_guide_type.entries_replaced' => 'Detail panduan ukuran diubah',
            'size_guide_type.entries_deleted' => 'Detail panduan ukuran dihapus',
            default => self::modelActionLabel($action),
        };
    }

    private static function modelActionLabel(string $action): string
    {
        [$model, $event] = array_pad(explode('.', $action, 2), 2, 'updated');
        $modelLabel = match ($model) {
            'user' => 'Akun',
            'product' => 'Produk',
            'product_image' => 'Gambar produk',
            'category' => 'Kategori',
            'promotion' => 'Promo',
            'review' => 'Rating',
            'home_banner_setting' => 'Banner beranda',
            'size_guide_type' => 'Panduan ukuran',
            'size_guide_entry' => 'Detail panduan ukuran',
            'payment_setting' => 'Pengaturan pembayaran',
            'contact_message' => 'Pesan pelanggan',
            'inventory_adjustment' => 'Opname stok',
            'order' => 'Pesanan',
            'order_item' => 'Item pesanan',
            default => str($model)->replace('_', ' ')->title()->toString(),
        };

        $eventLabel = match ($event) {
            'created' => 'dibuat',
            'updated' => 'diubah',
            'deleted' => 'dihapus',
            default => $event,
        };

        return $modelLabel.' '.$eventLabel;
    }
}
