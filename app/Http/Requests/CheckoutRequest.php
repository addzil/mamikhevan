<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'max:25', 'regex:/^(\+62|62|0)[0-9]{8,13}$/'],
            'customer_email' => ['nullable', 'string', 'lowercase', 'email', 'max:120'],
            'customer_address' => ['required', 'string', 'min:15', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', Rule::in([Order::PAYMENT_TRANSFER, Order::PAYMENT_COD])],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_phone.regex' => 'Nomor telepon harus diawali 08, 62, atau +62.',
            'customer_address.min' => 'Alamat lengkap minimal 15 karakter agar kurir mudah menemukan lokasi.',
        ];
    }
}
