{{-- Legacy Breeze input, restyled to the design system. Prefer <x-ui.input> inside <x-ui.field>. --}}
@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'input']) }}>
