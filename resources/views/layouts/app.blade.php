<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ config('app.name', 'Mamikhevanshop') }}</title>

        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E%F0%9F%91%95%3C/text%3E%3C/svg%3E">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#FFFDF4] font-sans text-stone-800 antialiased">
        <header class="border-b border-yellow-100 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-3.5 sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-amber-500 text-white shadow-sm shadow-amber-500/25">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75 4.5 6l1.875 4.5L9 9.75V21h6V9.75l2.625.75L19.5 6 15 3.75a3 3 0 0 1-6 0Z" /></svg>
                    </span>
                    <span class="font-display text-xl font-bold tracking-tight text-stone-900">Mamikhevan<span class="text-amber-600">Shop</span></span>
                </a>

                <div class="flex items-center gap-2">
                    @auth
                        <span class="hidden text-xs font-bold text-stone-500 sm:inline">{{ auth()->user()->name }}</span>
                        <a href="{{ route('dashboard') }}" class="rounded-xl border border-stone-200 bg-white px-3 py-2 text-xs font-extrabold text-stone-600 transition hover:border-amber-200 hover:text-amber-600">
                            Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-extrabold text-amber-600 transition hover:bg-amber-100">
                                Keluar
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl bg-amber-500 px-4 py-2 text-xs font-extrabold text-white transition hover:bg-amber-600">Masuk</a>
                    @endauth
                </div>
            </div>
        </header>

        @isset($header)
            <div class="mx-auto max-w-5xl px-4 pt-8 sm:px-6">
                {{ $header }}
            </div>
        @endisset

        <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8">
            {{ $slot }}
        </main>

        <footer class="px-4 pb-8 text-center text-[11px] font-semibold text-stone-400">
            Mamikhevanshop &copy; {{ date('Y') }} &middot; Laravel {{ app()->version() }}
        </footer>
    </body>
</html>
