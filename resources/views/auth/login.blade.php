<x-guest-layout>
    <div class="text-center">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.18em] text-amber-600 ring-1 ring-amber-100">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
            Akun Pembeli
        </span>
        <h1 class="mt-4 font-display text-2xl font-extrabold text-stone-900">Selamat datang kembali</h1>
        <p class="mt-1.5 text-sm font-semibold leading-6 text-stone-400">
            Masuk untuk melihat pesanan dan mengelola profil Anda.
        </p>
    </div>

    <x-auth-session-status class="mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-600 ring-1 ring-emerald-100" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   placeholder="email@contoh.com"
                   class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
            @error('email')
                <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p>
            @enderror
        </div>

        <div x-data="{ show: false }">
            <div class="flex items-center justify-between gap-3">
                <label for="password" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Kata sandi</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-[11px] font-extrabold text-amber-600 transition hover:text-yellow-700">Lupa kata sandi?</a>
                @endif
            </div>
            <div class="relative mt-2">
                <input id="password" :type="show ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password"
                       placeholder="Masukkan kata sandi"
                       class="w-full rounded-xl border-stone-200 py-2.5 pr-11 text-sm focus:border-amber-300 focus:ring-amber-200">
                <button type="button" @click="show = ! show"
                        class="absolute right-2 top-1/2 grid h-8 w-8 -translate-y-1/2 place-items-center rounded-lg text-stone-400 transition hover:bg-stone-100 hover:text-stone-600"
                        :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                    <svg x-show="! show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    <svg x-show="show" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p>
            @enderror
        </div>

        <label for="remember_me" class="flex cursor-pointer items-center gap-2.5">
            <input id="remember_me" type="checkbox" name="remember"
                   class="rounded border-stone-300 text-amber-600 focus:ring-amber-200">
            <span class="text-xs font-bold text-stone-500">Ingat saya di perangkat ini</span>
        </label>

        <button type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-amber-500/25 transition hover:bg-amber-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
            Masuk ke akun
        </button>
    </form>

    <p class="mt-5 text-center text-sm text-stone-500">Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-amber-600 hover:underline">Daftar sebagai pembeli</a></p>
    <p class="mt-5 text-center text-[10px] font-semibold text-stone-300"><a href="{{ route('admin.login') }}" class="transition hover:text-stone-500">Akses staf</a></p>

    @if (app()->environment('local'))
        <div class="mt-6 rounded-2xl border border-dashed border-yellow-200 bg-yellow-50/70 p-4">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-yellow-500">Akun demo lokal</p>
            <div class="mt-2 space-y-1 text-[11px] font-bold text-stone-600">
                <p>Email: <span class="font-extrabold text-stone-800">admin@mamikhevanshop.id</span></p>
                <p>Kata sandi: <span class="font-extrabold text-stone-800">Admin#2026</span></p>
            </div>
        </div>
    @endif
</x-guest-layout>
