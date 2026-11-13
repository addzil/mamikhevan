<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'event_name' => ['nullable', 'string', 'max:120'],
            'voucher_code' => [
                'required_if:type,giveaway', 'nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('promotions', 'voucher_code')->ignore($this->route('promotion')),
            ],
            'type' => ['required', Rule::in(Promotion::TYPES)],
            'target_type' => ['required', Rule::in(Promotion::TARGETS)],
            'product_id' => ['required_if:target_type,product', 'nullable', 'integer', 'exists:products,id'],
            'category_id' => ['required_if:target_type,category', 'nullable', 'integer', 'exists:categories,id'],
            'discount_kind' => ['required_if:type,price,shipping_discount', 'nullable', Rule::in([Promotion::KIND_PERCENT, Promotion::KIND_FIXED])],
            'discount_value' => ['required_if:type,price,shipping_discount,shipping_subsidy', 'nullable', 'integer', 'min:1', 'max:1000000000'],
            'maximum_discount' => ['nullable', 'integer', 'min:1', 'max:1000000000'],
            'minimum_subtotal' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'buy_quantity' => ['required_if:type,buy_get', 'nullable', 'integer', 'min:1', 'max:99'],
            'gift_product_id' => ['required_if:type,buy_get,giveaway', 'nullable', 'integer', 'exists:products,id'],
            'gift_size' => ['nullable', 'string', 'max:20'],
            'gift_quantity' => ['required_if:type,buy_get,giveaway', 'nullable', 'integer', 'min:1', 'max:99'],
            'gift_unit_price' => ['required_if:type,buy_get,giveaway', 'nullable', 'integer', 'min:1', 'max:1000000000'],
            'tiers' => ['required_if:type,quantity_tier', 'nullable', 'array', 'min:1', 'max:10'],
            'tiers.*.min_quantity' => ['required_if:type,quantity_tier', 'nullable', 'integer', 'min:1', 'max:999'],
            'tiers.*.discount_kind' => ['required_if:type,quantity_tier', 'nullable', Rule::in([Promotion::KIND_PERCENT, Promotion::KIND_FIXED])],
            'tiers.*.discount_value' => ['required_if:type,quantity_tier', 'nullable', 'integer', 'min:1', 'max:1000000000'],
            'tiers.*.maximum_discount' => ['nullable', 'integer', 'min:1', 'max:1000000000'],
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'ends_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:starts_at'],
            'is_active' => ['boolean'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (in_array($this->input('type'), [Promotion::TYPE_PRICE, Promotion::TYPE_SHIPPING_DISCOUNT], true)
                && $this->input('discount_kind') === Promotion::KIND_PERCENT
                && (int) $this->input('discount_value') > 100) {
                $validator->errors()->add('discount_value', 'Persentase diskon maksimal 100%.');
            }

            if ($this->input('type') === Promotion::TYPE_QUANTITY_TIER) {
                $this->validateTiers($validator);
            }

            if (! in_array($this->input('type'), [Promotion::TYPE_BUY_GET, Promotion::TYPE_GIVEAWAY], true)) {
                return;
            }

            $giftProduct = Product::query()->find($this->integer('gift_product_id'));
            $sizes = $giftProduct?->sizeList() ?? [];
            $giftSize = $this->input('gift_size');

            if ($sizes !== [] && ! in_array($giftSize, $sizes, true)) {
                $validator->errors()->add('gift_size', 'Pilih ukuran hadiah yang tersedia pada produk hadiah.');
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        $voucherCode = $this->input('voucher_code');

        $tiers = $this->input('tiers');
        if (is_array($tiers)) {
            $tiers = array_values(array_filter($tiers, function (mixed $tier): bool {
                return is_array($tier)
                    && (filled($tier['min_quantity'] ?? null) || filled($tier['discount_value'] ?? null));
            }));
        }

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'voucher_code' => filled($voucherCode) ? mb_strtoupper(trim((string) $voucherCode)) : null,
            'tiers' => is_array($tiers) ? $tiers : null,
        ]);

        if ($this->input('type') === Promotion::TYPE_GIVEAWAY) {
            $this->merge([
                'target_type' => Promotion::TARGET_ALL,
                'product_id' => null,
                'category_id' => null,
            ]);
        }
    }

    private function validateTiers(Validator $validator): void
    {
        $tiers = collect($this->input('tiers') ?? []);

        $minimumQuantities = $tiers->map(fn (array $tier) => (int) ($tier['min_quantity'] ?? 0));

        if ($minimumQuantities->duplicates()->isNotEmpty()) {
            $validator->errors()->add('tiers', 'Setiap tingkat jumlah item harus memiliki minimal jumlah yang berbeda.');
        }

        foreach ($tiers as $index => $tier) {
            if (($tier['discount_kind'] ?? null) === Promotion::KIND_PERCENT
                && (int) ($tier['discount_value'] ?? 0) > 100) {
                $validator->errors()->add("tiers.{$index}.discount_value", 'Persentase diskon maksimal 100%.');
            }
        }
    }
}
