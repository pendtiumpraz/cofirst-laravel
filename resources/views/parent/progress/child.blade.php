@php($f = \App\Services\Academic\Format::class)
<x-app-layout :title="'Progres · '.$child->name">
    <x-ui.page-header :title="$child->name" eyebrow="Progres anak"
        :breadcrumbs="['Progres anak' => route('parent.progress.index'), $child->name => null]">
        @if ($reportsVisible)
            <x-slot:actions>
                <x-ui.button variant="secondary" icon="clipboard-document-list" :href="route('parent.progress.meetings', $child)">Catatan pertemuan</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @if ($classes->isEmpty())
        <x-ui.empty-state size="page" icon="academic-cap" title="{{ $child->name }} belum terdaftar di kelas mana pun." />
    @else
        <div class="grid gap-gutter lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                @foreach ($classes as $class)
                    @php($summary = $summaries->get($class->id))
                    @php($att = $attendance->get($class->id, collect()))
                    @php($attended = (int) ($att['present'] ?? 0) + (int) ($att['late'] ?? 0))
                    @php($recorded = (int) $att->sum())
                    <x-ui.card as="article" :title="$class->name" :subtitle="($class->curriculum?->title ?? 'Kurikulum belum ditetapkan').' · '.($class->teachers->pluck('name')->join(', ') ?: 'Pengajar belum ditetapkan')">
                        @if ($summary?->hasCurriculum())
                            <x-ui.progress :value="$summary->percent()" show-value size="lg"
                                :tone="$summary->percent() >= 100 ? 'success' : 'brand'"
                                label="{{ $summary->completed() }} dari {{ $summary->total() }} pertemuan selesai" />
                            <dl class="mt-5 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                                <div><dt class="text-fg-muted">Sedang berjalan</dt><dd class="font-num text-lg text-fg">{{ $summary->inProgress() }}</dd></div>
                                <div><dt class="text-fg-muted">Kehadiran</dt><dd class="font-num text-lg text-fg">{{ $recorded > 0 ? $f::percent(round($attended / $recorded * 100)) : '—' }}</dd></div>
                                <div class="col-span-2 sm:col-span-1"><dt class="text-fg-muted">Berikutnya</dt><dd class="text-fg">{{ $summary->nextMeeting()?->title ?? 'Semua selesai' }}</dd></div>
                            </dl>
                        @else
                            <p class="text-sm text-fg-subtle">Progres per pertemuan muncul setelah kurikulum kelas ditetapkan.</p>
                        @endif
                        <x-slot:footer>
                            <x-ui.button size="sm" variant="secondary" icon="folder-open" :href="route('parent.progress.child-materials', [$child, $class])">Materi</x-ui.button>
                            <x-ui.button size="sm" icon="queue-list" :href="route('parent.curriculum.child-progress', [$child, $class])">Kurikulum</x-ui.button>
                        </x-slot:footer>
                    </x-ui.card>
                @endforeach
            </div>

            <aside class="space-y-6">
                @if ($reportsVisible)
                    <x-ui.card title="Catatan pertemuan terbaru" padding="none">
                        @forelse ($latestReports as $report)
                            <a href="{{ route('parent.progress.meeting', [$child, $report]) }}" class="block border-t border-line px-card py-3 hover:bg-surface-2">
                                <p class="font-medium text-fg">{{ $report->className?->name }} · Pertemuan {{ $report->meeting_number }}</p>
                                <p class="text-sm text-fg-subtle">{{ $f::date($report->report_date) }} · {{ $report->teacher?->name }}</p>
                            </a>
                        @empty
                            <div class="border-t border-line"><x-ui.empty-state icon="clipboard-document-list" title="Belum ada catatan pertemuan." /></div>
                        @endforelse
                    </x-ui.card>
                @endif

                <x-ui.card title="Materi yang terakhir dibuka" padding="none">
                    @forelse ($recentMaterials as $access)
                        <div class="flex items-center justify-between gap-3 border-t border-line px-card py-3">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-fg">{{ $access->material?->title }}</p>
                                <p class="text-sm text-fg-subtle">{{ $f::dateTime($access->accessed_at, 'd M, H.i') }}</p>
                            </div>
                            @include('admin.materials.partials.type-badge', ['type' => $access->material?->type])
                        </div>
                    @empty
                        <div class="border-t border-line"><x-ui.empty-state icon="book-open" title="Belum ada materi yang dibuka." /></div>
                    @endforelse
                </x-ui.card>
            </aside>
        </div>
    @endif
</x-app-layout>
