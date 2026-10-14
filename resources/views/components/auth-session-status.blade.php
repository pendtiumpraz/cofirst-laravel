{{-- Legacy Breeze status line, restyled to the design system. --}}
@props(['status'])

@if ($status)
    <div role="status" {{ $attributes->merge(['class' => 'alert alert-success']) }}>
        <x-ui.icon name="check-circle" class="alert-icon" />
        <div class="alert-body">{{ $status }}</div>
    </div>
@endif
