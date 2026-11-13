<?php

namespace App\Http\Requests\Admin;

use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'reviewer_name' => ['required', 'string', 'max:100'],
            'reviewer_city' => ['nullable', 'string', 'max:100'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'product_quality' => ['nullable', 'integer', 'between:1,5'],
            'shipping_speed' => ['nullable', 'integer', 'between:1,5'],
            'service' => ['nullable', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'source' => ['required', Rule::in(Review::SOURCES)],
            'is_visible' => ['sometimes', 'boolean'],
            'reviewed_at' => ['nullable', 'date'],
            'admin_reply' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
