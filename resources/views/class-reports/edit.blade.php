@php($f = \App\Services\Academic\Format::class)
<x-app-layout title="Ubah berita acara">
    <x-ui.page-header title="Ubah berita acara"
        :subtitle="($report->student?->name ?? 'Siswa').' · '.($report->className?->name ?? '')"
        :breadcrumbs="['Berita acara' => route('class-reports.index'), 'Pertemuan '.$report->meeting_number => route('class-reports.show', $report), 'Ubah' => null]">
        <x-slot:badge>
            <x-ui.badge tone="info" icon="clock">Bisa diubah s.d. {{ $f::dateTime($editableUntil, 'd M, H.i') }}</x-ui.badge>
        </x-slot:badge>
    </x-ui.page-header>

    @if ((int) $report->teacher_id !== (int) auth()->id())
        <x-ui.alert tone="warning" title="Koreksi oleh admin" class="mb-6">
            Laporan ini ditulis {{ $report->teacher?->name ?? 'pengajar lain' }}. Setiap koreksi dicatat di log audit.
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('class-reports.update', $report) }}" x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true" novalidate>
        @csrf
        @method('PUT')

        <x-ui.card>
            @include('class-reports.partials.session-fields', [
                'syllabuses' => $syllabuses,
                'values' => [
                    'report_date' => $report->report_date?->toDateString(),
                    'start_time' => $report->start_time?->format('H:i'),
                    'end_time' => $report->end_time?->format('H:i'),
                    'meeting_number' => $report->meeting_number,
                    'syllabus_id' => $report->syllabus_id,
                    'material_id' => $report->material_id,
                    'learning_concept' => $report->learning_concept,
                    'learning_media_link' => $report->learning_media_link,
                ],
            ])

            <div class="form-section">
                <div class="form-section-intro">
                    <h2 class="card-title">Catatan siswa</h2>
                    <p class="mt-1 text-sm text-fg-muted">Siswa, kelas dan pengajar tidak dapat diubah. Bila salah siswa, hapus berita acara ini dan buat yang baru.</p>
                </div>
                <div class="form-section-body">
                    @include('class-reports.partials.student-fields', [
                        'prefix' => '',
                        'values' => $report->only(\App\Services\Academic\ClassReportService::STUDENT_FIELDS),
                        'open' => true,
                    ])
                </div>
            </div>
        </x-ui.card>

        <div class="form-actions mt-6">
            <x-ui.button variant="secondary" :href="route('class-reports.show', $report)">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan perubahan</x-ui.button>
        </div>
    </form>
</x-app-layout>
