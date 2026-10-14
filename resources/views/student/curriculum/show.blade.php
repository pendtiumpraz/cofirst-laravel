<x-app-layout :title="'Kurikulum · '.$class->name">
    <x-ui.page-header :title="$class->name" :eyebrow="$class->curriculum?->title ?? 'Kurikulum'"
        :subtitle="$class->course?->name.($class->teachers->isNotEmpty() ? ' · bersama '.$class->teachers->pluck('name')->join(', ') : '')"
        :breadcrumbs="['Kurikulum' => route('student.curriculum.index'), $class->name => null]">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="chart-bar" :href="route('student.progress.class', $class)">Progres</x-ui.button>
            <x-ui.button icon="folder-open" :href="route('student.materials.by-class', $class)">Materi</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (! $summary->hasCurriculum())
        <x-ui.empty-state size="page" icon="queue-list" title="Kurikulum kelas ini belum ditetapkan."
            description="Pengajar atau admin akan segera melengkapinya. Sementara itu, tanyakan materi lewat chat kelas." />
    @else
        <div class="grid gap-gutter lg:grid-cols-3">
            <div class="lg:col-span-2">
                @include('student.curriculum.partials.timeline', [
                    'materialUrl' => fn ($material) => route('student.materials.show', $material),
                ])
            </div>

            <aside class="space-y-6">
                <x-ui.card title="Progresmu" variant="highlight">
                    <div class="flex items-center gap-5">
                        <x-ui.progress-ring :value="$summary->percent()" :size="88" marker label="Progres kurikulum">
                            <span class="font-num text-lg text-fg">{{ (int) round($summary->percent()) }}%</span>
                        </x-ui.progress-ring>
                        <div class="space-y-1 text-sm">
                            <p class="text-fg"><span class="font-num font-semibold">{{ $summary->completed() }}</span> dari {{ $summary->total() }} pertemuan selesai</p>
                            <p class="text-fg-subtle">{{ count($unlockedIds) }} materi sudah terbuka</p>
                        </div>
                    </div>
                </x-ui.card>

                @if (filled($class->curriculum?->description) || $class->curriculum?->objectives_list)
                    <x-ui.card title="Tentang kurikulum">
                        @if (filled($class->curriculum->description))
                            <p class="text-sm text-fg-muted">{{ $class->curriculum->description }}</p>
                        @endif
                        @if ($class->curriculum->objectives_list)
                            <ul class="mt-3 space-y-1.5 text-sm text-fg">
                                @foreach ($class->curriculum->objectives_list as $item)
                                    <li class="flex gap-2"><x-ui.icon name="check" size="16" class="mt-0.5 shrink-0 text-success" /> <span>{{ $item }}</span></li>
                                @endforeach
                            </ul>
                        @endif
                    </x-ui.card>
                @endif
            </aside>
        </div>
    @endif
</x-app-layout>
