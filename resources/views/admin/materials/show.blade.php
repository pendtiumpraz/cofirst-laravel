@php($f = \App\Services\Academic\Format::class)
<x-app-layout :title="$material->title">
    <x-ui.page-header :title="$material->title" :eyebrow="$material->meeting_range"
        :subtitle="$material->description"
        :breadcrumbs="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Materi', 'url' => route('admin.materials.index')],
            ['label' => $material->syllabus ? 'Pertemuan '.$material->syllabus->meeting_number : 'Pertemuan diarsipkan', 'url' => $material->syllabus ? route('admin.syllabuses.show', $material->syllabus) : null],
            ['label' => $material->title],
        ]">
        <x-slot:badge>
            @include('admin.materials.partials.type-badge', ['type' => $material->type])
            <x-ui.badge :tone="$material->status === 'active' ? 'success' : 'neutral'" dot>{{ $material->status === 'active' ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
        </x-slot:badge>
        <x-slot:actions>
            <x-ui.button icon="pencil-square" :href="route('admin.materials.edit', $material)">Ubah</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-gutter lg:grid-cols-3">
        <div class="lg:col-span-2">
            @include('admin.materials.partials.viewer', [
                'downloadUrl' => route('admin.materials.download', $material),
            ])
        </div>

        <div class="space-y-6">
            <x-ui.card title="Letak materi" variant="inset">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-fg-muted">Kurikulum</dt>
                        <dd class="text-right text-fg">
                            @if ($material->syllabus?->curriculum)
                                <a class="link" href="{{ route('admin.curriculums.show', $material->syllabus->curriculum) }}">{{ $material->syllabus->curriculum->title }}</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3"><dt class="text-fg-muted">Pertemuan</dt><dd class="text-right text-fg">{{ $material->syllabus?->title ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-fg-muted">Urutan</dt><dd class="num text-fg">{{ $material->order }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-fg-muted">Dipakai di berita acara</dt><dd class="num text-fg">{{ $reportCount }}</dd></div>
                </dl>
            </x-ui.card>

            <x-ui.card title="Akses siswa" subtitle="Hanya nama yang ditampilkan (minimisasi data).">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-fg-muted">Dibuka</p>
                        <p class="font-num text-2xl text-fg">{{ number_format((int) $stats?->views, 0, ',', '.') }}<span class="text-sm text-fg-subtle"> kali</span></p>
                    </div>
                    <div>
                        <p class="text-sm text-fg-muted">Siswa</p>
                        <p class="font-num text-2xl text-fg">{{ number_format((int) $stats?->students, 0, ',', '.') }}</p>
                    </div>
                </div>
                @if ($recent->isNotEmpty())
                    <ul class="mt-5 divide-y divide-line border-t border-line">
                        @foreach ($recent as $access)
                            <li class="flex items-center justify-between gap-3 py-2.5 text-sm">
                                <span class="truncate text-fg">{{ $access->student?->name ?? 'Pengguna dihapus' }}</span>
                                <span class="shrink-0 text-fg-subtle">{{ $f::dateTime($access->accessed_at, 'd M, H.i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 text-sm text-fg-subtle">Belum ada siswa yang membuka materi ini.</p>
                @endif
            </x-ui.card>

            <x-ui.card variant="inset">
                <x-ui.confirm-delete :action="route('admin.materials.destroy', $material)" label="Arsipkan materi"
                    title="Arsipkan materi ini?" confirm-text="Ya, arsipkan"
                    message="Materi disembunyikan dari semua portal. Berita acara yang memakainya tetap tersimpan." />
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
