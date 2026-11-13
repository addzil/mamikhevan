<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Promotion;

class PromotionService
{
    /**
     * @param  array<int|string, array{product: Product, quantity: int}>  $items
     * @return array{promotion: Promotion, product_discount: int, shipping_discount: int, gift_product: ?Product, gift_quantity: int, gift_unit_price: int, voucher_code: ?string, tier_min_quantity: ?int, savings: int}|null
     */
    public function bestFor(array $items, int $shippingCost, ?string $voucherCode = null): ?array
    {
        $subtotal = (int) collect($items)->sum(fn (array $item) => $item['product']->price * $item['quantity']);
        $now = now();
        $promotions = Promotion::query()
            ->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->with('giftProduct')
            ->orderBy('id')
            ->get();

        $best = null;

        foreach ($promotions as $promotion) {
            if ($promotion->type === Promotion::TYPE_GIVEAWAY) {
                continue;
            }

            if ($promotion->hasVoucher() && ! $promotion->matchesVoucher($voucherCode)) {
                continue;
            }

            if ($subtotal < $promotion->minimum_subtotal) {
                continue;
            }

            $eligibleItems = collect($items)->filter(fn (array $item) => match ($promotion->target_type) {
                Promotion::TARGET_ALL => true,
                Promotion::TARGET_PRODUCT => $item['product']->id === $promotion->product_id,
                Promotion::TARGET_CATEGORY => $item['product']->category_id === $promotion->category_id,
                default => false,
            });

            if ($eligibleItems->isEmpty()) {
                continue;
            }

            $eligibleSubtotal = (int) $eligibleItems->sum(fn (array $item) => $item['product']->price * $item['quantity']);
            $eligibleQuantity = (int) $eligibleItems->sum('quantity');
            $productDiscount = 0;
            $shippingDiscount = 0;
            $giftProduct = null;
            $giftQuantity = 0;
            $giftUnitPrice = 0;
            $tierMinQuantity = null;

            if ($promotion->type === Promotion::TYPE_PRICE) {
                $productDiscount = $this->discount($eligibleSubtotal, $promotion);
            } elseif ($promotion->type === Promotion::TYPE_SHIPPING_DISCOUNT) {
                $shippingDiscount = $this->discount($shippingCost, $promotion);
            } elseif ($promotion->type === Promotion::TYPE_SHIPPING_SUBSIDY) {
                $shippingDiscount = min($shippingCost, $promotion->discount_value);
            } elseif ($promotion->type === Promotion::TYPE_QUANTITY_TIER) {
                $tier = $this->applicableTier($promotion, $eligibleQuantity);

                if ($tier === null) {
                    continue;
                }

                $productDiscount = $this->tierDiscount($eligibleSubtotal, $tier);
                $tierMinQuantity = (int) $tier['min_quantity'];
            } elseif ($promotion->type === Promotion::TYPE_BUY_GET) {
                $giftProduct = $promotion->giftProduct;

                if ($giftProduct === null || ! $giftProduct->is_active || ! $promotion->buy_quantity || ! $promotion->gift_quantity) {
                    continue;
                }

                $giftQuantity = intdiv($eligibleQuantity, $promotion->buy_quantity) * $promotion->gift_quantity;
                $giftSize = $promotion->gift_size;
                $purchasedGiftQuantity = (int) collect($items)
                    ->filter(fn (array $item): bool => $item['product']->id === $giftProduct->id
                        && ($item['size'] ?? null) === $giftSize)
                    ->sum('quantity');

                if ($giftQuantity < 1 || $giftProduct->stockForSize($giftSize) < $purchasedGiftQuantity + $giftQuantity) {
                    continue;
                }

                $giftUnitPrice = $promotion->gift_unit_price ?? $giftProduct->price;
            } else {
                continue;
            }

            $savings = $productDiscount + $shippingDiscount + ($giftQuantity * $giftUnitPrice);

            if ($savings > 0 && ($best === null || $savings > $best['savings'])) {
                $best = [
                    'promotion' => $promotion,
                    'product_discount' => $productDiscount,
                    'shipping_discount' => $shippingDiscount,
                    'gift_product' => $giftProduct,
                    'gift_quantity' => $giftQuantity,
                    'gift_unit_price' => $giftUnitPrice,
                    'voucher_code' => $promotion->voucher_code,
                    'tier_min_quantity' => $tierMinQuantity,
                    'savings' => $savings,
                ];
            }
        }

        return $best;
    }

    public function activeVoucher(?string $voucherCode): ?Promotion
    {
        if (blank($voucherCode)) {
            return null;
        }

        $normalized = mb_strtoupper(trim($voucherCode));
        $now = now();

        return Promotion::query()
            ->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->whereRaw('UPPER(voucher_code) = ?', [$normalized])
            ->first();
    }

    /**
     * @return array{promotion: Promotion, gift_product: Product, gift_quantity: int, gift_unit_price: int, savings: int}|null
     */
    public function giveawayFor(?string $voucherCode): ?array
    {
        $promotion = $this->activeVoucher($voucherCode);

        if ($promotion === null || $promotion->type !== Promotion::TYPE_GIVEAWAY) {
            return null;
        }

        $giftProduct = $promotion->giftProduct;

        if ($giftProduct === null || ! $giftProduct->is_active || ! $promotion->gift_quantity) {
            return null;
        }

        $giftQuantity = $promotion->gift_quantity;

        if ($giftProduct->stockForSize($promotion->gift_size) < $giftQuantity) {
            return null;
        }

        $giftUnitPrice = $promotion->gift_unit_price ?? $giftProduct->price;

        return [
            'promotion' => $promotion,
            'gift_product' => $giftProduct,
            'gift_quantity' => $giftQuantity,
            'gift_unit_price' => $giftUnitPrice,
            'savings' => $giftQuantity * $giftUnitPrice,
        ];
    }

    private function discount(int $amount, Promotion $promotion): int
    {
        $discount = $promotion->discount_kind === Promotion::KIND_PERCENT
            ? intdiv($amount * $promotion->discount_value, 100)
            : $promotion->discount_value;

        if ($promotion->maximum_discount !== null) {
            $discount = min($discount, $promotion->maximum_discount);
        }

        return min($amount, $discount);
    }

    /**
     * @return array{min_quantity: int, discount_kind: string, discount_value: int, maximum_discount?: int|null}|null
     */
    private function applicableTier(Promotion $promotion, int $quantity): ?array
    {
        $tiers = collect($promotion->tiers ?? [])
            ->filter(fn (mixed $tier) => is_array($tier) && isset($tier['min_quantity'], $tier['discount_value']))
            ->sortByDesc(fn (array $tier) => (int) $tier['min_quantity']);

        $tier = $tiers->first(fn (array $tier) => $quantity >= (int) $tier['min_quantity']);

        return $tier === null ? null : $tier;
    }

    /**
     * @param  array{min_quantity: int, discount_kind: string, discount_value: int, maximum_discount?: int|null}  $tier
     */
    private function tierDiscount(int $amount, array $tier): int
    {
        $kind = $tier['discount_kind'] ?? Promotion::KIND_PERCENT;
        $value = (int) $tier['discount_value'];

        $discount = $kind === Promotion::KIND_PERCENT
            ? intdiv($amount * $value, 100)
            : $value;

        $maximum = $tier['maximum_discount'] ?? null;

        if ($maximum !== null && $maximum !== '') {
            $discount = min($discount, (int) $maximum);
        }

        return min($amount, $discount);
    }
}
