@php($types = \App\Models\Curriculum::getTypes())
<x-app-layout title="Kurikulum">
    <x-ui.page-header title="Kurikulum"
        subtitle="Kurikulum yang dipakai kelas Anda. Buka untuk melihat rencana tiap pertemuan beserta materinya." />

    @if ($curriculums->isEmpty())
        <x-ui.empty-state size="page" icon="queue-list" title="Belum ada kurikulum untuk kelas Anda."
            description="Kurikulum muncul di sini setelah admin menetapkannya pada kelas yang Anda ajar." />
    @else
        <div class="grid gap-gutter md:grid-cols-2 xl:grid-cols-3">
            @foreach ($curriculums as $curriculum)
                @php($myClasses = $classes->get($curriculum->id, collect()))
                <x-ui.card :href="route('teacher.curriculums.show', $curriculum)" :title="$curriculum->title"
                    :subtitle="$curriculum->course?->name" as="article">
                    <div class="flex flex-wrap gap-2">
                        <x-ui.badge outline>{{ $types[$curriculum->type] ?? $curriculum->type }}</x-ui.badge>
                        <x-ui.badge icon="calendar-days">{{ $curriculum->active_syllabuses_count }} pertemuan</x-ui.badge>
                        <x-ui.badge icon="folder-open">{{ $curriculum->active_materials_count }} materi</x-ui.badge>
                    </div>
                    <div class="mt-5 border-t border-line pt-4">
                        <p class="eyebrow mb-2">Kelas Anda</p>
                        <ul class="space-y-1.5 text-sm">
                            @foreach ($myClasses as $class)
                                <li class="flex items-center justify-between gap-3">
                                    <span class="truncate text-fg">{{ $class->name }}</span>
                                    <span class="shrink-0 text-fg-subtle">{{ $class->active_enrollments_count }} siswa</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        @if ($curriculums->hasPages())
            <div class="mt-6">{{ $curriculums->links() }}</div>
        @endif
    @endif
</x-app-layout>
