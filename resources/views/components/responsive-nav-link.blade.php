{{-- Legacy Breeze mobile nav link, restyled to the design system tokens. --}}
@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2.5 border-l-2 border-indicator text-start text-base font-medium text-fg bg-surface-2 transition-colors duration-fast'
            : 'block w-full ps-3 pe-4 py-2.5 border-l-2 border-transparent text-start text-base font-medium text-fg-muted hover:text-fg hover:bg-surface-2 hover:border-line-strong transition-colors duration-fast';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if ($active ?? false) aria-current="page" @endif>
    {{ $slot }}
</a>
