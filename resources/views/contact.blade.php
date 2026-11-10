@extends('layouts.storefront')

@section('title', 'Hubungi Kami — Mamikhevanshop')
@section('meta_description', 'Hubungi tim Mamikhevanshop untuk bantuan ukuran, pertanyaan produk, atau kerja sama. Kami siap membantu setiap hari.')

@section('content')
    <section class="relative overflow-hidden border-b border-yellow-100 bg-gradient-to-b from-yellow-50/80 to-[#FFFDF4]">
        <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-amber-200/40 blur-3xl"></div>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <nav class="flex items-center gap-2 text-xs font-bold text-stone-400" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="transition hover:text-amber-600">Beranda</a>
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                <span class="text-amber-600">Kontak</span>
            </nav>
            <div class="mt-4 max-w-2xl">
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-amber-400">Kami siap membantu</p>
                <h1 class="mt-2 font-display text-3xl font-extrabold text-stone-900 sm:text-4xl">Ada yang bisa kami bantu?</h1>
                <p class="mt-3 text-sm leading-7 text-stone-500">
                    Butuh bantuan memilih ukuran, tanya stok, atau ada kendala pesanan? Kirim pesan lewat form atau WhatsApp, kami balas maksimal 1x24 jam.
                </p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[380px_1fr] lg:gap-8">
            <div class="space-y-4">
                <div class="rounded-3xl border border-yellow-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start gap-3">
                        <span class="grid h-11 w-11 flex-none place-items-center rounded-2xl bg-amber-50 text-amber-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                        </span>
                        <div>
                            <h2 class="font-display text-base font-bold text-stone-900">Alamat toko</h2>
                            <p class="mt-1 text-sm leading-6 text-stone-500">Jl. Ceria Anak No. 17, Sukajadi, Bandung, Jawa Barat 40162</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-yellow-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start gap-3">
                        <span class="grid h-11 w-11 flex-none place-items-center rounded-2xl bg-emerald-50 text-emerald-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                        </span>
                        <div>
                            <h2 class="font-display text-base font-bold text-stone-900">WhatsApp</h2>
                            <p class="mt-1 text-sm leading-6 text-stone-500">0812-3456-7890</p>
                            <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer" class="mt-1 inline-flex text-xs font-extrabold text-emerald-600 hover:underline">Chat sekarang</a>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-yellow-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start gap-3">
                        <span class="grid h-11 w-11 flex-none place-items-center rounded-2xl bg-sky-50 text-sky-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                        </span>
                        <div>
                            <h2 class="font-display text-base font-bold text-stone-900">Email</h2>
                            <p class="mt-1 text-sm leading-6 text-stone-500">halo@mamikhevanshop.id</p>
                            <p class="text-xs font-semibold text-stone-400">Untuk pertanyaan &amp; kerja sama</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-yellow-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start gap-3">
                        <span class="grid h-11 w-11 flex-none place-items-center rounded-2xl bg-amber-50 text-amber-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </span>
                        <div>
                            <h2 class="font-display text-base font-bold text-stone-900">Jam operasional</h2>
                            <p class="mt-1 text-sm leading-6 text-stone-500">Senin &ndash; Sabtu, 08.00 &ndash; 17.00 WIB</p>
                            <p class="text-xs font-semibold text-stone-400">Minggu &amp; hari libur nasional tutup</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-yellow-100 bg-white p-6 shadow-sm sm:p-8">
                <h2 class="font-display text-2xl font-extrabold text-stone-900">Kirim pesan</h2>
                <p class="mt-1 text-sm font-semibold text-stone-400">Isi form di bawah, kami akan menghubungimu kembali.</p>

                @if ($errors->any())
                    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                        <p class="text-sm font-extrabold text-amber-700">Ada data yang perlu diperbaiki:</p>
                        <ul class="mt-2 list-inside list-disc space-y-1 text-xs font-semibold text-yellow-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('contact.store') }}" method="POST" class="mt-6 space-y-5">
                    @csrf
                    <div class="absolute -left-[9999px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                        <label for="website">Jangan diisi</label>
                        <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Nama <span class="text-amber-600">*</span></label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                                   class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                            @error('name') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Email <span class="text-amber-600">*</span></label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                                   class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                            @error('email') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="phone" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">No. HP <span class="font-semibold text-stone-400">(opsional)</span></label>
                            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" autocomplete="tel"
                                   class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                            @error('phone') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="subject" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Subjek <span class="text-amber-600">*</span></label>
                            <input id="subject" type="text" name="subject" value="{{ old('subject') }}" required placeholder="Contoh: Tanya ukuran"
                                   class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">
                            @error('subject') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="message" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Pesan <span class="text-amber-600">*</span></label>
                        <textarea id="message" name="message" rows="6" required placeholder="Tulis pertanyaanmu di sini..."
                                  class="mt-2 w-full rounded-xl border-yellow-200 py-2.5 text-sm focus:border-amber-300 focus:ring-amber-200">{{ old('message') }}</textarea>
                        @error('message') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                        <p class="text-[11px] font-semibold text-stone-400">Data kamu aman dan tidak akan dibagikan.</p>
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-6 py-3 text-sm font-extrabold text-white shadow-soft transition hover:bg-amber-600 sm:w-auto">
                            Kirim pesan
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
