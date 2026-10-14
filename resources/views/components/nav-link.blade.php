{{-- Legacy Breeze top-nav link, restyled to the design system tokens. --}}
@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center gap-2 px-1 pt-1 border-b-2 border-indicator text-sm font-medium leading-5 text-fg transition-colors duration-fast'
            : 'inline-flex items-center gap-2 px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-fg-muted hover:text-fg hover:border-line-strong transition-colors duration-fast';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if ($active ?? false) aria-current="page" @endif>
    {{ $slot }}
</a>
