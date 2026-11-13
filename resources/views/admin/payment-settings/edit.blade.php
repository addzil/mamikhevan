@extends('layouts.admin')

@section('title', 'Pengaturan Pembayaran')
@section('heading', 'Pembayaran')
@section('subheading', 'Kelola rekening, QRIS, dan nomor WhatsApp untuk konfirmasi pembayaran.')

@section('content')
    @php
        $savedAccounts = $settings->bank_accounts ?? [];
        $savedAdmins = $settings->whatsapp_numbers ?? [];
        if ($savedAdmins === [] && filled($settings->whatsapp_number)) {
            $savedAdmins = [['phone' => $settings->whatsapp_number, 'is_active' => true]];
        }
    @endphp

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
            <p class="font-extrabold">Pengaturan belum tersimpan. Periksa isian berikut:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="mb-6 rounded-3xl border border-amber-200 bg-amber-50/60 p-6">
        <h2 class="font-display text-lg font-bold text-stone-900">Record tersimpan</h2>
        <p class="mt-1 text-xs leading-5 text-stone-500">Ringkasan data pembayaran yang saat ini tersimpan di master.</p>

        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-stone-200 bg-white p-4">
                <h3 class="text-sm font-extrabold text-stone-800">Rekening bank</h3>
                @forelse ($savedAccounts as $account)
                    <div class="mt-3 rounded-xl bg-stone-50 p-3 text-xs text-stone-700">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-bold">{{ $account['bank'] ?? 'Bank' }}</p>
                            <span @class([
                                'shrink-0 rounded-full px-2 py-1 text-[10px] font-extrabold',
                                'bg-emerald-100 text-emerald-700' => $account['is_active'] ?? false,
                                'bg-stone-200 text-stone-600' => ! ($account['is_active'] ?? false),
                            ])>{{ ($account['is_active'] ?? false) ? 'Tampil' : 'Disembunyikan' }}</span>
                        </div>
                        <p class="mt-1">{{ $account['account_name'] ?? '—' }}</p>
                        <p class="mt-1 font-mono font-bold">{{ $account['account_number'] ?? '—' }}</p>
                    </div>
                @empty
                    <p class="mt-3 text-xs text-stone-500">Belum ada rekening tersimpan.</p>
                @endforelse
            </div>

            <div class="rounded-2xl border border-stone-200 bg-white p-4">
                <h3 class="text-sm font-extrabold text-stone-800">QRIS</h3>
                @if ($settings->qris_image)
                    <div class="mt-3 flex items-start justify-between gap-2">
                        <p class="text-xs text-stone-600">Gambar QRIS tersimpan</p>
                        <span @class([
                            'shrink-0 rounded-full px-2 py-1 text-[10px] font-extrabold',
                            'bg-emerald-100 text-emerald-700' => $settings->qris_enabled,
                            'bg-stone-200 text-stone-600' => ! $settings->qris_enabled,
                        ])>{{ $settings->qris_enabled ? 'Tampil' : 'Disembunyikan' }}</span>
                    </div>
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings->qris_image) }}" alt="QRIS tersimpan" class="mt-3 max-h-48 rounded-xl border border-stone-200">
                @else
                    <p class="mt-3 text-xs text-stone-500">Belum ada gambar QRIS tersimpan.</p>
                @endif
            </div>

            <div class="rounded-2xl border border-stone-200 bg-white p-4">
                <h3 class="text-sm font-extrabold text-stone-800">Nomor WhatsApp admin</h3>
                @forelse ($savedAdmins as $index => $admin)
                    <div class="mt-3 flex items-center justify-between gap-2 rounded-xl bg-stone-50 p-3 text-xs">
                        <div>
                            <p class="font-bold text-stone-700">Admin {{ $index + 1 }}</p>
                            <p class="mt-1 font-mono text-stone-600">{{ $admin['phone'] ?? '—' }}</p>
                        </div>
                        <span @class([
                            'shrink-0 rounded-full px-2 py-1 text-[10px] font-extrabold',
                            'bg-emerald-100 text-emerald-700' => $admin['is_active'] ?? false,
                            'bg-stone-200 text-stone-600' => ! ($admin['is_active'] ?? false),
                        ])>{{ ($admin['is_active'] ?? false) ? 'Tampil' : 'Disembunyikan' }}</span>
                    </div>
                @empty
                    <p class="mt-3 text-xs text-stone-500">Belum ada nomor admin tersimpan.</p>
                @endforelse
            </div>
        </div>
    </section>

    <form action="{{ route('admin.payment-settings.update') }}" method="POST" enctype="multipart/form-data"
          x-data="{ accounts: @js(old('bank_accounts', $settings->bank_accounts ?? [])), admins: @js(old('whatsapp_numbers', $settings->whatsapp_numbers ?? [])), addAccount() { this.accounts.push({ bank: '', account_name: '', account_number: '', is_active: true }); }, addAdmin() { this.admins.push({ phone: '', is_active: true }); } }"
          class="space-y-6">
        @csrf
        @method('PUT')

        <section class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-display text-lg font-bold text-stone-900">Rekening bank</h2>
            <p class="mt-1 text-xs leading-5 text-stone-500">Tambahkan beberapa rekening. Hanya rekening yang diaktifkan yang tampil di halaman pesanan.</p>

            <div class="mt-5 space-y-4">
                <template x-for="(account, index) in accounts" :key="index">
                    <div class="rounded-2xl border border-stone-200 p-4">
                        <div class="grid gap-4 sm:grid-cols-3">
                            <label class="text-xs font-bold text-stone-600">
                                Nama bank
                                <input type="text" :name="'bank_accounts[' + index + '][bank]'" x-model="account.bank" maxlength="60" required class="mt-2 w-full rounded-xl border-stone-200 text-sm">
                            </label>
                            <label class="text-xs font-bold text-stone-600">
                                Nama pemilik rekening
                                <input type="text" :name="'bank_accounts[' + index + '][account_name]'" x-model="account.account_name" maxlength="100" required class="mt-2 w-full rounded-xl border-stone-200 text-sm">
                            </label>
                            <label class="text-xs font-bold text-stone-600">
                                Nomor rekening
                                <input type="text" :name="'bank_accounts[' + index + '][account_number]'" x-model="account.account_number" maxlength="40" required class="mt-2 w-full rounded-xl border-stone-200 text-sm">
                            </label>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <label class="inline-flex items-center gap-2 text-xs font-bold text-stone-600">
                                <input type="checkbox" :name="'bank_accounts[' + index + '][is_active]'" value="1" x-model="account.is_active" class="rounded border-stone-300 text-amber-500 focus:ring-amber-200">
                                Tampilkan kepada pembeli
                            </label>
                            <button type="button" @click="accounts.splice(index, 1)" class="text-xs font-bold text-red-600 hover:underline">Hapus rekening</button>
                        </div>
                    </div>
                </template>
            </div>

            @error('bank_accounts.*.bank') <p class="mt-3 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
            @error('bank_accounts.*.account_name') <p class="mt-3 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
            @error('bank_accounts.*.account_number') <p class="mt-3 text-xs font-bold text-red-600">{{ $message }}</p> @enderror

            <button type="button" @click="addAccount()" class="mt-4 rounded-xl border border-amber-200 px-4 py-2.5 text-xs font-extrabold text-amber-700 hover:bg-amber-50">
                + Tambah rekening
            </button>
        </section>

        <section class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-display text-lg font-bold text-stone-900">QRIS</h2>
            <label class="mt-4 block text-xs font-bold text-stone-600">
                Gambar QRIS (JPG, PNG, atau WebP; maks. 3 MB)
                <input type="file" name="qris_image" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm">
            </label>
            @error('qris_image') <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p> @enderror

            @if ($settings->qris_image)
                <div class="mt-4 flex flex-wrap items-center gap-4">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings->qris_image) }}" alt="QRIS saat ini" class="max-h-48 rounded-xl border border-stone-200">
                    <label class="inline-flex items-center gap-2 text-xs font-bold text-stone-600">
                        <input type="checkbox" name="remove_qris_image" value="1" class="rounded border-stone-300 text-amber-500 focus:ring-amber-200">
                        Hapus gambar QRIS
                    </label>
                </div>
            @endif

            <label class="mt-4 inline-flex items-center gap-2 text-xs font-bold text-stone-600">
                <input type="hidden" name="qris_enabled" value="0">
                <input type="checkbox" name="qris_enabled" value="1" @checked(old('qris_enabled', $settings->qris_enabled)) class="rounded border-stone-300 text-amber-500 focus:ring-amber-200">
                Tampilkan QRIS kepada pembeli
            </label>
        </section>

        <section class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-display text-lg font-bold text-stone-900">WhatsApp admin</h2>
            <p class="mt-1 text-xs leading-5 text-stone-500">Nomor aktif akan muncul sebagai pilihan Admin 1, Admin 2, dan seterusnya pada halaman pesanan.</p>
            <div class="mt-4 space-y-3">
                <template x-for="(admin, index) in admins" :key="index">
                    <div class="flex flex-wrap items-end gap-3 rounded-2xl border border-stone-200 p-4">
                        <label class="flex-1 text-xs font-bold text-stone-600">
                            <span x-text="'Nomor Admin ' + (index + 1)"></span>
                            <input type="tel" :name="'whatsapp_numbers[' + index + '][phone]'" x-model="admin.phone" placeholder="6281234567890" maxlength="25" required class="mt-2 w-full rounded-xl border-stone-200 text-sm">
                        </label>
                        <label class="inline-flex items-center gap-2 pb-2 text-xs font-bold text-stone-600">
                            <input type="checkbox" :name="'whatsapp_numbers[' + index + '][is_active]'" value="1" x-model="admin.is_active" class="rounded border-stone-300 text-amber-500 focus:ring-amber-200">
                            Tampilkan
                        </label>
                        <button type="button" @click="admins.splice(index, 1)" class="pb-2 text-xs font-bold text-red-600 hover:underline">Hapus</button>
                    </div>
                </template>
            </div>
            @error('whatsapp_numbers.*.phone') <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
            <button type="button" @click="addAdmin()" class="mt-4 rounded-xl border border-amber-200 px-4 py-2.5 text-xs font-extrabold text-amber-700 hover:bg-amber-50">+ Tambah nomor admin</button>
            <p class="mt-2 text-xs text-stone-400">Gunakan format 62xxxxxxxxxx atau 08xxxxxxxxxx. Pesan konfirmasi menyertakan kode pesanan dan total transfer.</p>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="rounded-xl bg-amber-500 px-5 py-3 text-sm font-extrabold text-white shadow-sm hover:bg-amber-600">Simpan pengaturan</button>
        </div>
    </form>
@endsection
