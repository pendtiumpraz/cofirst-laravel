{{-- Legacy Breeze button, restyled to the design system. Prefer <x-ui.button variant="secondary">. --}}
<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-secondary']) }}>
    {{ $slot }}
</button>
