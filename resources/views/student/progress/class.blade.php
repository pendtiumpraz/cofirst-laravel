@php($f = \App\Services\Academic\Format::class)
@php($attended = (int) ($attendance['present'] ?? 0) + (int) ($attendance['late'] ?? 0))
@php($recorded = (int) $attendance->sum())
<x-app-layout :title="'Progres · '.$class->name">
    <x-ui.page-header :title="$class->name" eyebrow="Progres belajar" :subtitle="$class->curriculum?->title"
        :breadcrumbs="['Kurikulum' => route('student.curriculum.index'), $class->name => route('student.curriculum.show', $class), 'Progres' => null]">
        <x-slot:actions>
            <x-ui.button icon="folder-open" :href="route('student.materials.by-class', $class)">Materi</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.metric-strip class="mb-8" :stats="[
        ['label' => 'Pertemuan selesai', 'value' => $summary->completed().' / '.$summary->total(), 'icon' => 'check-circle', 'tone' => 'success'],
        ['label' => 'Sedang berjalan', 'value' => (string) $summary->inProgress(), 'icon' => 'play-circle', 'tone' => 'info'],
        ['label' => 'Progres', 'value' => $f::percent($summary->percent()), 'icon' => 'chart-bar'],
        ['label' => 'Kehadiran', 'value' => $recorded > 0 ? $f::percent(round($attended / $recorded * 100, 1)) : null, 'hint' => $recorded > 0 ? $attended.' dari '.$recorded.' pertemuan' : null, 'icon' => 'calendar-days'],
    ]" />

    @if (! $summary->hasCurriculum())
        <x-ui.empty-state size="page" icon="queue-list" title="Kurikulum kelas ini belum ditetapkan."
            description="Progres per pertemuan akan muncul setelah kurikulum kelas dilengkapi." />
    @else
        <x-ui.card title="Perjalanan belajar" padding="none">
            <x-slot:actions>
                <x-ui.progress class="w-40" :value="$summary->percent()" size="sm" label="Progres keseluruhan" />
            </x-slot:actions>
            <ol>
                @foreach ($summary->meetings as $meeting)
                    <li class="flex flex-col gap-2 border-t border-line px-card py-4 sm:flex-row sm:items-center sm:gap-4">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-surface-2 font-num text-sm font-semibold text-fg" aria-hidden="true">{{ $meeting['number'] }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-fg">{{ $meeting['syllabus']->title }}</p>
                            @if ($meeting['completed_at'])
                                <p class="text-sm text-fg-subtle">Selesai {{ $f::date($meeting['completed_at'], 'd F Y') }}</p>
                            @elseif (filled($meeting['notes']))
                                <p class="text-sm text-fg-subtle">{{ $meeting['notes'] }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @include('student.curriculum.partials.meeting-status', ['status' => $meeting['status']])
                            @if ($meeting['unlocked'])
                                <x-ui.badge icon="lock-open" tone="success">Materi terbuka</x-ui.badge>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </x-ui.card>
    @endif
</x-app-layout>
