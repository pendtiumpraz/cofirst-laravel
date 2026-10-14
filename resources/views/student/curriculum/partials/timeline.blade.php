{{--
    Meeting timeline of one class (student and parent views).
    Params: $summary (ProgressSummary built with materials), $unlockedIds (int[]),
    $materialUrl (callable(Material): string|null — null renders titles without links),
    $accessStats (Collection keyed by material id, optional)
--}}
@php
    $f = \App\Services\Academic\Format::class;
    $lockHint = match (\App\Services\Academic\AcademicSettings::unlockMode()) {
        'upcoming' => 'Terbuka menjelang pertemuan ini',
        default => 'Terbuka setelah pertemuan ini selesai',
    };
@endphp

<ol class="relative space-y-4">
    @foreach ($summary->meetings as $meeting)
        @php($syllabus = $meeting['syllabus'])
        @php($done = $meeting['status'] === 'completed')
        @php($running = $meeting['status'] === 'in_progress')
        <li class="relative flex gap-4">
            @unless ($loop->last)
                <span class="absolute left-5 top-12 -bottom-4 w-px bg-line" aria-hidden="true"></span>
            @endunless
            <span @class([
                'relative z-[1] flex size-10 shrink-0 items-center justify-center rounded-full border font-num text-sm font-semibold',
                'border-success bg-success-soft text-success' => $done,
                'border-info bg-info-soft text-info' => $running,
                'border-line bg-surface-2 text-fg-muted' => ! $done && ! $running,
            ]) aria-hidden="true">
                @if ($done)
                    <x-ui.icon name="check" size="18" />
                @else
                    {{ $meeting['number'] }}
                @endif
            </span>

            <div class="card min-w-0 flex-1 p-card">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="eyebrow">Pertemuan {{ $meeting['number'] }}</p>
                        <h3 class="card-title mt-1">{{ $syllabus->title }}</h3>
                    </div>
                    @include('student.curriculum.partials.meeting-status', ['status' => $meeting['status']])
                </div>

                @if ($done && $meeting['completed_at'])
                    <p class="mt-1 text-sm text-fg-subtle">Selesai {{ $f::date($meeting['completed_at'], 'd F Y') }}</p>
                @endif
                @if (filled($meeting['notes']))
                    <p class="mt-2 text-sm text-fg-muted">{{ $meeting['notes'] }}</p>
                @endif

                @if ($syllabus->learning_objectives_list)
                    <details class="group mt-3">
                        <summary class="inline-flex min-h-[44px] cursor-pointer list-none items-center gap-1 text-sm font-medium text-brand-text [&::-webkit-details-marker]:hidden">
                            Tujuan pertemuan
                            <x-ui.icon name="chevron-down" size="16" class="transition-transform duration-fast group-open:rotate-180 motion-reduce:transition-none" />
                        </summary>
                        <ul class="mt-1 space-y-1.5 text-sm text-fg">
                            @foreach ($syllabus->learning_objectives_list as $item)
                                <li class="flex gap-2"><x-ui.icon name="check" size="16" class="mt-0.5 shrink-0 text-success" /> <span>{{ $item }}</span></li>
                            @endforeach
                        </ul>
                    </details>
                @endif

                @if ($syllabus->activeMaterials->isNotEmpty())
                    <ul class="mt-4 space-y-2 border-t border-line pt-4">
                        @foreach ($syllabus->activeMaterials as $material)
                            @php($open = in_array($material->id, $unlockedIds, true))
                            @php($seen = isset($accessStats) ? $accessStats->get($material->id) : null)
                            <li>
                                @if ($open && $materialUrl)
                                    <a href="{{ $materialUrl($material) }}" class="flex min-h-[44px] items-center gap-3 rounded-lg border border-line px-3 py-2 hover:bg-surface-2">
                                        <x-ui.icon name="lock-open" class="shrink-0 text-success" label="Terbuka" />
                                        <span class="min-w-0 flex-1 truncate text-fg">{{ $material->title }}</span>
                                        @if ($seen)
                                            <span class="hidden text-xs text-fg-subtle sm:inline">Dibuka {{ $f::date($seen['last'], 'd M') }}</span>
                                        @else
                                            <x-ui.badge tone="primary">Baru</x-ui.badge>
                                        @endif
                                        @include('admin.materials.partials.type-badge', ['type' => $material->type])
                                    </a>
                                @else
                                    <div @class(['flex min-h-[44px] items-center gap-3 rounded-lg border border-line px-3 py-2', 'bg-surface-2' => ! $open])>
                                        <x-ui.icon :name="$open ? 'lock-open' : 'lock-closed'" @class(['shrink-0', 'text-success' => $open, 'text-fg-subtle' => ! $open]) :label="$open ? 'Terbuka' : 'Terkunci'" />
                                        <span @class(['min-w-0 flex-1 truncate', 'text-fg' => $open, 'text-fg-subtle' => ! $open])>{{ $material->title }}</span>
                                        @unless ($open)
                                            <span class="hidden text-xs text-fg-subtle sm:inline">{{ $lockHint }}</span>
                                        @endunless
                                        @include('admin.materials.partials.type-badge', ['type' => $material->type])
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </li>
    @endforeach
</ol>
