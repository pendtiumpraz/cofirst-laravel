{{-- Legacy Breeze validation messages, restyled to the design system. Prefer <x-ui.field error="…">. --}}
@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li class="error-text">
                <x-ui.icon name="m-exclamation-circle" />
                <span>{{ $message }}</span>
            </li>
        @endforeach
    </ul>
@endif
