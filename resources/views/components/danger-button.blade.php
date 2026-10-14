{{-- Legacy Breeze button, restyled to the design system. Prefer <x-ui.button variant="danger"> or <x-ui.confirm-delete>. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-danger']) }}>
    {{ $slot }}
</button>
