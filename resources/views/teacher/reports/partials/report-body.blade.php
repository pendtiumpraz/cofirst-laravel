{{--
    Body of a progress report (laporan perkembangan), shared by the teacher, admin and
    student pages. Params: $report (with student, teacher, className.course loaded),
    optional $hideStudent (bool).
--}}
@php($f = \App\Services\Academic\Format::class)
<div class="grid gap-gutter lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <x-ui.card title="Perkembangan">
            <x-ui.progress :value="(float) $report->progress_percentage" show-value size="lg"
                :tone="\App\Services\Academic\ProgressReportService::progressTone($report->progress_percentage)"
                label="Progres belajar" />
            <div class="mt-6 max-w-measure whitespace-pre-line text-fg">{{ $report->content }}</div>
        </x-ui.card>

        @if (filled($report->recommendations))
            <x-ui.card title="Rekomendasi" variant="callout" style="--callout-c: var(--cf-success)">
                <div class="max-w-measure whitespace-pre-line text-fg">{{ $report->recommendations }}</div>
            </x-ui.card>
        @endif
    </div>

    <x-ui.card title="Rincian" variant="inset">
        <dl class="space-y-3 text-sm">
            @empty($hideStudent)
                <div class="flex justify-between gap-3"><dt class="text-fg-muted">Siswa</dt><dd class="text-right font-medium text-fg">{{ $report->student?->name ?? '—' }}</dd></div>
            @endempty
            <div class="flex justify-between gap-3"><dt class="text-fg-muted">Kelas</dt><dd class="text-right text-fg">{{ $report->className?->name ?? '—' }}</dd></div>
            @if ($report->className?->course)
                <div class="flex justify-between gap-3"><dt class="text-fg-muted">Kursus</dt><dd class="text-right text-fg">{{ $report->className->course->name }}</dd></div>
            @endif
            <div class="flex justify-between gap-3"><dt class="text-fg-muted">Pengajar</dt><dd class="text-right text-fg">{{ $report->teacher?->name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-fg-muted">Jenis</dt><dd><x-ui.badge :tone="\App\Services\Academic\ProgressReportService::typeTone($report->type)">{{ $report->type_label }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-3"><dt class="text-fg-muted">Tanggal</dt><dd class="num text-fg">{{ $f::date($report->report_date, 'd F Y') }}</dd></div>
            @if ($report->score !== null)
                <div class="flex justify-between gap-3"><dt class="text-fg-muted">Nilai</dt><dd class="num text-fg">{{ rtrim(rtrim(number_format((float) $report->score, 2, ',', '.'), '0'), ',') }}</dd></div>
            @endif
        </dl>
    </x-ui.card>
</div>
