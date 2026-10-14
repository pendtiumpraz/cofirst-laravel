<x-app-layout :title="$material->title">
    <x-ui.page-header :title="$material->title" :eyebrow="$material->meeting_range" :subtitle="$material->description"
        :breadcrumbs="[
            ['label' => 'Materi', 'url' => route('teacher.materials.index')],
            ['label' => $material->syllabus?->curriculum?->title ?? 'Kurikulum', 'url' => $material->syllabus?->curriculum ? route('teacher.curriculums.show', $material->syllabus->curriculum) : null],
            ['label' => $material->title],
        ]">
        <x-slot:badge>
            @include('admin.materials.partials.type-badge', ['type' => $material->type])
        </x-slot:badge>
        @if ($material->syllabus)
            <x-slot:actions>
                <x-ui.button variant="secondary" icon="document-text" :href="route('teacher.syllabuses.show', $material->syllabus)">Pertemuan {{ $material->syllabus->meeting_number }}</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <div class="max-w-4xl">
        @include('admin.materials.partials.viewer', [
            'downloadUrl' => route('teacher.materials.download', $material),
        ])
    </div>
</x-app-layout>
