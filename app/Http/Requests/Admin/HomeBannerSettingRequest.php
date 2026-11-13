<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class HomeBannerSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'eyebrow' => ['required', 'string', 'max:80'],
            'headline' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:500'],
            'design' => ['required', 'in:collage,single,stacked'],
            'background' => ['required', 'in:warm,mint,lavender'],
            'is_active' => ['required', 'boolean'],
            'cards' => ['required', 'array', 'size:3'],
            'cards.*.label' => ['nullable', 'string', 'max:40'],
            'cards.*.product_id' => ['nullable', 'integer', 'distinct', 'exists:products,id'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $selectedProducts = collect($this->input('cards', []))
                ->filter(fn (array $card): bool => filled($card['product_id'] ?? null));

            if ($selectedProducts->isEmpty()) {
                $validator->errors()->add('cards', 'Pilih minimal satu produk untuk banner.');
            }
        }];
    }
}
