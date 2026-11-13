<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentSettingController extends Controller
{
    public function edit(): View
    {
        $settings = PaymentSetting::query()->first() ?? new PaymentSetting([
            'bank_accounts' => [],
            'qris_enabled' => false,
        ]);
        $settings->whatsapp_numbers ??= $settings->whatsapp_number
            ? [['phone' => $settings->whatsapp_number, 'is_active' => true]]
            : [];

        return view('admin.payment-settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'whatsapp_numbers' => ['nullable', 'array', 'max:10'],
            'whatsapp_numbers.*.phone' => ['required', 'string', 'max:25', 'regex:/^(\+?62|0)[0-9]{8,13}$/'],
            'whatsapp_numbers.*.is_active' => ['nullable', 'boolean'],
            'bank_accounts' => ['nullable', 'array', 'max:10'],
            'bank_accounts.*.bank' => ['required', 'string', 'max:60'],
            'bank_accounts.*.account_name' => ['required', 'string', 'max:100'],
            'bank_accounts.*.account_number' => ['required', 'string', 'max:40'],
            'bank_accounts.*.is_active' => ['nullable', 'boolean'],
            'qris_enabled' => ['nullable', 'boolean'],
            'qris_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_qris_image' => ['nullable', 'boolean'],
        ]);

        $settings = PaymentSetting::query()->first() ?? new PaymentSetting;
        $oldQrisImage = $settings->qris_image;
        $qrisImage = $request->boolean('remove_qris_image') ? null : $oldQrisImage;

        if ($request->hasFile('qris_image')) {
            $qrisImage = $request->file('qris_image')->store('payment', 'public');
        }

        $bankAccounts = collect($validated['bank_accounts'] ?? [])
            ->map(fn (array $account) => [
                'bank' => trim($account['bank']),
                'account_name' => trim($account['account_name']),
                'account_number' => trim($account['account_number']),
                'is_active' => (bool) ($account['is_active'] ?? false),
            ])
            ->values()
            ->all();
        $whatsappNumbers = collect($validated['whatsapp_numbers'] ?? [])
            ->map(fn (array $admin) => [
                'phone' => preg_replace('/\D/', '', $admin['phone']),
                'is_active' => (bool) ($admin['is_active'] ?? false),
            ])
            ->values()
            ->all();
        $primaryWhatsappNumber = collect($whatsappNumbers)
            ->first(fn (array $admin) => $admin['is_active'])['phone'] ?? null;

        $settings->fill([
            'bank_accounts' => $bankAccounts,
            'qris_image' => $qrisImage,
            'qris_enabled' => $request->boolean('qris_enabled') && $qrisImage !== null,
            'whatsapp_numbers' => $whatsappNumbers,
            'whatsapp_number' => $primaryWhatsappNumber,
        ])->save();

        if ($oldQrisImage && $oldQrisImage !== $qrisImage
            && ! Order::query()->where('payment_destination->image', $oldQrisImage)->exists()) {
            Storage::disk('public')->delete($oldQrisImage);
        }

        return back()->with('success', 'Pengaturan pembayaran berhasil disimpan.');
    }
}
