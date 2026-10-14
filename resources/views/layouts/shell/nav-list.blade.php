{{--
    Grouped navigation list, shared by the sidebar, drawer and "Lainnya" sheet.
    Variables: $sections (Navigation::for), $rail (bool: desktop sidebar that can collapse
    to an icon rail), $filterable (bool: items react to an outer Alpine `q` search string),
    $idPrefix (unique id prefix per instance).
--}}
@php
    $rail ??= false;
    $filterable ??= false;
    $idPrefix ??= 'nav';
@endphp
@foreach ($sections as $section)
    @php
        $hasActive = collect($section['items'])->contains('is_active', true);
        $groupId = $idPrefix.'-'.$section['key'];
    @endphp
    <div @class(['mt-5' => ! $loop->first]) x-data="cfNavGroup(@js($section['key']), @js($hasActive || ! $section['label']))">
        @if ($section['label'])
            <h3 @class(['[[data-sidebar=collapsed]_.cf-sidebar_&]:hidden' => $rail]) @if ($filterable) x-show="!q" @endif>
                <button type="button" @click="toggle()" :aria-expanded="open.toString()" aria-expanded="true" aria-controls="{{ $groupId }}"
                        class="nav-label flex w-full items-center justify-between gap-1 rounded-md px-3 py-1.5 text-left transition-colors hover:text-fg-muted">
                    <span class="min-w-0 text-balance">{{ $section['label'] }}</span>
                    <x-heroicon-m-chevron-down class="size-4 shrink-0 transition-transform duration-150 motion-reduce:transition-none" x-bind:class="open ? '' : '-rotate-90'" aria-hidden="true" />
                </button>
            </h3>
        @endif
        <ul id="{{ $groupId }}" role="list"
            @class(['space-y-0.5', 'mt-1' => (bool) $section['label'], '[[data-sidebar=collapsed]_.cf-sidebar_&]:!block' => $rail])
            x-show="{{ $filterable ? 'open || q' : 'open' }}">
            @foreach ($section['items'] as $item)
                @include('layouts.shell.nav-item', ['item' => $item + ['section' => $section['label']], 'rail' => $rail, 'filterable' => $filterable])
            @endforeach
        </ul>
    </div>
@endforeach
