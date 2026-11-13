<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PaymentSetting extends Model
{
    protected $fillable = [
        'bank_accounts',
        'qris_image',
        'qris_enabled',
        'whatsapp_number',
        'whatsapp_numbers',
    ];

    protected function casts(): array
    {
        return [
            'bank_accounts' => 'array',
            'qris_enabled' => 'boolean',
            'whatsapp_numbers' => 'array',
        ];
    }

    /**
     * @return array{bank_accounts: array<int, array{bank: string, account_name: string, account_number: string, is_active: bool}>, qris_image: ?string, qris_enabled: bool, whatsapp_numbers: array<int, string>}
     */
    public static function current(): array
    {
        $settings = self::query()->first();

        return [
            'bank_accounts' => collect($settings?->bank_accounts ?? [])
                ->filter(fn (array $account) => (bool) ($account['is_active'] ?? false))
                ->values()
                ->all(),
            'qris_image' => $settings?->qris_image,
            'qris_enabled' => (bool) $settings?->qris_enabled,
            'whatsapp_numbers' => collect($settings?->whatsapp_numbers ?? [])
                ->filter(fn (array $admin) => (bool) ($admin['is_active'] ?? false) && filled($admin['phone'] ?? null))
                ->map(fn (array $admin) => (string) $admin['phone'])
                ->values()
                ->all() ?: (filled($settings?->whatsapp_number) ? [$settings->whatsapp_number] : []),
        ];
    }

    /** @return array<string, array{type: string, bank?: string, account_name?: string, account_number?: string, image?: string}> */
    public static function availableDestinations(): array
    {
        $settings = self::current();
        $destinations = [];

        foreach ($settings['bank_accounts'] as $account) {
            $destination = [
                'type' => 'bank',
                'bank' => $account['bank'],
                'account_name' => $account['account_name'],
                'account_number' => $account['account_number'],
            ];
            $key = 'bank:'.hash('sha256', implode('|', array_values($destination)));
            $destinations[$key] = $destination;
        }

        if ($settings['qris_enabled'] && filled($settings['qris_image']) && Storage::disk('public')->exists($settings['qris_image'])) {
            $destinations['qris'] = ['type' => 'qris', 'image' => $settings['qris_image']];
        }

        return $destinations;
    }
}
