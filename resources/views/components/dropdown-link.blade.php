{{-- Legacy Breeze dropdown link, restyled. Prefer <x-ui.dropdown-item>. --}}
<a {{ $attributes->merge(['class' => 'dropdown-item']) }}>{{ $slot }}</a>
