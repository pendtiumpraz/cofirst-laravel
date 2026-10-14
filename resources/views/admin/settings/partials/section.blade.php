{{--
    One section of a settings group. Regular sections use the 1/3 intro + 2/3 fields
    layout; sections with A/B cards or repeaters stack (intro above) to give them room.
--}}
@php
    $wide = collect($section['fields'])->contains(fn ($field) => in_array($field['type'], ['choice', 'repeater'], true));
    $headingId = 'section-'.$group['key'].'-'.$index;
@endphp
<section aria-labelledby="{{ $headingId }}" @class([
    'border-b border-line py-8 first:pt-0 last:border-b-0 last:pb-0',
    'grid gap-6 md:grid-cols-3' => ! $wide,
    'space-y-6' => $wide,
])>
    <div @class(['md:col-span-1' => ! $wide, 'max-w-measure' => $wide])>
        <h3 id="{{ $headingId }}" class="text-base font-semibold text-fg">{{ $section['title'] ?? $group['label'] }}</h3>
        @if (filled($section['description']))
            <p class="mt-1 text-sm text-fg-muted">{{ $section['description'] }}</p>
        @endif
    </div>

    <div @class(['flex min-w-0 flex-col gap-6', 'md:col-span-2' => ! $wide])>
        @foreach ($section['fields'] as $field)
            @include('admin.settings.partials.field', ['field' => $field])
        @endforeach
    </div>
</section>
