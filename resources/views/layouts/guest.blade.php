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
    <body class="min-h-screen bg-[#FFFDF4] font-sans text-stone-900 antialiased">
        <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
            <div class="absolute -left-24 top-[-6rem] h-72 w-72 rounded-full bg-amber-200/40 blur-3xl"></div>
            <div class="absolute -right-24 top-1/3 h-80 w-80 rounded-full bg-yellow-200/50 blur-3xl"></div>
            <div class="absolute bottom-[-8rem] left-1/3 h-64 w-64 rounded-full bg-amber-100/70 blur-3xl"></div>
        </div>

        <div class="relative flex min-h-screen flex-col">
            <header class="px-6 py-6">
                <a href="{{ route('home') }}" class="mx-auto flex w-fit items-center gap-2.5">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-amber-500 text-lg text-white shadow-lg shadow-amber-500/25">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75 4.5 6l1.875 4.5L9 9.75V21h6V9.75l2.625.75L19.5 6 15 3.75a3 3 0 0 1-6 0Z" /></svg>
                    </span>
                    <span class="font-display text-2xl font-bold tracking-tight text-stone-900">Mamikhevan<span class="text-amber-600">Shop</span></span>
                </a>
            </header>

            <main class="flex flex-1 items-center justify-center px-4 pb-10">
                <div class="w-full max-w-md rounded-[2rem] border border-yellow-100 bg-white p-6 shadow-xl shadow-yellow-900/5 sm:p-8">
                    {{ $slot }}
                </div>
            </main>

            <footer class="px-6 pb-8 text-center">
                <p class="text-xs font-semibold text-stone-400">
                    Mamikhevanshop Admin &middot; &copy; {{ date('Y') }}
                </p>
                <a href="{{ route('home') }}" class="mt-2 inline-flex items-center gap-1.5 text-xs font-extrabold text-amber-600 transition hover:text-yellow-700">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                    Kembali ke toko
                </a>
            </footer>
        </div>
    </body>
</html>
