@php($types = \App\Models\Curriculum::getTypes())
<x-app-layout :title="$curriculum->title">
    <x-ui.page-header :title="$curriculum->title" eyebrow="Kurikulum" :subtitle="$curriculum->course?->name"
        :breadcrumbs="['Kurikulum' => route('teacher.curriculums.index'), $curriculum->title => null]">
        <x-slot:badge>
            <x-ui.badge outline>{{ $types[$curriculum->type] ?? $curriculum->type }}</x-ui.badge>
        </x-slot:badge>
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="table-cells" :href="route('teacher.curriculums.syllabuses', $curriculum)">Tabel pertemuan</x-ui.button>
            <x-ui.button variant="secondary" icon="folder-open" :href="route('teacher.curriculums.materials', $curriculum)">Semua materi</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-gutter lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            @if (filled($curriculum->description) || $curriculum->objectives_list)
                <x-ui.card title="Tentang kurikulum">
                    @if (filled($curriculum->description))
                        <p class="max-w-measure whitespace-pre-line text-fg-muted">{{ $curriculum->description }}</p>
                    @endif
                    @if ($curriculum->objectives_list)
                        <ul class="mt-4 space-y-2">
                            @foreach ($curriculum->objectives_list as $objective)
                                <li class="flex gap-2 text-fg"><x-ui.icon name="check-circle" class="mt-0.5 shrink-0 text-success" /> <span>{{ $objective }}</span></li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card>
            @endif

            <section aria-labelledby="pertemuan-heading">
                <h2 id="pertemuan-heading" class="section-title mb-3">Pertemuan</h2>
                <div class="card divide-y divide-line">
                    @forelse ($curriculum->activeSyllabuses as $syllabus)
                        <details class="group" @if ($loop->first) open @endif>
                            <summary class="flex min-h-[44px] cursor-pointer list-none items-center gap-4 px-card py-4 hover:bg-surface-2 [&::-webkit-details-marker]:hidden">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-surface-2 font-num text-sm font-semibold text-fg">{{ $syllabus->meeting_number }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block font-medium text-fg">{{ $syllabus->title }}</span>
                                    <span class="block text-sm text-fg-subtle">{{ $syllabus->duration_minutes }} menit · {{ $syllabus->activeMaterials->count() }} materi</span>
                                </span>
                                <x-ui.icon name="chevron-down" class="shrink-0 text-fg-subtle transition-transform duration-fast group-open:rotate-180 motion-reduce:transition-none" />
                            </summary>
                            <div class="space-y-5 px-card pb-5 sm:pl-[4.25rem]">
                                @if (filled($syllabus->description))
                                    <p class="text-sm text-fg-muted">{{ $syllabus->description }}</p>
                                @endif
                                <div class="grid gap-5 sm:grid-cols-2">
                                    @if ($syllabus->learning_objectives_list)
                                        <div>
                                            <p class="eyebrow mb-2">Tujuan</p>
                                            <ul class="space-y-1.5 text-sm text-fg">
                                                @foreach ($syllabus->learning_objectives_list as $item)
                                                    <li class="flex gap-2"><x-ui.icon name="check" size="16" class="mt-0.5 shrink-0 text-success" /> <span>{{ $item }}</span></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    @if ($syllabus->activities_list)
                                        <div>
                                            <p class="eyebrow mb-2">Kegiatan</p>
                                            <ol class="list-decimal space-y-1.5 pl-5 text-sm text-fg">
                                                @foreach ($syllabus->activities_list as $item)
                                                    <li>{{ $item }}</li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    @endif
                                </div>
                                @if ($syllabus->activeMaterials->isNotEmpty())
                                    <ul class="space-y-2">
                                        @foreach ($syllabus->activeMaterials as $material)
                                            <li>
                                                <a href="{{ route('teacher.materials.show', $material) }}" class="flex min-h-[44px] items-center gap-3 rounded-lg border border-line px-3 py-2 hover:bg-surface-2">
                                                    <span class="min-w-0 flex-1 truncate text-fg">{{ $material->title }}</span>
                                                    @include('admin.materials.partials.type-badge', ['type' => $material->type])
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                <x-ui.button variant="link" size="sm" arrow :href="route('teacher.syllabuses.show', $syllabus)">Detail pertemuan</x-ui.button>
                            </div>
                        </details>
                    @empty
                        <x-ui.empty-state icon="calendar-days" title="Kurikulum ini belum memiliki pertemuan aktif." />
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="space-y-6" aria-label="Kelas Anda">
            <x-ui.card title="Kelas Anda" subtitle="Progres rata-rata siswa aktif" padding="none">
                @forelse ($classes as $class)
                    @php($rate = $rates->get($class->id))
                    <div class="space-y-3 border-t border-line px-card py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-fg">{{ $class->name }}</p>
                                <p class="text-sm text-fg-subtle">{{ $class->active_enrollments_count }} siswa · {{ $class->status_label }}</p>
                            </div>
                        </div>
                        <x-ui.progress :value="$rate['percent'] ?? 0" show-value size="sm" label="Penyelesaian" />
                        <div class="flex flex-wrap gap-2">
                            <x-ui.button size="sm" variant="secondary" icon="chart-bar" :href="route('teacher.curriculums.class-progress', $class)">Progres</x-ui.button>
                            @if (in_array($class->status, ['planned', 'active'], true))
                                <x-ui.button size="sm" icon="clipboard-document-list" :href="route('class-reports.create', ['class_id' => $class->id])">Berita acara</x-ui.button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="border-t border-line"><x-ui.empty-state icon="user-group" title="Tidak ada kelas Anda yang memakai kurikulum ini." /></div>
                @endforelse
            </x-ui.card>
        </aside>
    </div>
</x-app-layout>
