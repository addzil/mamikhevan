<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
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
            'stock' => ['required', 'integer', 'min:0', 'max:99999'],
            'sizes' => ['nullable', 'array'],
            'sizes.*' => ['string', 'max:15'],
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
        ];
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

        $this->merge([
            'slug' => filled($this->slug) ? str($this->slug)->slug()->toString() : null,
            'sizes' => $sizes ?: null,
            'size_guide_type_id' => filled($this->input('size_guide_type_id')) ? (int) $this->input('size_guide_type_id') : null,
            'is_featured' => $this->boolean('is_featured'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
