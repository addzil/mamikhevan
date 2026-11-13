@props(['product', 'class' => ''])

@if ($product->image_url)
    <img src="{{ $product->image_url }}" alt="Ilustrasi {{ $product->name }}"
         class="h-full w-full {{ $product->image_fit === 'cover' ? 'object-cover' : 'object-contain' }} {{ $class }}" loading="lazy">
@else
    <div class="grid h-full w-full place-items-center bg-gradient-to-br from-yellow-100 via-amber-100 to-amber-100 {{ $class }}">
        <svg class="h-1/3 w-1/3 text-amber-400/70" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75 4.5 6l1.875 4.5L9 9.75V21h6V9.75l2.625.75L19.5 6 15 3.75a3 3 0 0 1-6 0Z" />
        </svg>
    </div>
@endif
