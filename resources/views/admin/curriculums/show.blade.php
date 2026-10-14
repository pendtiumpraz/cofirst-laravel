@php($types = \App\Models\Curriculum::getTypes())
<x-app-layout :title="$curriculum->title">
    <x-ui.page-header :title="$curriculum->title" eyebrow="Kurikulum"
        :subtitle="$curriculum->course?->name"
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Kurikulum' => route('admin.curriculums.index'), $curriculum->title => null]">
        <x-slot:badge>
            <x-ui.badge outline>{{ $types[$curriculum->type] ?? $curriculum->type }}</x-ui.badge>
            <x-ui.badge :tone="$curriculum->status === 'active' ? 'success' : 'neutral'" dot>{{ $curriculum->status === 'active' ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
        </x-slot:badge>
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="pencil-square" :href="route('admin.curriculums.edit', $curriculum)">Ubah</x-ui.button>
            <x-ui.button icon="plus" :href="route('admin.syllabuses.create', ['curriculum_id' => $curriculum->id])">Tambah pertemuan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.metric-strip class="mb-8" :stats="[
        ['label' => 'Pertemuan', 'value' => (string) $curriculum->syllabuses->count(), 'icon' => 'calendar-days'],
        ['label' => 'Materi', 'value' => (string) $curriculum->syllabuses->sum('materials_count'), 'icon' => 'folder-open'],
        ['label' => 'Kelas memakai', 'value' => (string) $curriculum->classes->count(), 'icon' => 'user-group'],
        ['label' => 'Durasi', 'value' => $curriculum->duration_weeks ? (string) $curriculum->duration_weeks : null, 'unit' => 'minggu', 'icon' => 'clock'],
    ]" />

    <div class="grid gap-gutter lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            @if (filled($curriculum->description) || $curriculum->objectives_list)
                <x-ui.card title="Tentang kurikulum">
                    @if (filled($curriculum->description))
                        <p class="max-w-measure whitespace-pre-line text-fg-muted">{{ $curriculum->description }}</p>
                    @endif
                    @if ($curriculum->objectives_list)
                        <p class="eyebrow mb-2 mt-5">Tujuan pembelajaran</p>
                        <ul class="space-y-2">
                            @foreach ($curriculum->objectives_list as $objective)
                                <li class="flex gap-2 text-fg"><x-ui.icon name="check-circle" class="mt-0.5 shrink-0 text-success" /> <span>{{ $objective }}</span></li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card>
            @endif

            <x-ui.card title="Pertemuan" subtitle="Urutan pertemuan menentukan kapan materi terbuka untuk siswa." padding="none">
                <x-slot:actions>
                    <x-ui.button variant="link" size="sm" :href="route('admin.syllabuses.index', ['curriculum_id' => $curriculum->id])" arrow>Tabel lengkap</x-ui.button>
                </x-slot:actions>

                @forelse ($curriculum->syllabuses as $syllabus)
                    <a href="{{ route('admin.syllabuses.show', $syllabus) }}"
                        class="flex items-center gap-4 border-t border-line px-card py-4 transition-colors duration-fast hover:bg-surface-2 motion-reduce:transition-none">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-surface-2 font-num text-sm font-semibold text-fg">{{ $syllabus->meeting_number }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-medium text-fg">{{ $syllabus->title }}</span>
                            <span class="block text-sm text-fg-subtle">{{ $syllabus->duration_minutes }} menit · {{ $syllabus->materials_count }} materi</span>
                        </span>
                        @if ($syllabus->status !== 'active')
                            <x-ui.badge>Nonaktif</x-ui.badge>
                        @endif
                        <x-ui.icon name="chevron-right" class="shrink-0 text-fg-subtle" />
                    </a>
                @empty
                    <div class="border-t border-line">
                        <x-ui.empty-state icon="calendar-days" title="Belum ada pertemuan."
                            description="Tambahkan pertemuan pertama, lalu lengkapi tujuan, kegiatan dan materinya.">
                            <x-slot:actions>
                                <x-ui.button icon="plus" :href="route('admin.syllabuses.create', ['curriculum_id' => $curriculum->id])">Tambah pertemuan</x-ui.button>
                            </x-slot:actions>
                        </x-ui.empty-state>
                    </div>
                @endforelse
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card title="Kelas yang memakai" padding="none">
                @forelse ($curriculum->classes as $class)
                    <div class="border-t border-line px-card py-3">
                        <p class="font-medium text-fg">{{ $class->name }}</p>
                        <p class="text-sm text-fg-subtle">
                            {{ $class->teachers->pluck('name')->join(', ') ?: 'Belum ada pengajar' }} · {{ $class->active_enrollments_count }} siswa aktif
                        </p>
                    </div>
                @empty
                    <div class="border-t border-line">
                        <x-ui.empty-state icon="user-group" title="Belum dipakai kelas mana pun." description="Pilih kurikulum ini saat membuat atau mengubah kelas." />
                    </div>
                @endforelse
            </x-ui.card>

            <x-ui.card title="Pengaturan" variant="inset">
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm text-fg-muted">Status</span>
                        @include('admin.curriculums.partials.status-toggle', [
                            'action' => route('admin.curriculums.toggle-status', $curriculum),
                            'active' => $curriculum->status === 'active',
                            'label' => 'Status kurikulum',
                        ])
                    </div>
                    <x-ui.divider />
                    @if ($curriculum->classes->isEmpty())
                        <x-ui.confirm-delete :action="route('admin.curriculums.destroy', $curriculum)" label="Arsipkan kurikulum"
                            title="Arsipkan kurikulum ini?" confirm-text="Ya, arsipkan"
                            message="Kurikulum, pertemuan dan materinya disembunyikan. Riwayat berita acara tetap tersimpan." />
                    @else
                        <p class="text-sm text-fg-subtle">Kurikulum yang dipakai kelas tidak dapat diarsipkan. Nonaktifkan bila sudah tidak dipakai untuk kelas baru.</p>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
