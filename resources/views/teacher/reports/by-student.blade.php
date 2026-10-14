@php($f = \App\Services\Academic\Format::class)
<x-app-layout :title="'Laporan · '.$student['name']">
    <x-ui.page-header :title="$student['name']" eyebrow="Riwayat laporan"
        subtitle="Laporan perkembangan siswa ini di kelas-kelas Anda."
        :breadcrumbs="['Laporan siswa' => route('teacher.reports.index'), $student['name'] => null]">
        <x-slot:actions>
            <x-ui.button icon="plus" :href="route('teacher.reports.create', ['student_id' => $student['id']])">Tulis laporan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($reports->isEmpty())
        <x-ui.empty-state size="page" icon="document-chart-bar" title="Belum ada laporan untuk siswa ini." />
    @else
        <ol class="relative space-y-4 border-l border-line pl-6">
            @foreach ($reports as $report)
                <li class="relative">
                    <span class="absolute -left-[1.94rem] top-5 size-3 rounded-full border-2 border-surface bg-brand" aria-hidden="true"></span>
                    <x-ui.card :href="route('teacher.reports.show', $report)" :title="$report->title" padding="sm" level="3"
                        :subtitle="$f::date($report->report_date, 'd F Y').' · '.($report->className?->name ?? '').' · '.($report->teacher?->name ?? '')">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <x-ui.badge :tone="\App\Services\Academic\ProgressReportService::typeTone($report->type)">{{ $report->type_label }}</x-ui.badge>
                            <x-ui.progress class="sm:max-w-xs" :value="(float) $report->progress_percentage" size="sm" show-value label="Progres"
                                :tone="\App\Services\Academic\ProgressReportService::progressTone($report->progress_percentage)" />
                        </div>
                    </x-ui.card>
                </li>
            @endforeach
        </ol>
        @if ($reports->hasPages())
            <div class="mt-6">{{ $reports->links() }}</div>
        @endif
    @endif
</x-app-layout>
