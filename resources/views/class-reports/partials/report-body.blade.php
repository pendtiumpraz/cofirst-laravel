{{--
    Narrative of a meeting report (berita acara), shared by the teacher/admin, student
    and parent pages.
    Params: $report; $showInternal (bool: teacher follow-up notes); $showMedia (bool: documentation link)
--}}
@php($bloom = \App\Services\Academic\ClassReportService::bloomFields())
@php($filled = collect($bloom)->filter(fn ($meta, $field) => filled($report->{$field})))
<div class="space-y-6">
    <x-ui.card title="Yang dipelajari">
        <p class="max-w-measure whitespace-pre-line text-fg">{{ $report->learning_concept }}</p>
        @if ($report->syllabus || $report->material)
            <div class="mt-4 flex flex-wrap gap-2">
                @if ($report->syllabus)
                    <x-ui.badge icon="calendar-days">Pertemuan {{ $report->syllabus->meeting_number ?? $report->meeting_number }} · {{ $report->syllabus->title }}</x-ui.badge>
                @endif
                @if ($report->material)
                    <x-ui.badge icon="folder-open">{{ $report->material->title }}</x-ui.badge>
                @endif
            </div>
        @endif
    </x-ui.card>

    @if ($filled->isNotEmpty())
        <x-ui.card title="Capaian belajar" subtitle="Disusun mengikuti taksonomi Bloom, dari mengingat sampai mencipta.">
            <dl class="grid gap-5 sm:grid-cols-2">
                @foreach ($filled as $field => [$label, $hint])
                    <div>
                        <dt class="eyebrow mb-1">{{ $label }}</dt>
                        <dd class="whitespace-pre-line text-fg">{{ $report->{$field} }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-ui.card>
    @endif

    @if (filled($report->notes_recommendations))
        <x-ui.card title="Catatan & rekomendasi" variant="callout" style="--callout-c: var(--cf-success)">
            <p class="max-w-measure whitespace-pre-line text-fg">{{ $report->notes_recommendations }}</p>
        </x-ui.card>
    @endif

    @if (! empty($showInternal) && filled($report->follow_up_notes))
        <x-ui.card title="Tindak lanjut pengajar" subtitle="Catatan internal, tidak ditampilkan kepada siswa dan orang tua." variant="inset">
            <p class="max-w-measure whitespace-pre-line text-fg">{{ $report->follow_up_notes }}</p>
        </x-ui.card>
    @endif

    @if (! empty($showMedia) && filled($report->learning_media_link))
        <x-ui.card variant="inset">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <x-ui.icon name="photo" class="mt-0.5 shrink-0 text-fg-muted" />
                    <div>
                        <p class="font-medium text-fg">Dokumentasi belajar</p>
                        <p class="text-sm text-fg-subtle">Disimpan di {{ parse_url($report->learning_media_link, PHP_URL_HOST) }}. Jangan bagikan tautan ini ke pihak lain.</p>
                    </div>
                </div>
                <x-ui.button variant="secondary" icon-right="arrow-top-right-on-square" :href="$report->learning_media_link" target="_blank" rel="noopener noreferrer">Buka</x-ui.button>
            </div>
        </x-ui.card>
    @endif
</div>
