<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(User::ROLE_ADMINISTRATOR) ?? false;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $isCreating = $this->isMethod('post');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user)],
            'role' => ['required', Rule::in([
                User::ROLE_ADMINISTRATOR,
                User::ROLE_MANAGEMENT,
                User::ROLE_OPERATOR,
                User::ROLE_CUSTOMER,
            ])],
            'password' => $isCreating
                ? ['required', 'string', 'min:12', 'confirmed']
                : ['nullable', 'string', 'min:12', 'confirmed'],
        ];
    }
}
