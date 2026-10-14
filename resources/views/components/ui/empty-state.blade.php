{{--
    <x-ui.empty-state icon="calendar" title="Belum ada kelas minggu ini."
        description="Jadwal berikutnya muncul setelah pendaftaran dikonfirmasi." hint="tenggat respons 3×24 jam">
        <x-slot:actions><x-ui.button :href="route('courses')" variant="secondary">Lihat program</x-ui.button></x-slot:actions>
    </x-ui.empty-state>
    size: inline (default, inside cards/tables) | page (dashed panel for a whole page)
    icon: heroicon name, or false for none. Default slot = extra description markup.
--}}
@props([
    'icon' => 'inbox',
    'title',
    'description' => null,
    'hint' => null,
    'size' => 'inline',
])

<div {{ $attributes->class(['empty-state', 'empty-state-page' => $size === 'page']) }}>
    @if ($icon)
        <div class="empty-state-icon" aria-hidden="true">
            <x-ui.icon :name="$icon" />
        </div>
    @endif
    <p class="empty-state-title">{{ $title }}</p>
    @if (filled($description))
        <p class="empty-state-description">{{ $description }}</p>
    @endif
    @if ($slot->hasActualContent())
        <div class="empty-state-description">{{ $slot }}</div>
    @endif
    @if (filled($hint))
        <p class="empty-state-hint">{{ $hint }}</p>
    @endif
    @isset($actions)
        <div class="empty-state-actions">{{ $actions }}</div>
    @endisset
</div>
