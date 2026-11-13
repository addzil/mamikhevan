<x-guest-layout>
    <div class="text-center">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-stone-100 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.18em] text-stone-600 ring-1 ring-stone-200">
            Akses staf
        </span>
        <h1 class="mt-4 font-display text-2xl font-extrabold text-stone-900">Masuk panel admin</h1>
        <p class="mt-1.5 text-sm font-semibold leading-6 text-stone-400">Gunakan akun Administrator, Manajemen, atau Operator.</p>
    </div>

    <x-auth-session-status class="mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-600 ring-1 ring-emerald-100" :status="session('status')" />

    <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Email staf</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   placeholder="email@perusahaan.com"
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
                    <span x-text="show ? 'Sembunyikan' : 'Lihat'" class="text-[10px] font-bold"></span>
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p>
            @enderror
        </div>

        <label for="remember_me" class="flex cursor-pointer items-center gap-2.5">
            <input id="remember_me" type="checkbox" name="remember" class="rounded border-stone-300 text-amber-600 focus:ring-amber-200">
            <span class="text-xs font-bold text-stone-500">Ingat saya di perangkat ini</span>
        </label>

        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-stone-900 px-4 py-3 text-sm font-extrabold text-white shadow-lg transition hover:bg-stone-800">
            Masuk ke panel admin
        </button>
    </form>

    <p class="mt-5 text-center text-xs font-semibold text-stone-400">Akun pelanggan? <a href="{{ route('login') }}" class="font-extrabold text-amber-600 hover:underline">Masuk di sini</a></p>
</x-guest-layout>
