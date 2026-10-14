{{-- Admin reports hub · tab "Progres belajar". Vars: $classes (paginator), $rates, $topMaterials, $unopenedMaterials, $f --}}
<div class="grid gap-gutter lg:grid-cols-3">
    <div class="lg:col-span-2">
        <x-ui.table caption="Progres per kelas" mobile="stack">
            <x-slot:head>
                <x-ui.th>Kelas</x-ui.th>
                <x-ui.th numeric>Siswa aktif</x-ui.th>
                <x-ui.th numeric>Pertemuan</x-ui.th>
                <x-ui.th>Rata-rata penyelesaian</x-ui.th>
            </x-slot:head>

            @foreach ($classes as $class)
                @php($rate = $rates->get($class->id))
                <tr>
                    <td class="cell-primary" data-label="Kelas">
                        <span class="font-medium text-fg">{{ $class->name }}</span>
                        <p class="cell-secondary">{{ $class->curriculum?->title }} · {{ $class->status_label }}</p>
                    </td>
                    <td class="num" data-label="Siswa aktif">{{ $rate['students'] ?? 0 }}</td>
                    <td class="num" data-label="Pertemuan">{{ $rate['meetings'] ?? 0 }}</td>
                    <td data-label="Rata-rata penyelesaian" class="min-w-[10rem]">
                        <x-ui.progress :value="$rate['percent'] ?? 0" show-value size="sm"
                            :tone="\App\Services\Academic\ProgressReportService::progressTone($rate['percent'] ?? 0)"
                            label="Penyelesaian {{ $class->name }}" />
                    </td>
                </tr>
            @endforeach

            <x-slot:empty>
                <x-ui.empty-state icon="chart-bar" title="Belum ada kelas berjalan dengan kurikulum." />
            </x-slot:empty>

            @if ($classes->hasPages())
                <x-slot:footer>{{ $classes->links() }}</x-slot:footer>
            @endif
        </x-ui.table>
    </div>

    <div class="space-y-6">
        <x-ui.card title="Paling sering dibuka" subtitle="90 hari terakhir" padding="none">
            @forelse ($topMaterials as $row)
                <div class="flex items-center justify-between gap-3 border-t border-line px-card py-3">
                    <div class="min-w-0">
                        <p class="truncate font-medium text-fg">{{ $row->material?->title ?? 'Materi diarsipkan' }}</p>
                        <p class="text-sm text-fg-subtle">{{ $row->students }} siswa</p>
                    </div>
                    <span class="num shrink-0 text-fg">{{ $row->views }}×</span>
                </div>
            @empty
                <div class="border-t border-line"><x-ui.empty-state icon="eye" title="Belum ada akses materi." /></div>
            @endforelse
        </x-ui.card>

        <x-ui.card title="Belum pernah dibuka" subtitle="Materi aktif tanpa satu pun akses" padding="none">
            @forelse ($unopenedMaterials as $material)
                <a href="{{ route('admin.materials.show', $material) }}" class="block border-t border-line px-card py-3 hover:bg-surface-2">
                    <p class="truncate font-medium text-fg">{{ $material->title }}</p>
                    <p class="text-sm text-fg-subtle">{{ $material->syllabus?->curriculum?->title }} · {{ $material->meeting_range }}</p>
                </a>
            @empty
                <div class="border-t border-line"><x-ui.empty-state icon="check-circle" title="Semua materi aktif sudah pernah dibuka." /></div>
            @endforelse
        </x-ui.card>
    </div>
</div>
