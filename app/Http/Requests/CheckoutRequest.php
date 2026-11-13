<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Services\KomerceShippingCostService;
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
            'customer_email' => ['required', 'string', 'lowercase', 'email', 'max:120'],
            'customer_address' => ['required', 'string', 'min:15', 'max:500'],
            'shipping_province' => ['required', 'string', 'max:60'],
            'shipping_city' => ['required', 'string', 'max:100'],
            'shipping_district' => ['required', 'string', 'max:100'],
            'shipping_subdistrict' => ['required', 'string', 'max:100'],
            'shipping_postal_code' => ['required', 'digits:5'],
            'shipping_destination_id' => [app(KomerceShippingCostService::class)->enabled() ? 'required' : 'nullable', 'integer', 'min:1'],
            'shipping_courier' => ['required', Rule::in([Order::COURIER_JNE, Order::COURIER_JNT, Order::COURIER_GOSEND])],
            'voucher_code' => ['nullable', 'string', 'max:40'],
            'note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', Rule::in([Order::PAYMENT_TRANSFER, Order::PAYMENT_COD])],
            'payment_destination' => ['required_if:payment_method,transfer', 'nullable', 'string', 'max:80'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_email.required' => 'Masukkan email agar kami dapat mengirim konfirmasi pesanan.',
            'customer_phone.regex' => 'Nomor telepon harus diawali 08, 62, atau +62.',
            'customer_address.min' => 'Alamat lengkap minimal 15 karakter agar kurir mudah menemukan lokasi.',
            'shipping_postal_code.digits' => 'Kode pos harus terdiri dari 5 angka.',
            'payment_destination.required_if' => 'Pilih satu rekening atau QRIS untuk pembayaran.',
        ];
    }
}
