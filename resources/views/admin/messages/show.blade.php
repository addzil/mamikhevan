@extends('layouts.admin')

@section('title', 'Pesan dari '.$message->name)
@section('heading', 'Detail pesan')
@section('subheading', 'Diterima '.$message->created_at->format('d/m/Y H:i').' WIB')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <a href="{{ route('admin.messages.index') }}" class="inline-flex items-center gap-2 text-xs font-extrabold text-stone-500 transition hover:text-yellow-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                Kembali ke kotak masuk
            </a>
            <a href="#reply-form"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-amber-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                Balas via email
            </a>
        </div>

        <div class="mt-4 rounded-3xl border border-stone-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-stone-100 p-6 sm:flex-row sm:items-center">
                <div class="grid h-14 w-14 flex-none place-items-center rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-400 text-xl font-extrabold text-amber-950">
                    {{ strtoupper(mb_substr($message->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="font-display text-xl font-extrabold text-stone-900">{{ $message->name }}</h2>
                    <p class="mt-0.5 text-xs font-semibold text-stone-400">
                        {{ $message->email }}
                        @if ($message->phone)
                            &middot; {{ $message->phone }}
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-extrabold text-emerald-600 ring-1 ring-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Sudah dibaca
                    </span>
                    @if ($message->replied_at)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-2.5 py-1 text-[10px] font-extrabold text-sky-600 ring-1 ring-sky-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span> Sudah dibalas
                        </span>
                    @endif
                    <span class="text-[11px] font-semibold text-stone-400">{{ $message->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-stone-400">Subjek</p>
                <h3 class="mt-1 font-display text-lg font-bold text-stone-900">{{ $message->subject }}</h3>

                <div class="mt-5 rounded-2xl bg-stone-50 p-5">
                    <p class="whitespace-pre-line text-sm font-semibold leading-7 text-stone-700">{{ $message->message }}</p>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-stone-100 bg-stone-50/50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap gap-2">
                    <a href="mailto:{{ $message->email }}" class="rounded-xl border border-stone-200 bg-white px-3 py-2 text-[11px] font-extrabold text-stone-600 transition hover:border-amber-200 hover:text-yellow-700">
                        {{ $message->email }}
                    </a>
                    @if ($message->phone)
                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $message->phone)) }}" target="_blank" rel="noopener noreferrer"
                           class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] font-extrabold text-emerald-600 transition hover:bg-emerald-100">
                            WhatsApp {{ $message->phone }}
                        </a>
                    @endif
                </div>

                <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" onsubmit="return confirm('Hapus pesan ini? Tindakan ini tidak bisa dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-white px-3 py-2 text-[11px] font-extrabold text-yellow-700 transition hover:bg-amber-50">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        Hapus pesan
                    </button>
                </form>
            </div>
        </div>

        <div id="reply-form" class="mt-6 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-start">
                <div>
                    <h2 class="font-display text-xl font-extrabold text-stone-900">Balas via email</h2>
                    <p class="mt-1 text-xs font-semibold leading-5 text-stone-400">
                        Balasan dikirim langsung dari
                        <span class="font-extrabold text-stone-600">{{ config('mail.from.address') }}</span>
                        ke {{ $message->email }}.
                    </p>
                </div>
                @if ($message->replied_at)
                    <p class="text-[11px] font-semibold text-sky-600">Terakhir dibalas {{ $message->replied_at->format('d/m/Y H:i') }} WIB</p>
                @endif
            </div>

            @if ($errors->any())
                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-xs font-extrabold text-amber-700">Balasan belum terkirim:</p>
                    <ul class="mt-1.5 list-inside list-disc space-y-1 text-xs font-semibold text-yellow-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.messages.reply', $message) }}" method="POST" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="reply_subject" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Subjek email <span class="text-amber-600">*</span></label>
                    <input id="reply_subject" name="reply_subject" type="text" required maxlength="150"
                           value="{{ old('reply_subject', 'Re: '.$message->subject) }}"
                           class="mt-2 w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                    @error('reply_subject') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="reply_body" class="text-xs font-extrabold uppercase tracking-wider text-stone-500">Isi balasan <span class="text-amber-600">*</span></label>
                    <textarea id="reply_body" name="reply_body" rows="8" required maxlength="5000"
                              placeholder="Tulis balasan untuk {{ $message->name }}..."
                              class="mt-2 w-full rounded-xl border-stone-200 text-sm leading-6 focus:border-amber-300 focus:ring-amber-200">{{ old('reply_body') }}</textarea>
                    @error('reply_body') <p class="mt-1.5 text-xs font-bold text-amber-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col gap-3 border-t border-stone-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-[11px] font-semibold leading-5 text-stone-400">Pesan asli pelanggan otomatis disertakan di bagian bawah email.</p>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-amber-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                        Kirim balasan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
