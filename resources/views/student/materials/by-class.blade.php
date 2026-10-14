<x-app-layout :title="'Materi · '.$class->name">
    <x-ui.page-header :title="$class->name" eyebrow="Materi kelas" :subtitle="$class->curriculum?->title"
        :breadcrumbs="['Materi' => route('student.materials.index'), $class->name => null]">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="queue-list" :href="route('student.curriculum.show', $class)">Kurikulum</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (! $summary->hasCurriculum())
        <x-ui.empty-state size="page" icon="folder-open" title="Kurikulum kelas ini belum ditetapkan."
            description="Materi akan muncul setelah kurikulum kelas dilengkapi." />
    @else
        <x-ui.alert tone="info" icon="lock-closed" class="mb-6">
            {{ count($unlockedIds) }} materi sudah terbuka. Materi lain terbuka mengikuti pertemuan yang kamu jalani.
        </x-ui.alert>

        @foreach ($summary->meetings as $meeting)
            @continue($meeting['syllabus']->activeMaterials->isEmpty())
            <section class="mb-8" aria-labelledby="materi-p{{ $meeting['number'] }}">
                <div class="mb-3 flex flex-wrap items-center gap-3">
                    <span class="eyebrow">Pertemuan {{ $meeting['number'] }}</span>
                    <h2 id="materi-p{{ $meeting['number'] }}" class="section-title">{{ $meeting['syllabus']->title }}</h2>
                    @include('student.curriculum.partials.meeting-status', ['status' => $meeting['status']])
                </div>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($meeting['syllabus']->activeMaterials as $material)
                        @php($open = in_array($material->id, $unlockedIds, true))
                        @php($seen = $accessStats->get($material->id))
                        @if ($open)
                            <x-ui.card :href="route('student.materials.show', $material)" :title="$material->title" padding="sm" level="3" :subtitle="$material->description">
                                <div class="flex flex-wrap items-center gap-2">
                                    @include('admin.materials.partials.type-badge', ['type' => $material->type])
                                    @if ($seen)
                                        <x-ui.badge icon="eye">Sudah dibuka</x-ui.badge>
                                    @else
                                        <x-ui.badge tone="primary">Baru</x-ui.badge>
                                    @endif
                                </div>
                            </x-ui.card>
                        @else
                            <div class="card bg-surface-2 p-4" aria-label="{{ $material->title }} (terkunci)">
                                <div class="flex items-start gap-3">
                                    <x-ui.icon name="lock-closed" class="mt-0.5 shrink-0 text-fg-subtle" />
                                    <div class="min-w-0">
                                        <p class="font-medium text-fg-muted">{{ $material->title }}</p>
                                        <p class="mt-1 text-sm text-fg-subtle">{{ \App\Services\Academic\AcademicSettings::unlockMode() === 'upcoming' ? 'Terbuka menjelang pertemuan '.$meeting['number'].'.' : 'Terbuka setelah pertemuan '.$meeting['number'].' selesai.' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach
    @endif
</x-app-layout>
