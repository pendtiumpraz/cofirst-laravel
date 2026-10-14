@php($f = \App\Services\Academic\Format::class)
<x-app-layout title="Materi">
    <x-ui.page-header title="Materi belajar"
        subtitle="Materi terbuka mengikuti pertemuan yang sudah kamu jalani. Ulangi kapan saja." />

    @if ($classes->isEmpty())
        <x-ui.empty-state size="page" icon="folder-open" title="Belum ada materi untukmu."
            description="Materi muncul setelah kamu terdaftar di kelas dan pertemuan pertama selesai." />
    @else
        <div class="grid gap-gutter lg:grid-cols-3">
            <div class="grid gap-gutter sm:grid-cols-2 lg:col-span-2">
                @foreach ($classes as $class)
                    @php($summary = $summaries->get($class->id))
                    @php($total = $summary ? $summary->meetings->sum(fn ($m) => $m['syllabus']->activeMaterials->count()) : 0)
                    @php($open = count($unlocked->get($class->id, [])))
                    <x-ui.card as="article" :href="route('student.materials.by-class', $class)" :title="$class->name" :subtitle="$class->curriculum?->title ?? 'Kurikulum belum ditetapkan'">
                        <div class="flex items-baseline gap-2">
                            <span class="font-num text-kpi text-fg">{{ $open }}</span>
                            <span class="text-sm text-fg-muted">dari {{ $total }} materi terbuka</span>
                        </div>
                        <x-ui.progress class="mt-4" :value="$summary?->percent() ?? 0" size="sm" show-value label="Pertemuan selesai" />
                    </x-ui.card>
                @endforeach
            </div>

            <x-ui.card title="Terakhir kamu buka" padding="none">
                @forelse ($recent as $access)
                    <a href="{{ route('student.materials.show', $access->material_id) }}" class="flex min-h-[44px] items-center gap-3 border-t border-line px-card py-3 hover:bg-surface-2">
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-medium text-fg">{{ $access->material?->title }}</span>
                            <span class="block text-sm text-fg-subtle">{{ $f::dateTime($access->accessed_at, 'd M, H.i') }}</span>
                        </span>
                        @include('admin.materials.partials.type-badge', ['type' => $access->material?->type])
                    </a>
                @empty
                    <div class="border-t border-line">
                        <x-ui.empty-state icon="book-open" title="Belum ada materi yang dibuka." description="Mulai dari kelasmu di sebelah." />
                    </div>
                @endforelse
            </x-ui.card>
        </div>
    @endif
</x-app-layout>
