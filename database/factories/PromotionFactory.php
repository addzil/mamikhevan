<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'event_name' => null,
            'voucher_code' => null,
            'type' => Promotion::TYPE_PRICE,
            'target_type' => Promotion::TARGET_ALL,
            'discount_kind' => Promotion::KIND_PERCENT,
            'discount_value' => 10,
            'tiers' => null,
            'minimum_subtotal' => 0,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'is_active' => true,
        ];
    }
}
