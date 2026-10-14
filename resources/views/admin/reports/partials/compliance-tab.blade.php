{{-- Admin reports hub · tab "Kepatuhan berita acara". Vars: $classes, $reported, $pending, $editDays, $f --}}
<div class="grid gap-gutter lg:grid-cols-3">
    <div class="lg:col-span-2">
        <x-ui.table caption="Berita acara per kelas" mobile="stack">
            <x-slot:head>
                <x-ui.th>Kelas</x-ui.th>
                <x-ui.th>Pengajar</x-ui.th>
                <x-ui.th numeric>Siswa</x-ui.th>
                <x-ui.th numeric>Pertemuan dilaporkan</x-ui.th>
                <x-ui.th numeric>Terakhir</x-ui.th>
            </x-slot:head>

            @foreach ($classes as $class)
                @php($row = $reported->get($class->id))
                <tr>
                    <td class="cell-primary" data-label="Kelas">
                        <span class="font-medium text-fg">{{ $class->name }}</span>
                        <p class="cell-secondary">{{ $class->course?->name }}</p>
                    </td>
                    <td data-label="Pengajar">{{ $class->teachers->pluck('name')->join(', ') ?: '—' }}</td>
                    <td class="num" data-label="Siswa">{{ $class->active_enrollments_count }}</td>
                    <td class="num" data-label="Pertemuan dilaporkan">{{ (int) $row?->meetings }}</td>
                    <td class="num" data-label="Terakhir">{{ $row ? $f::date($row->last_date) : 'Belum ada' }}</td>
                </tr>
            @endforeach

            <x-slot:empty>
                <x-ui.empty-state icon="clipboard-document-list" title="Tidak ada kelas yang sedang berjalan." />
            </x-slot:empty>
        </x-ui.table>
    </div>

    <x-ui.card title="Belum dilaporkan" subtitle="Pertemuan 14 hari terakhir menurut jadwal mingguan yang belum memiliki berita acara." padding="none">
        @forelse ($pending as $item)
            <div class="border-t border-line px-card py-3">
                <p class="font-medium text-fg">{{ $item['class']->name }}</p>
                <p class="text-sm text-fg-subtle">
                    {{ $f::date($item['date'], 'l, d M') }} · {{ $item['start'] }}–{{ $item['end'] }}
                    · {{ $item['class']->teachers->pluck('name')->join(', ') ?: 'tanpa pengajar' }}
                </p>
                @if ($item['date']->copy()->addDays($editDays)->endOfDay()->isPast())
                    <x-ui.badge tone="warning" class="mt-2">Lewat batas {{ $editDays }} hari</x-ui.badge>
                @endif
            </div>
        @empty
            <div class="border-t border-line">
                <x-ui.empty-state icon="check-circle" title="Semua pertemuan sudah dilaporkan." />
            </div>
        @endforelse
    </x-ui.card>
</div>
