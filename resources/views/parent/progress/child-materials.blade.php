@php($f = \App\Services\Academic\Format::class)
<x-app-layout :title="'Materi · '.$child->name">
    <x-ui.page-header :title="'Materi '.$class->name" :eyebrow="$child->name"
        subtitle="Materi yang sudah terbuka untuk anak Anda dan kapan terakhir dibuka."
        :breadcrumbs="['Progres anak' => route('parent.progress.index'), $child->name => route('parent.progress.child', $child), 'Materi' => null]" />

    @php($rows = $summary->meetings->flatMap(fn ($m) => $m['syllabus']->activeMaterials->filter(fn ($mat) => in_array($mat->id, $unlockedIds, true))->map(fn ($mat) => ['material' => $mat, 'meeting' => $m['number']])))

    <x-ui.table caption="Materi terbuka" mobile="stack">
        <x-slot:head>
            <x-ui.th>Materi</x-ui.th>
            <x-ui.th numeric>Pertemuan</x-ui.th>
            <x-ui.th>Jenis</x-ui.th>
            @if ($trackingMode === 'detailed')
                <x-ui.th numeric>Dibuka</x-ui.th>
            @endif
            <x-ui.th numeric>Terakhir dibuka</x-ui.th>
        </x-slot:head>

        @foreach ($rows as $row)
            @php($stat = $accessStats->get($row['material']->id))
            <tr>
                <td class="cell-primary" data-label="Materi">
                    <span class="font-medium text-fg">{{ $row['material']->title }}</span>
                    @if (filled($row['material']->description))
                        <p class="cell-secondary line-clamp-1">{{ $row['material']->description }}</p>
                    @endif
                </td>
                <td class="num" data-label="Pertemuan">{{ $row['meeting'] }}</td>
                <td data-label="Jenis">@include('admin.materials.partials.type-badge', ['type' => $row['material']->type])</td>
                @if ($trackingMode === 'detailed')
                    <td class="num" data-label="Dibuka">{{ $stat ? $stat['count'].'×' : '—' }}</td>
                @endif
                <td class="num" data-label="Terakhir dibuka">
                    @if ($stat)
                        {{ $f::dateTime($stat['last'], 'd M Y') }}
                    @else
                        <x-ui.badge>Belum dibuka</x-ui.badge>
                    @endif
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="lock-closed" title="Belum ada materi yang terbuka."
                description="Materi terbuka mengikuti pertemuan yang sudah dijalani anak Anda." />
        </x-slot:empty>
    </x-ui.table>

    <p class="mt-4 flex items-start gap-2 text-sm text-fg-subtle">
        <x-ui.icon name="shield-check" class="mt-0.5 shrink-0" />
        <span>Kami hanya mencatat kapan materi dibuka, bukan isi aktivitas di perangkat anak. Riwayat dihapus otomatis setelah masa simpan berakhir.</span>
    </p>
</x-app-layout>
