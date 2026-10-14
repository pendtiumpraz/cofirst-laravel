@php($f = \App\Services\Academic\Format::class)
<x-app-layout :title="$material->title">
    <x-ui.page-header :title="$material->title" :eyebrow="$material->meeting_range" :subtitle="$material->description"
        :breadcrumbs="array_merge(
            ['Materi' => route('student.materials.index')],
            $class ? [$class->name => route('student.materials.by-class', $class)] : [],
            [$material->title => null]
        )">
        <x-slot:badge>
            @include('admin.materials.partials.type-badge', ['type' => $material->type])
        </x-slot:badge>
    </x-ui.page-header>

    <div class="grid gap-gutter lg:grid-cols-3">
        <div class="lg:col-span-2">
            @include('admin.materials.partials.viewer', [
                'downloadUrl' => route('student.materials.download', $material),
            ])
        </div>

        <aside class="space-y-6">
            <x-ui.card title="Catatanmu" variant="inset">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-fg-muted">Pertemuan</dt><dd class="text-right text-fg">{{ $material->syllabus?->title }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-fg-muted">Pertama dibuka</dt><dd class="text-right text-fg">{{ $f::dateTime($stats['first'] ?? null, 'd M Y') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-fg-muted">Terakhir dibuka</dt><dd class="text-right text-fg">{{ $f::dateTime($stats['last'] ?? null, 'd M, H.i') }}</dd></div>
                </dl>
                <p class="mt-4 text-xs text-fg-subtle">Kami mencatat kapan materi dibuka agar pengajar dan orang tuamu bisa melihat perkembangan belajarmu.</p>
            </x-ui.card>
            @if ($class)
                <x-ui.button variant="secondary" block icon="arrow-left" :href="route('student.materials.by-class', $class)">Kembali ke materi kelas</x-ui.button>
            @endif
        </aside>
    </div>
</x-app-layout>
