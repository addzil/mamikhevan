<?php

namespace App\Http\Requests\Admin;

use App\Models\SizeGuideEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SizeGuideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        $sizeGuide = $this->route('sizeGuide');
        $validFields = array_keys(SizeGuideEntry::MEASUREMENT_LABELS);

        return [
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('size_guide_types', 'name')->ignore($sizeGuide?->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('size_guide_types', 'slug')->ignore($sizeGuide?->id),
            ],
            'description' => ['nullable', 'string', 'max:300'],
            'measurement_fields' => ['required', 'array', 'min:1'],
            'measurement_fields.*' => ['string', Rule::in($validFields)],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.label' => ['required', 'string', 'max:20'],
            'entries.*.age_range' => ['nullable', 'string', 'max:40'],
            'entries.*.weight_kg' => ['nullable', 'string', 'max:20'],
            'entries.*.height_cm' => ['nullable', 'string', 'max:20'],
            'entries.*.chest_cm' => ['nullable', 'string', 'max:20'],
            'entries.*.waist_cm' => ['nullable', 'string', 'max:20'],
            'entries.*.hip_cm' => ['nullable', 'string', 'max:20'],
            'entries.*.thigh_cm' => ['nullable', 'string', 'max:20'],
            'entries.*.length_cm' => ['nullable', 'string', 'max:20'],
            'entries.*.sleeve_cm' => ['nullable', 'string', 'max:20'],
            'entries.*.sort_order' => ['integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (filled($this->input('name')) && blank($this->input('slug'))) {
            $this->merge([
                'slug' => str($this->input('name'))->slug()->toString(),
            ]);
        }
    }
}
