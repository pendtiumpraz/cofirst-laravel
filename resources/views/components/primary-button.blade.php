{{-- Legacy Breeze button, restyled to the design system. Prefer <x-ui.button>. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary']) }}>
    {{ $slot }}
</button>
