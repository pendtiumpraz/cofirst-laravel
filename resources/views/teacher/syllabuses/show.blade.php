<x-app-layout :title="'Pertemuan '.$syllabus->meeting_number">
    <x-ui.page-header :title="$syllabus->title" :eyebrow="'Pertemuan '.$syllabus->meeting_number.' · '.$syllabus->duration_minutes.' menit'"
        :subtitle="$syllabus->curriculum?->title"
        :breadcrumbs="[
            ['label' => 'Kurikulum', 'url' => route('teacher.curriculums.index')],
            ['label' => $syllabus->curriculum?->title ?? 'Kurikulum', 'url' => $syllabus->curriculum ? route('teacher.curriculums.show', $syllabus->curriculum) : null],
            ['label' => 'Pertemuan '.$syllabus->meeting_number],
        ]">
        @if ($classes->isNotEmpty())
            <x-slot:actions>
                @if ($classes->count() === 1)
                    <x-ui.button icon="clipboard-document-list" :href="route('class-reports.create', ['class_id' => $classes->first()->id, 'meeting' => $syllabus->meeting_number])">Buat berita acara</x-ui.button>
                @else
                    <x-ui.dropdown label="Buat berita acara" icon="clipboard-document-list" variant="primary">
                        <p class="dropdown-label">Pilih kelas</p>
                        @foreach ($classes as $class)
                            <x-ui.dropdown-item :href="route('class-reports.create', ['class_id' => $class->id, 'meeting' => $syllabus->meeting_number])">{{ $class->name }}</x-ui.dropdown-item>
                        @endforeach
                    </x-ui.dropdown>
                @endif
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <div class="grid gap-gutter lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Rencana belajar">
                @if (filled($syllabus->description))
                    <p class="max-w-measure whitespace-pre-line text-fg-muted">{{ $syllabus->description }}</p>
                @endif
                <div class="mt-5 grid gap-6 sm:grid-cols-2">
                    <div>
                        <p class="eyebrow mb-2">Tujuan pembelajaran</p>
                        @forelse ($syllabus->learning_objectives_list as $item)
                            <p class="mb-2 flex gap-2 text-fg"><x-ui.icon name="check-circle" class="mt-0.5 shrink-0 text-success" /> <span>{{ $item }}</span></p>
                        @empty
                            <p class="text-sm text-fg-subtle">Belum diisi.</p>
                        @endforelse
                    </div>
                    <div>
                        <p class="eyebrow mb-2">Kegiatan</p>
                        @if ($syllabus->activities_list)
                            <ol class="list-decimal space-y-2 pl-5 text-fg">
                                @foreach ($syllabus->activities_list as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ol>
                        @else
                            <p class="text-sm text-fg-subtle">Belum diisi.</p>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            <nav class="flex flex-col gap-3 sm:flex-row sm:justify-between" aria-label="Navigasi pertemuan">
                @if ($previous)
                    <x-ui.button variant="secondary" icon="arrow-left" :href="route('teacher.syllabuses.show', $previous)">Pertemuan {{ $previous->meeting_number }}</x-ui.button>
                @else
                    <span></span>
                @endif
                @if ($next)
                    <x-ui.button variant="secondary" icon-right="arrow-right" :href="route('teacher.syllabuses.show', $next)">Pertemuan {{ $next->meeting_number }}</x-ui.button>
                @endif
            </nav>
        </div>

        <x-ui.card title="Materi pertemuan" padding="none">
            @forelse ($syllabus->activeMaterials as $material)
                <a href="{{ route('teacher.materials.show', $material) }}" class="flex min-h-[44px] items-center gap-3 border-t border-line px-card py-3 hover:bg-surface-2">
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-medium text-fg">{{ $material->title }}</span>
                        @if (filled($material->description))
                            <span class="block truncate text-sm text-fg-subtle">{{ $material->description }}</span>
                        @endif
                    </span>
                    @include('admin.materials.partials.type-badge', ['type' => $material->type])
                </a>
            @empty
                <div class="border-t border-line"><x-ui.empty-state icon="folder-open" title="Belum ada materi untuk pertemuan ini." /></div>
            @endforelse
        </x-ui.card>
    </div>
</x-app-layout>
