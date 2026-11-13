<x-guest-layout>
    <h1 class="font-display text-2xl font-extrabold text-stone-900">Lupa kata sandi?</h1>
    <div class="mb-4 text-sm text-gray-600">
        Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Kirim tautan reset
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
