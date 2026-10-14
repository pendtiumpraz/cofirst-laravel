<x-app-layout :title="'Materi · '.$curriculum->title">
    <x-ui.page-header title="Materi kurikulum" :subtitle="$curriculum->title"
        :breadcrumbs="['Kurikulum' => route('teacher.curriculums.index'), $curriculum->title => route('teacher.curriculums.show', $curriculum), 'Materi' => null]">
        <x-ui.tabs label="Filter jenis materi" :active="$type ?? 'semua'" :tabs="collect(['semua' => 'Semua'] + $types)->map(fn ($label, $key) => ['label' => $label, 'href' => route('teacher.curriculums.materials', $key === 'semua' ? $curriculum : [$curriculum, 'type' => $key])])->all()" />
    </x-ui.page-header>

    @forelse ($syllabuses as $syllabus)
        <section class="mb-8" aria-labelledby="pertemuan-{{ $syllabus->id }}">
            <div class="mb-3 flex items-baseline gap-3">
                <span class="eyebrow">Pertemuan {{ $syllabus->meeting_number }}</span>
                <h2 id="pertemuan-{{ $syllabus->id }}" class="section-title">{{ $syllabus->title }}</h2>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($syllabus->activeMaterials as $material)
                    <x-ui.card :href="route('teacher.materials.show', $material)" :title="$material->title" padding="sm" level="3">
                        <div class="flex flex-wrap items-center gap-2">
                            @include('admin.materials.partials.type-badge', ['type' => $material->type])
                            @if ($material->file_path)<x-ui.badge icon="paper-clip">Berkas</x-ui.badge>@endif
                            @if ($material->external_url)<x-ui.badge icon="link">Tautan</x-ui.badge>@endif
                        </div>
                    </x-ui.card>
                @endforeach
            </div>
        </section>
    @empty
        <x-ui.empty-state size="page" icon="folder-open" title="Tidak ada materi untuk filter ini."
            description="Pilih jenis lain, atau minta admin menambahkan materi." />
    @endforelse
</x-app-layout>
