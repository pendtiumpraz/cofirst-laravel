{{-- Optional heading block of the guest layout. Variables: $heading, $subheading. --}}
@if ($heading)
    <div class="mb-8">
        <h1 class="font-display text-2xl font-title leading-tight tracking-display text-fg sm:text-[1.75rem]">{{ $heading }}</h1>
        @if ($subheading)
            <p class="mt-2 text-sm text-fg-muted">{{ $subheading }}</p>
        @endif
    </div>
@endif
