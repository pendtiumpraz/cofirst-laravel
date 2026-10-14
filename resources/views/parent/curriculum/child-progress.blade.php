@php($f = \App\Services\Academic\Format::class)
<x-app-layout :title="$child->name.' · '.$class->name">
    <x-ui.page-header :title="$class->name" :eyebrow="$child->name"
        :subtitle="($class->curriculum?->title ?? 'Kurikulum').' · '.($class->course?->name ?? '')"
        :breadcrumbs="['Kurikulum anak' => route('parent.curriculum.index'), $child->name.' · '.$class->name => null]">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="folder-open" :href="route('parent.progress.child-materials', [$child, $class])">Materi</x-ui.button>
            <x-ui.button variant="secondary" icon="chart-bar" :href="route('parent.progress.child', $child)">Progres anak</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (! $summary->hasCurriculum())
        <x-ui.empty-state size="page" icon="queue-list" title="Kurikulum kelas ini belum ditetapkan."
            description="Kami akan memberi tahu Anda setelah kurikulum kelas dilengkapi." />
    @else
        <div class="grid gap-gutter lg:grid-cols-3">
            <div class="lg:col-span-2">
                @include('student.curriculum.partials.timeline', ['materialUrl' => null, 'accessStats' => null])
            </div>

            <aside class="space-y-6">
                <x-ui.card title="Ringkasan" variant="highlight">
                    <div class="flex items-center gap-5">
                        <x-ui.progress-ring :value="$summary->percent()" :size="88" marker label="Progres kurikulum">
                            <span class="font-num text-lg text-fg">{{ (int) round($summary->percent()) }}%</span>
                        </x-ui.progress-ring>
                        <div class="space-y-1 text-sm">
                            <p class="text-fg"><span class="font-num font-semibold">{{ $summary->completed() }}</span> dari {{ $summary->total() }} pertemuan selesai</p>
                            <p class="text-fg-subtle">{{ $summary->label() }}</p>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card title="Catatan pertemuan terbaru" padding="none">
                    @if (! $reportsVisible)
                        <div class="border-t border-line px-card py-4 text-sm text-fg-subtle">Catatan pertemuan sedang tidak dibagikan kepada orang tua. Hubungi admin bila Anda memerlukannya.</div>
                    @else
                        @forelse ($latestReports as $report)
                            <a href="{{ route('parent.progress.meeting', [$child, $report]) }}" class="block border-t border-line px-card py-3 hover:bg-surface-2">
                                <p class="font-medium text-fg">Pertemuan {{ $report->meeting_number }} · {{ $f::date($report->report_date, 'd M') }}</p>
                                <p class="line-clamp-2 text-sm text-fg-subtle">{{ $report->notes_recommendations ?: $report->learning_concept }}</p>
                            </a>
                        @empty
                            <div class="border-t border-line"><x-ui.empty-state icon="clipboard-document-list" title="Belum ada catatan pertemuan." /></div>
                        @endforelse
                        @if ($latestReports->isNotEmpty())
                            <div class="border-t border-line px-card py-3">
                                <x-ui.button variant="link" size="sm" arrow :href="route('parent.progress.meetings', ['child' => $child, 'class_id' => $class->id])">Semua catatan</x-ui.button>
                            </div>
                        @endif
                    @endif
                </x-ui.card>
            </aside>
        </div>
    @endif
</x-app-layout>
