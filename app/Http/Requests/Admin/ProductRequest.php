<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'nullable',
                'string',
                'max:140',
                'alpha_dash',
                Rule::unique('products', 'slug')->ignore($product?->id),
            ],
            'description' => ['required', 'string', 'min:20', 'max:3000'],
            'price' => ['required', 'integer', 'min:1000', 'max:100000000'],
            'cost_price' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'compare_price' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'stock' => [Rule::requiredIf($product === null && count((array) $this->input('sizes', [])) === 0), 'nullable', 'integer', 'min:0', 'max:99999'],
            'size_stocks' => ['nullable', 'array', 'max:20'],
            'size_stocks.*' => ['required', 'integer', 'min:0', 'max:99999'],
            'weight_grams' => ['nullable', 'integer', 'min:1', 'max:30000'],
            'sizes' => ['nullable', 'array', 'max:20'],
            'sizes.*' => ['string', 'max:15', 'distinct'],
            'size_guide_type_id' => ['nullable', 'integer', 'exists:size_guide_types,id'],
            'gender' => ['required', Rule::in(['uniseks', 'laki-laki', 'perempuan'])],
            'age_group' => ['nullable', 'string', 'max:50'],
            'material' => ['nullable', 'string', 'max:100'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'removed_images' => ['nullable', 'array'],
            'removed_images.*' => ['integer'],
            'remove_image' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'cod_available' => ['boolean'],
            'free_shipping' => ['boolean'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty() || $this->route('product') !== null) {
                return;
            }

            $sizes = (array) $this->input('sizes', []);

            if ($sizes !== [] && count((array) $this->input('size_stocks', [])) !== count($sizes)) {
                $validator->errors()->add('size_stocks', 'Masukkan stok untuk setiap ukuran yang dipilih.');
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        $sizes = $this->input('sizes');

        if (is_string($sizes)) {
            $sizes = collect(explode(',', $sizes))
                ->map(fn ($size) => trim($size))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $sizes = is_array($sizes) ? array_values(array_unique($sizes)) : null;

        $this->merge([
            'slug' => filled($this->slug) ? str($this->slug)->slug()->toString() : null,
            'sizes' => $sizes ?: null,
            'size_guide_type_id' => filled($this->input('size_guide_type_id')) ? (int) $this->input('size_guide_type_id') : null,
            'is_featured' => $this->boolean('is_featured'),
            'is_active' => $this->boolean('is_active'),
            'cod_available' => $this->boolean('cod_available'),
            'free_shipping' => $this->boolean('free_shipping'),
        ]);
    }
}
