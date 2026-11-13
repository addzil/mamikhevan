<x-guest-layout>
    <div class="text-center">
        <h1 class="font-display text-2xl font-extrabold text-stone-900">Verifikasi email Anda</h1>
        <p class="mt-3 text-sm leading-6 text-stone-600">
            Kami mengirim tautan konfirmasi ke <strong>{{ auth()->user()->email }}</strong>.
            Buka email tersebut dan klik tautannya untuk mengaktifkan akun.
        </p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            Tautan verifikasi baru telah dikirim ke alamat email Anda.
        </div>
    @endif

    <div class="mt-6 flex items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-600">
                Kirim ulang email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-bold text-stone-500 hover:text-stone-800">
                Keluar
            </button>
        </form>
    </div>
</x-guest-layout>
