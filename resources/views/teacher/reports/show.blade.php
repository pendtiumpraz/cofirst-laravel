<x-app-layout :title="$report->title">
    <x-ui.page-header :title="$report->title" eyebrow="Laporan perkembangan" :subtitle="$report->student?->name"
        :breadcrumbs="['Laporan siswa' => route('teacher.reports.index'), $report->title => null]">
        @if ($canManage)
            <x-slot:actions>
                <x-ui.confirm-delete :action="route('teacher.reports.destroy', $report)" title="Hapus laporan ini?"
                    message="Laporan akan hilang dari portal siswa dan orang tua. Tindakan ini tidak dapat dibatalkan." />
                <x-ui.button icon="pencil-square" :href="route('teacher.reports.edit', $report)">Ubah</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @include('teacher.reports.partials.report-body')

    @if ($report->student && $report->className)
        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button variant="secondary" icon="user" :href="route('teacher.reports.by-student', $report->student)">Semua laporan {{ \Illuminate\Support\Str::of($report->student->name)->before(' ') }}</x-ui.button>
            <x-ui.button variant="secondary" icon="user-group" :href="route('teacher.reports.by-class', $report->className)">Laporan kelas {{ $report->className->name }}</x-ui.button>
        </div>
    @endif
</x-app-layout>
