{{-- Legacy Breeze label, restyled to the design system. Prefer <x-ui.field label="…">. --}}
@props(['value'])

<label {{ $attributes->merge(['class' => 'label']) }}>
    {{ $value ?? $slot }}
</label>
