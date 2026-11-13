<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'notification_email' => $this->user()->isAdmin()
                ? ['sometimes', 'nullable', 'string', 'lowercase', 'email', 'max:255']
                : ['prohibited'],
            'current_password' => Rule::when(
                $this->input('email') !== $this->user()->email
                    || ($this->user()->isAdmin() && $this->has('notification_email')
                        && $this->input('notification_email') !== $this->user()->notification_email),
                ['required', 'current_password'],
            ),
        ];
    }
}
