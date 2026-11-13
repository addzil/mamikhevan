<x-guest-layout>
    <div class="text-center">
        <h1 class="font-display text-2xl font-extrabold text-stone-900">Buat akun pembeli</h1>
        <p class="mt-2 text-sm text-stone-500">Simpan riwayat pesanan dan kelola profil Anda.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="name" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Nama lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
            @error('name') <p class="mt-1 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                   class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
            @error('email') <p class="mt-1 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Kata sandi</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
            @error('password') <p class="mt-1 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Ulangi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="mt-2 w-full rounded-xl border-stone-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
        </div>

        <button type="submit" class="w-full rounded-xl bg-amber-500 px-4 py-3 text-sm font-extrabold text-white hover:bg-amber-600">Daftar</button>
    </form>

    <p class="mt-5 text-center text-sm text-stone-500">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-amber-600 hover:underline">Masuk</a></p>
</x-guest-layout>
