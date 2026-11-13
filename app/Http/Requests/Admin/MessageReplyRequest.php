<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MessageReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'reply_subject' => ['required', 'string', 'max:150'],
            'reply_body' => ['required', 'string', 'min:5', 'max:5000'],
        ];
    }
}
