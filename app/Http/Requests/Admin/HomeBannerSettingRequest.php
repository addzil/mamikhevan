<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'cards.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
        ];
    }
}
