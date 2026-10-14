{{--
    <x-ui.divider />                       hairline
    <x-ui.divider label="atau" />          labelled
    <x-ui.divider vertical />              between inline items (parent must be flex)
--}}
@props([
    'label' => null,
    'vertical' => false,
])

@if ($vertical)
    <span role="separator" aria-orientation="vertical" {{ $attributes->class('divider-vertical') }}></span>
@elseif (filled($label))
    <div role="separator" {{ $attributes->class('divider-label') }}>{{ $label }}</div>
@else
    <hr {{ $attributes->class('divider') }}>
@endif
