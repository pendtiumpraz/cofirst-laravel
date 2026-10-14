@php($f = \App\Services\Academic\Format::class)
<x-app-layout title="Berita acara">
    <x-ui.page-header :title="$report->student?->name ?? 'Siswa dihapus'"
        :eyebrow="'Berita acara · Pertemuan '.$report->meeting_number"
        :subtitle="$f::date($report->report_date, 'l, d F Y').' · '.$f::time($report->start_time).'–'.$f::time($report->end_time)"
        :breadcrumbs="['Berita acara' => route('class-reports.index'), 'Pertemuan '.$report->meeting_number => null]">
        <x-slot:badge>
            @if ($isEditable)
                <x-ui.badge tone="info" icon="pencil">Bisa diubah s.d. {{ $f::dateTime($editableUntil, 'd M, H.i') }}</x-ui.badge>
            @else
                <x-ui.badge icon="lock-closed">Terkunci</x-ui.badge>
            @endif
        </x-slot:badge>
        <x-slot:actions>
            @can('delete', $report)
                <x-ui.confirm-delete :action="route('class-reports.destroy', $report)" title="Hapus berita acara ini?"
                    message="Progres pertemuan siswa ini akan dikembalikan bila tidak ada berita acara lain untuk pertemuan yang sama." />
            @endcan
            @can('update', $report)
                <x-ui.button icon="pencil-square" :href="route('class-reports.edit', $report)">Ubah</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-gutter lg:grid-cols-3">
        <div class="lg:col-span-2">
            @include('class-reports.partials.report-body', ['showInternal' => true, 'showMedia' => true])
        </div>

        <div class="space-y-6">
            <x-ui.card title="Rincian" variant="inset">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-fg-muted">Kelas</dt><dd class="text-right text-fg">{{ $report->className?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-fg-muted">Kursus</dt><dd class="text-right text-fg">{{ $report->className?->course?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-fg-muted">Kurikulum</dt><dd class="text-right text-fg">{{ $report->curriculum?->title ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-fg-muted">Pengajar</dt><dd class="text-right text-fg">{{ $report->teacher?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-fg-muted">Dibuat</dt><dd class="text-right text-fg">{{ $f::dateTime($report->created_at) }}</dd></div>
                </dl>
            </x-ui.card>

            @if ($siblings->isNotEmpty())
                <x-ui.card title="Siswa lain di pertemuan ini" padding="none">
                    @foreach ($siblings as $sibling)
                        <a href="{{ route('class-reports.show', $sibling->id) }}" class="flex min-h-[44px] items-center justify-between gap-3 border-t border-line px-card py-3 hover:bg-surface-2">
                            <span class="truncate text-fg">{{ $sibling->student?->name ?? 'Siswa dihapus' }}</span>
                            <x-ui.icon name="chevron-right" class="shrink-0 text-fg-subtle" />
                        </a>
                    @endforeach
                </x-ui.card>
            @endif
        </div>
    </div>
</x-app-layout>
