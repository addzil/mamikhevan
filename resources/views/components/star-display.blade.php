@props([
    'rating' => 0,
    'size' => 'h-4 w-4',
])

@php
    $filledStars = (int) round((float) $rating);
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-0.5 text-amber-400']) }} aria-label="{{ $rating }} dari 5 bintang">
    @for ($star = 1; $star <= 5; $star++)
        <svg class="{{ $size }} {{ $star <= $filledStars ? 'text-amber-400' : 'text-stone-200' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.539 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.98 10.1c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69l1.519-4.674Z" />
        </svg>
    @endfor
</div>
