<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Str;

class ShippingQuoteService
{
    public function __construct(private readonly KomerceShippingCostService $komerce) {}

    /**
     * @param  array<string, array{product: Product, quantity: int}>  $items
     * @return array{weight_grams: int, uses_assumed_weight: bool, options: array<string, array{courier: string, service: string, label: string, cost: int, eta: string, source: string}>}
     */
    public function estimate(array $items, string $province, string $city): array
    {
        [$weightGrams, $usesAssumedWeight] = $this->weightDetails($items);

        $billedKilograms = max(1, intdiv($weightGrams + 999, 1000));
        $zone = $this->zone($province, $city);
        $rates = config('shipping.internal_rates.'.$zone);
        $options = [
            Order::COURIER_JNE => [
                'courier' => Order::COURIER_JNE,
                'service' => 'regular',
                'label' => 'JNE Regular',
                'cost' => $rates[Order::COURIER_JNE] * $billedKilograms,
                'eta' => 'Tarif toko',
                'source' => 'internal_rate',
            ],
            Order::COURIER_JNT => [
                'courier' => Order::COURIER_JNT,
                'service' => 'regular',
                'label' => 'J&T Express',
                'cost' => $rates[Order::COURIER_JNT] * $billedKilograms,
                'eta' => 'Tarif toko',
                'source' => 'internal_rate',
            ],
        ];

        if ($zone === 'metro' && $weightGrams <= 5000) {
            $options[Order::COURIER_GOSEND] = [
                'courier' => Order::COURIER_GOSEND,
                'service' => 'same_day',
                'label' => 'GoSend SameDay',
                'cost' => (int) config('shipping.gosend_sameday_estimate'),
                'eta' => 'Same day, jika area dan jarak memenuhi',
                'source' => 'internal_rate',
            ];
        }

        return [
            'weight_grams' => $weightGrams,
            'uses_assumed_weight' => $usesAssumedWeight,
            'options' => $options,
        ];
    }

    /**
     * @param  array<string, array{product: Product, quantity: int}>  $items
     * @return array{weight_grams: int, uses_assumed_weight: bool, options: array<string, array{courier: string, service: string, label: string, cost: int, eta: string, source: string}>}
     */
    public function estimateFromKomerce(array $items, int $destinationId, string $province, string $city): array
    {
        [$weightGrams, $usesAssumedWeight] = $this->weightDetails($items);
        $rates = collect($this->komerce->calculate($destinationId, $weightGrams));
        $options = [];

        foreach ([Order::COURIER_JNE, Order::COURIER_JNT] as $courier) {
            $preferredService = $courier === Order::COURIER_JNE ? 'REG' : 'EZ';
            $matching = $rates->where('code', $courier);
            $rate = $matching->first(fn ($row) => strtoupper($row['service']) === $preferredService)
                ?? $matching->sortBy('cost')->first();

            if ($rate === null) {
                continue;
            }

            $options[$courier] = [
                'courier' => $courier,
                'service' => $rate['service'],
                'label' => ($courier === Order::COURIER_JNE ? 'JNE ' : 'J&T ').$rate['service'],
                'cost' => $rate['cost'],
                'eta' => $rate['etd'] !== '' ? $rate['etd'] : 'Estimasi kurir',
                'source' => 'komerce',
            ];
        }

        $gosend = $this->estimate($items, $province, $city)['options'][Order::COURIER_GOSEND] ?? null;
        if ($gosend !== null) {
            $options[Order::COURIER_GOSEND] = $gosend;
        }

        return [
            'weight_grams' => $weightGrams,
            'uses_assumed_weight' => $usesAssumedWeight,
            'options' => $options,
        ];
    }

    /**
     * @param  array<string, array{product: Product, quantity: int}>  $items
     * @return array{int, bool}
     */
    private function weightDetails(array $items): array
    {
        $weightGrams = 0;
        $usesAssumedWeight = false;

        foreach ($items as $item) {
            $itemWeight = $item['product']->weight_grams;
            if ($itemWeight === null) {
                $itemWeight = (int) config('shipping.assumed_item_weight_grams');
                $usesAssumedWeight = true;
            }

            $weightGrams += $itemWeight * $item['quantity'];
        }

        return [$weightGrams, $usesAssumedWeight];
    }

    private function zone(string $province, string $city): string
    {
        $province = Str::lower(trim($province));
        $city = Str::lower(trim($city));

        $isJakarta = $province === 'dki jakarta' && str_contains($city, 'jakarta');
        $isWestJavaMetro = $province === 'jawa barat'
            && (str_contains($city, 'bekasi') || str_contains($city, 'depok') || str_contains($city, 'bogor'));
        $isBantenMetro = $province === 'banten' && str_contains($city, 'tangerang');

        if ($isJakarta || $isWestJavaMetro || $isBantenMetro) {
            return 'metro';
        }

        if (in_array($province, ['dki jakarta', 'jawa barat', 'banten', 'jawa tengah', 'di yogyakarta', 'jawa timur'], true)) {
            return 'java';
        }

        return 'other';
    }
}
