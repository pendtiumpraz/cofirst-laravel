{{-- One weekly slot as a compact card. Expects $slot (ScheduleSlot) and $clashes (key => reasons). --}}
@php
    $reasons = $clashes[$slot->key] ?? [];
    $teacher = $slot->teacher();
@endphp

<a href="{{ route('admin.schedules.show', $slot->id()) }}"
    @class([
        'block rounded-md border bg-surface p-3 transition-colors duration-fast hover:border-line-strong hover:bg-surface-2 motion-reduce:transition-none',
        'border-danger' => $reasons,
        'border-line' => ! $reasons,
        'opacity-60' => ! $slot->isActive(),
    ])>
    <p class="num font-mono text-xs font-semibold text-fg-muted">{{ $slot->timeRange() }}</p>
    <p class="mt-1 text-sm font-medium leading-snug text-fg">{{ $slot->class()?->name ?? 'Kelas' }}</p>
    <p class="mt-0.5 text-xs text-fg-muted">
        {{ $teacher?->name ?? 'Tanpa pengajar' }}@if ($slot->room()) · {{ $slot->room() }}@endif
    </p>
    <div class="mt-2 flex flex-wrap gap-1">
        <x-ui.badge tone="neutral">{{ $slot->studentCount() }} siswa</x-ui.badge>
        @if ($reasons)
            <x-ui.badge tone="danger" icon="exclamation-triangle">Bentrok {{ implode(' & ', $reasons) }}</x-ui.badge>
        @endif
        @unless ($slot->isActive())
            <x-ui.badge tone="warning">Nonaktif</x-ui.badge>
        @endunless
    </div>
</a>
