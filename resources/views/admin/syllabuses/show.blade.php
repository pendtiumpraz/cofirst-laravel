<x-app-layout :title="'Pertemuan '.$syllabus->meeting_number">
    <x-ui.page-header :title="$syllabus->title" :eyebrow="'Pertemuan '.$syllabus->meeting_number"
        :subtitle="$syllabus->curriculum ? $syllabus->curriculum->title.' · '.($syllabus->curriculum->course?->name ?? '') : null"
        :breadcrumbs="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Kurikulum', 'url' => route('admin.curriculums.index')],
            ['label' => $syllabus->curriculum?->title ?? 'Kurikulum diarsipkan', 'url' => $syllabus->curriculum ? route('admin.curriculums.show', $syllabus->curriculum) : null],
            ['label' => 'Pertemuan '.$syllabus->meeting_number],
        ]">
        <x-slot:badge>
            <x-ui.badge :tone="$syllabus->status === 'active' ? 'success' : 'neutral'" dot>{{ $syllabus->status === 'active' ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
        </x-slot:badge>
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="pencil-square" :href="route('admin.syllabuses.edit', $syllabus)">Ubah</x-ui.button>
            <x-ui.button icon="plus" :href="route('admin.materials.create', ['syllabus_id' => $syllabus->id])">Tambah materi</x-ui.button>
        </x-slot:actions>
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
                        @forelse ($syllabus->activities_list as $item)
                            <p class="mb-2 flex gap-2 text-fg"><span class="mt-2 size-1.5 shrink-0 rounded-full bg-brand" aria-hidden="true"></span> <span>{{ $item }}</span></p>
                        @empty
                            <p class="text-sm text-fg-subtle">Belum diisi.</p>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Materi" subtitle="Urutan di sini sama dengan urutan yang dilihat pengajar dan siswa." padding="none">
                @forelse ($syllabus->materials as $material)
                    <div class="flex flex-col gap-3 border-t border-line px-card py-4 sm:flex-row sm:items-center">
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            <span class="mt-0.5 font-num text-sm text-fg-subtle">{{ $loop->iteration }}.</span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.materials.show', $material) }}" class="font-medium text-fg hover:text-brand-text">{{ $material->title }}</a>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    @include('admin.materials.partials.type-badge', ['type' => $material->type])
                                    @if ($material->isMultiMeeting())
                                        <x-ui.badge>{{ $material->meeting_range }}</x-ui.badge>
                                    @endif
                                    @if ($material->file_path)
                                        <x-ui.badge icon="paper-clip">Berkas</x-ui.badge>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-2 sm:justify-end">
                            @include('admin.curriculums.partials.status-toggle', [
                                'action' => route('admin.materials.toggle-status', $material),
                                'active' => $material->status === 'active',
                                'label' => 'Status materi '.$material->title,
                            ])
                            <div class="flex gap-1">
                                <form method="POST" action="{{ route('admin.materials.move', $material) }}">
                                    @csrf
                                    <input type="hidden" name="direction" value="up">
                                    <x-ui.button type="submit" variant="ghost" size="sm" icon="chevron-up" label="Naikkan {{ $material->title }}" :disabled="$loop->first" />
                                </form>
                                <form method="POST" action="{{ route('admin.materials.move', $material) }}">
                                    @csrf
                                    <input type="hidden" name="direction" value="down">
                                    <x-ui.button type="submit" variant="ghost" size="sm" icon="chevron-down" label="Turunkan {{ $material->title }}" :disabled="$loop->last" />
                                </form>
                                <x-ui.button variant="ghost" size="sm" icon="pencil-square" label="Ubah {{ $material->title }}" :href="route('admin.materials.edit', $material)" />
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="border-t border-line">
                        <x-ui.empty-state icon="folder-open" title="Belum ada materi untuk pertemuan ini."
                            description="Tambahkan dokumen, video, latihan, kuis atau proyek.">
                            <x-slot:actions>
                                <x-ui.button icon="plus" :href="route('admin.materials.create', ['syllabus_id' => $syllabus->id])">Tambah materi</x-ui.button>
                            </x-slot:actions>
                        </x-ui.empty-state>
                    </div>
                @endforelse
            </x-ui.card>

            <nav class="flex flex-col gap-3 sm:flex-row sm:justify-between" aria-label="Navigasi pertemuan">
                @if ($previous)
                    <x-ui.button variant="secondary" icon="arrow-left" :href="route('admin.syllabuses.show', $previous)">Pertemuan {{ $previous->meeting_number }}</x-ui.button>
                @else
                    <span></span>
                @endif
                @if ($next)
                    <x-ui.button variant="secondary" icon-right="arrow-right" :href="route('admin.syllabuses.show', $next)">Pertemuan {{ $next->meeting_number }}</x-ui.button>
                @endif
            </nav>
        </div>

        <div class="space-y-6">
            <x-ui.card title="Ringkasan" variant="inset">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-fg-muted">Durasi</dt><dd class="num text-fg">{{ $syllabus->duration_minutes }} menit</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-fg-muted">Materi</dt><dd class="num text-fg">{{ $syllabus->materials->count() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-fg-muted">Berita acara</dt><dd class="num text-fg">{{ $reportCount }}</dd></div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-fg-muted">Status</dt>
                        <dd>
                            @include('admin.curriculums.partials.status-toggle', [
                                'action' => route('admin.syllabuses.toggle-status', $syllabus),
                                'active' => $syllabus->status === 'active',
                                'label' => 'Status pertemuan',
                            ])
                        </dd>
                    </div>
                </dl>
                <x-ui.divider class="my-4" />
                @if ($reportCount === 0)
                    <x-ui.confirm-delete :action="route('admin.syllabuses.destroy', $syllabus)" label="Arsipkan pertemuan"
                        title="Arsipkan pertemuan ini?" confirm-text="Ya, arsipkan"
                        message="Pertemuan dan materinya disembunyikan dari semua portal." />
                @else
                    <p class="text-sm text-fg-subtle">Pertemuan yang sudah dilaporkan tidak dapat diarsipkan agar riwayat siswa tetap utuh. Nonaktifkan bila perlu.</p>
                @endif
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
