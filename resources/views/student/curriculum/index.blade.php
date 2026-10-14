@php($f = \App\Services\Academic\Format::class)
<x-app-layout title="Kurikulum">
    <x-ui.page-header title="Kurikulum saya"
        subtitle="Apa yang kamu pelajari di setiap kelas, pertemuan demi pertemuan." />

    @if ($classes->isEmpty())
        <x-ui.empty-state size="page" icon="academic-cap" title="Kamu belum terdaftar di kelas mana pun."
            description="Kurikulum kelas akan muncul di sini setelah pendaftaranmu aktif.">
            @if (Route::has('student.courses.index'))
                <x-slot:actions>
                    <x-ui.button variant="secondary" :href="route('student.courses.index')">Lihat kursus</x-ui.button>
                </x-slot:actions>
            @endif
        </x-ui.empty-state>
    @else
        <div class="grid gap-gutter md:grid-cols-2">
            @foreach ($classes as $class)
                @php($summary = $summaries->get($class->id))
                @php($next = $summary?->nextMeeting())
                <x-ui.card as="article" :title="$class->name" :subtitle="$class->course?->name"
                    :variant="$class->status === 'active' ? 'highlight' : 'default'">
                    <x-slot:actions>
                        @if ($enrollments->get($class->id)?->status === 'completed')
                            <x-ui.badge tone="success">Lulus</x-ui.badge>
                        @else
                            <x-ui.badge>{{ $class->status_label }}</x-ui.badge>
                        @endif
                    </x-slot:actions>

                    @if ($summary?->hasCurriculum())
                        <p class="text-sm text-fg-muted">{{ $class->curriculum->title }}</p>
                        <x-ui.progress class="mt-4" :value="$summary->percent()" show-value
                            :tone="$summary->percent() >= 100 ? 'success' : 'brand'"
                            label="{{ $summary->completed() }} dari {{ $summary->total() }} pertemuan selesai" />
                        @if ($next)
                            <div class="mt-4 rounded-lg bg-surface-2 p-3">
                                <p class="eyebrow">Berikutnya · Pertemuan {{ $next->meeting_number }}</p>
                                <p class="mt-1 font-medium text-fg">{{ $next->title }}</p>
                            </div>
                        @endif
                    @else
                        <x-ui.alert tone="neutral" icon="information-circle">Kurikulum kelas ini belum ditetapkan.</x-ui.alert>
                    @endif

                    @if ($class->teachers->isNotEmpty())
                        <p class="mt-4 text-sm text-fg-subtle">Pengajar: {{ $class->teachers->pluck('name')->join(', ') }}</p>
                    @endif

                    <x-slot:footer>
                        <x-ui.button variant="secondary" size="sm" icon="folder-open" :href="route('student.materials.by-class', $class)">Materi</x-ui.button>
                        <x-ui.button size="sm" icon="queue-list" :href="route('student.curriculum.show', $class)">Lihat kurikulum</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</x-app-layout>
