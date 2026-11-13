@props([
    'name',
    'value' => 5,
    'size' => 'h-6 w-6',
])

<div x-data="{ rating: {{ (int) $value }}, hover: 0 }" class="flex items-center gap-2">
    <input type="hidden" name="{{ $name }}" x-model.number="rating">
    <div class="flex items-center gap-0.5" @mouseleave="hover = 0">
        @for ($star = 1; $star <= 5; $star++)
            <button type="button"
                    @click="rating = {{ $star }}"
                    @mouseenter="hover = {{ $star }}"
                    class="rounded-md p-0.5 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300"
                    aria-label="{{ $star }} dari 5 bintang">
                <svg class="{{ $size }} transition-colors"
                     :class="(hover || rating) >= {{ $star }} ? 'text-amber-400' : 'text-stone-200'"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.539 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.98 10.1c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69l1.519-4.674Z" />
                </svg>
            </button>
        @endfor
    </div>
    <span class="text-xs font-extrabold text-stone-500" x-text="rating + '/5'"></span>
</div>
