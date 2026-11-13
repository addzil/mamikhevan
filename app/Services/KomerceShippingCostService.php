<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class KomerceShippingCostService
{
    private const BASE_URL = 'https://rajaongkir.komerce.id/api/v1';

    public function enabled(): bool
    {
        return filled(config('services.komerce.shipping_cost_key'))
            && filter_var(config('shipping.komerce_origin_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
    }

    /**
     * @return array<int, array{id: int, label: string, province_name: string, city_name: string, district_name: string, subdistrict_name: string, zip_code: string}>
     */
    public function searchDestinations(string $keyword): array
    {
        $response = $this->client()->get(self::BASE_URL.'/destination/domestic-destination', [
            'search' => $keyword,
            'limit' => 10,
            'offset' => 0,
        ]);

        if ($response->notFound()) {
            return [];
        }

        if (! $response->successful() || $response->json('meta.status', 'success') !== 'success' || ! is_array($response->json('data'))) {
            throw new RuntimeException('Pencarian wilayah Komerce sedang tidak tersedia. Coba lagi.');
        }

        return collect($response->json('data'))
            ->filter(fn ($row) => is_array($row) && filter_var($row['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false)
            ->map(fn ($row) => [
                'id' => (int) $row['id'],
                'label' => (string) ($row['label'] ?? ''),
                'province_name' => (string) ($row['province_name'] ?? ''),
                'city_name' => (string) ($row['city_name'] ?? ''),
                'district_name' => (string) ($row['district_name'] ?? ''),
                'subdistrict_name' => (string) ($row['subdistrict_name'] ?? ''),
                'zip_code' => (string) ($row['zip_code'] ?? ''),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{code: string, service: string, cost: int, etd: string}>
     */
    public function calculate(int $destinationId, int $weightGrams): array
    {
        $response = $this->client()->asForm()->post(self::BASE_URL.'/calculate/domestic-cost', [
            'origin' => (int) config('shipping.komerce_origin_id'),
            'destination' => $destinationId,
            'weight' => $weightGrams,
            'courier' => 'jne:jnt',
        ]);

        if (! $response->successful() || $response->json('meta.status', 'success') !== 'success' || ! is_array($response->json('data'))) {
            throw new RuntimeException('Tarif Komerce sedang tidak tersedia. Coba lagi.');
        }

        return collect($response->json('data'))
            ->filter(fn ($row) => is_array($row)
                && in_array(strtolower((string) ($row['code'] ?? '')), ['jne', 'jnt'], true)
                && trim((string) ($row['service'] ?? '')) !== ''
                && filter_var($row['cost'] ?? null, FILTER_VALIDATE_INT) !== false
                && (int) $row['cost'] >= 0)
            ->map(fn ($row) => [
                'code' => strtolower((string) $row['code']),
                'service' => (string) ($row['service'] ?? ''),
                'cost' => (int) $row['cost'],
                'etd' => (string) ($row['etd'] ?? ''),
            ])
            ->values()
            ->all();
    }

    private function client(): PendingRequest
    {
        return Http::withHeaders(['key' => (string) config('services.komerce.shipping_cost_key')])
            ->connectTimeout(3)
            ->timeout(8);
    }
}
