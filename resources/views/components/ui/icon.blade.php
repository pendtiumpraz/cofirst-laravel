{{--
    <x-ui.icon name="academic-cap" />              outline 24 (default)
    <x-ui.icon name="s-star" size="20" />          solid; also m- (mini 20) and c- (micro 16)
    Decorative by default (aria-hidden). Pass label="…" for a meaningful icon.
    Unknown names render nothing instead of throwing.
--}}
@props([
    'name',
    'size' => null,
    'label' => null,
])

@php
    $iconName = match (true) {
        str_starts_with($name, 'heroicon-') => $name,
        (bool) preg_match('/^(o|s|m|c)-/', $name) => 'heroicon-'.$name,
        default => 'heroicon-o-'.$name,
    };

    $sizeClass = match ((string) $size) {
        '12' => 'size-3',
        '14' => 'size-3.5',
        '16' => 'size-4',
        '18' => 'size-[1.125rem]',
        '20' => 'size-5',
        '24' => 'size-6',
        '32' => 'size-8',
        default => '',
    };

    $classes = trim('cf-icon shrink-0 '.$sizeClass.' '.$attributes->get('class', ''));
    $extra = $attributes->except('class')->getAttributes();
    $extra += $label ? ['role' => 'img', 'aria-label' => $label] : ['aria-hidden' => 'true'];

    try {
        $svg = svg($iconName, $classes, $extra)->toHtml();
    } catch (\BladeUI\Icons\Exceptions\SvgNotFound) {
        $svg = '';
    }
@endphp
{!! $svg !!}
