@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $start = max(1, $current - 2);
        $end = min($last, $current + 2);
    @endphp

    <nav role="navigation" aria-label="Navigasi halaman"
         class="flex flex-col items-center justify-between gap-4 rounded-3xl border border-yellow-100 bg-white px-4 py-3 shadow-sm sm:flex-row sm:px-5">
        <p class="text-xs font-semibold text-stone-400">
            Menampilkan <span class="font-extrabold text-stone-600">{{ $paginator->firstItem() }}</span>&ndash;<span class="font-extrabold text-stone-600">{{ $paginator->lastItem() }}</span>
            dari <span class="font-extrabold text-stone-600">{{ $paginator->total() }}</span> hasil
        </p>

        <div class="flex items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="grid h-9 w-9 place-items-center rounded-full border border-stone-100 text-stone-300" aria-disabled="true">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="grid h-9 w-9 place-items-center rounded-full border border-yellow-100 text-stone-500 transition hover:border-amber-200 hover:text-amber-600"
                   aria-label="Halaman sebelumnya">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                </a>
            @endif

            @if ($start > 1)
                <a href="{{ $paginator->url(1) }}" class="grid h-9 w-9 place-items-center rounded-full text-xs font-extrabold text-stone-500 transition hover:bg-yellow-50">1</a>
                @if ($start > 2)
                    <span class="px-1 text-stone-300">&hellip;</span>
                @endif
            @endif

            @for ($page = $start; $page <= $end; $page++)
                @if ($page === $current)
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-amber-500 text-xs font-extrabold text-white shadow-sm" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="grid h-9 w-9 place-items-center rounded-full text-xs font-extrabold text-stone-500 transition hover:bg-yellow-50">{{ $page }}</a>
                @endif
            @endfor

            @if ($end < $last)
                @if ($end < $last - 1)
                    <span class="px-1 text-stone-300">&hellip;</span>
                @endif
                <a href="{{ $paginator->url($last) }}" class="grid h-9 w-9 place-items-center rounded-full text-xs font-extrabold text-stone-500 transition hover:bg-yellow-50">{{ $last }}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="grid h-9 w-9 place-items-center rounded-full border border-yellow-100 text-stone-500 transition hover:border-amber-200 hover:text-amber-600"
                   aria-label="Halaman berikutnya">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                </a>
            @else
                <span class="grid h-9 w-9 place-items-center rounded-full border border-stone-100 text-stone-300" aria-disabled="true">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
