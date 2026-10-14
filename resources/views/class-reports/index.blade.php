@php($f = \App\Services\Academic\Format::class)
<x-app-layout title="Berita acara">
    <x-ui.page-header title="Berita acara"
        :subtitle="$isAdmin ? 'Catatan setiap pertemuan dari seluruh pengajar.' : 'Catatan setiap pertemuan untuk tiap siswa. Mengisinya otomatis memperbarui progres siswa dan membuka materinya.'">
        @can('create', \App\Models\ClassReport::class)
            <x-slot:actions>
                <x-ui.button icon="plus" :href="route('class-reports.create')">Buat berita acara</x-ui.button>
            </x-slot:actions>
        @endcan
    </x-ui.page-header>

    @if ($pending->isNotEmpty())
        <x-ui.card class="mb-8" variant="highlight" title="Belum dilaporkan" :subtitle="$pending->count().' pertemuan menurut jadwal mingguan belum memiliki berita acara.'" padding="none">
            <ul class="divide-y divide-line border-t border-line">
                @foreach ($pending->take(6) as $item)
                    <li class="flex flex-col gap-3 px-card py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="font-medium text-fg">{{ $item['class']->name }}</p>
                            <p class="text-sm text-fg-subtle">{{ $f::date($item['date'], 'l, d M Y') }} · {{ $item['start'] }}–{{ $item['end'] }}</p>
                        </div>
                        <x-ui.button size="sm" icon="pencil-square"
                            :href="route('class-reports.create', ['class_id' => $item['class']->id, 'date' => $item['date']->toDateString(), 'schedule_id' => $item['schedule_id']])">Laporkan</x-ui.button>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif

    <x-ui.table caption="Daftar berita acara" mobile="stack">
        <x-slot:toolbar>
            <x-ui.filter-bar placeholder="Cari nama siswa…">
                <x-ui.select name="class_id" :options="$classOptions" placeholder="Semua kelas" :value="request('class_id')" class="sm:w-48" aria-label="Filter kelas" />
                <label class="flex items-center gap-2 text-sm text-fg-muted">
                    <span class="shrink-0">Dari</span>
                    <input type="date" name="from" value="{{ request('from') }}" class="input sm:w-40" aria-label="Dari tanggal">
                </label>
                <label class="flex items-center gap-2 text-sm text-fg-muted">
                    <span class="shrink-0">s.d.</span>
                    <input type="date" name="to" value="{{ request('to') }}" class="input sm:w-40" aria-label="Sampai tanggal">
                </label>
            </x-ui.filter-bar>
        </x-slot:toolbar>

        <x-slot:head>
            <x-ui.th>Tanggal</x-ui.th>
            <x-ui.th>Siswa</x-ui.th>
            <x-ui.th>Pertemuan</x-ui.th>
            @if ($isAdmin)
                <x-ui.th>Pengajar</x-ui.th>
            @endif
            <x-ui.th>Status</x-ui.th>
            <x-ui.th><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($reports as $report)
            <tr>
                <td data-label="Tanggal">
                    <span class="num text-fg">{{ $f::date($report->report_date) }}</span>
                    <p class="cell-secondary">{{ $f::time($report->start_time) }}–{{ $f::time($report->end_time) }}</p>
                </td>
                <td class="cell-primary" data-label="Siswa">
                    <a href="{{ route('class-reports.show', $report) }}" class="font-medium text-fg hover:text-brand-text">{{ $report->student?->name ?? 'Siswa dihapus' }}</a>
                    <p class="cell-secondary">{{ $report->className?->name }}</p>
                </td>
                <td data-label="Pertemuan">
                    Pertemuan {{ $report->meeting_number }}
                    @if ($report->syllabus)
                        <p class="cell-secondary line-clamp-1">{{ $report->syllabus->title }}</p>
                    @endif
                </td>
                @if ($isAdmin)
                    <td data-label="Pengajar">{{ $report->teacher?->name ?? '—' }}</td>
                @endif
                <td data-label="Status">
                    @if ($service->isEditable($report))
                        <x-ui.badge tone="info" icon="pencil">Bisa diubah s.d. {{ $f::date($service->editableUntil($report), 'd M') }}</x-ui.badge>
                    @else
                        <x-ui.badge icon="lock-closed">Terkunci</x-ui.badge>
                    @endif
                </td>
                <td class="cell-actions">
                    <div class="flex justify-end gap-1">
                        <x-ui.button variant="ghost" size="sm" icon="eye" label="Lihat berita acara {{ $report->student?->name }}" :href="route('class-reports.show', $report)" />
                        @can('update', $report)
                            <x-ui.button variant="ghost" size="sm" icon="pencil-square" label="Ubah berita acara {{ $report->student?->name }}" :href="route('class-reports.edit', $report)" />
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="clipboard-document-list" title="Belum ada berita acara."
                :description="$isAdmin ? 'Berita acara muncul setelah pengajar melaporkan pertemuan.' : 'Laporkan pertemuan pertama Anda; progres siswa akan ikut diperbarui.'" />
        </x-slot:empty>

        @if ($reports->hasPages())
            <x-slot:footer>{{ $reports->links() }}</x-slot:footer>
        @endif
    </x-ui.table>
</x-app-layout>
