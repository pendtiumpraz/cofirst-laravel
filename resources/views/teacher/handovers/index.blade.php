@php
    use App\Models\TeacherHandover;
    use App\Services\Teacher\TeachingSchedule;

    $statusTone = [
        TeacherHandover::STATUS_PENDING => 'warning',
        TeacherHandover::STATUS_APPROVED => 'info',
        TeacherHandover::STATUS_COMPLETED => 'success',
        TeacherHandover::STATUS_REJECTED => 'danger',
        TeacherHandover::STATUS_CANCELLED => 'neutral',
    ];
    $tabs = [
        'masuk' => ['label' => 'Masuk', 'count' => $counts['masuk'], 'href' => route('teacher.handovers.index', ['tab' => 'masuk'])],
        'dikirim' => ['label' => 'Dikirim', 'count' => $counts['dikirim'], 'href' => route('teacher.handovers.index', ['tab' => 'dikirim'])],
        'riwayat' => ['label' => 'Riwayat', 'count' => $counts['riwayat'], 'href' => route('teacher.handovers.index', ['tab' => 'riwayat'])],
    ];
    $empty = [
        'masuk' => ['Tidak ada permintaan masuk.', 'Permintaan dari pengajar lain untuk menggantikan kelasnya akan tampil di sini.'],
        'dikirim' => ['Belum ada permintaan yang Anda kirim.', 'Ajukan serah terima bila Anda berhalangan mengajar atau kelas perlu dipindahkan.'],
        'riwayat' => ['Riwayat masih kosong.', 'Permintaan yang selesai, ditolak atau dibatalkan tersimpan di sini.'],
    ];
@endphp

<x-app-layout title="Serah Terima Kelas">
    <x-ui.page-header title="Serah terima kelas"
        subtitle="Minta pengajar lain menggantikan Anda untuk sementara, atau pindahkan kelas secara permanen.">
        <x-slot:actions>
            <x-ui.button icon="plus" :href="route('teacher.handovers.create')">Ajukan serah terima</x-ui.button>
        </x-slot:actions>
        <x-ui.tabs :tabs="$tabs" :active="$tab" label="Jenis permintaan" />
    </x-ui.page-header>

    @if ($pendingIncoming > 0 && $tab !== 'masuk')
        <x-ui.alert tone="info" class="mb-6" :title="$pendingIncoming.' permintaan menunggu jawaban Anda'">
            <x-slot:actions>
                <x-ui.button size="sm" variant="secondary" :href="route('teacher.handovers.index', ['tab' => 'masuk'])" arrow>Lihat permintaan masuk</x-ui.button>
            </x-slot:actions>
        </x-ui.alert>
    @endif

    <x-ui.table caption="Daftar serah terima" mobile="stack">
        <x-slot:head>
            <x-ui.th>Kelas</x-ui.th>
            <x-ui.th>{{ $tab === 'masuk' ? 'Dari' : ($tab === 'dikirim' ? 'Kepada' : 'Pengajar') }}</x-ui.th>
            <x-ui.th>Jenis & periode</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right"><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($handovers as $handover)
            @php
                $incoming = (int) $handover->to_teacher_id === (int) $teacher->id;
                $other = $incoming ? $handover->fromTeacher : $handover->toTeacher;
                $needsAnswer = $workflow->canRespond($handover, $teacher);
            @endphp
            <tr @if ($needsAnswer) aria-selected="true" @endif>
                <td class="cell-primary" data-label="Kelas">
                    <a href="{{ route('teacher.handovers.show', $handover) }}" class="hover:text-brand-text hover:underline">{{ $handover->className?->name ?? 'Kelas diarsipkan' }}</a>
                    <span class="cell-secondary block">{{ $handover->className?->course?->name }}</span>
                </td>
                <td data-label="{{ $incoming ? 'Dari' : 'Kepada' }}">
                    <div class="flex items-center gap-2">
                        <x-ui.avatar :user="$other" :name="$other?->name ?? 'Pengajar'" size="xs" decorative />
                        <span>
                            @if ($tab === 'riwayat')<span class="text-fg-subtle">{{ $incoming ? 'Dari' : 'Kepada' }}</span>@endif
                            {{ $other?->name ?? 'Pengajar' }}
                        </span>
                    </div>
                </td>
                <td data-label="Jenis & periode">
                    <span class="text-fg">{{ TeacherHandover::types()[$handover->type] ?? $handover->type }}</span>
                    <span class="cell-secondary block">
                        {{ TeachingSchedule::format($handover->effective_from, 'j M Y') }}@if ($handover->effective_until) – {{ TeachingSchedule::format($handover->effective_until, 'j M Y') }}@endif
                    </span>
                </td>
                <td data-label="Status">
                    <x-ui.badge :tone="$statusTone[$handover->status] ?? 'neutral'" dot>
                        {{ $needsAnswer ? 'Menunggu jawaban Anda' : $handover->status_label }}
                    </x-ui.badge>
                    @if ($workflow->isOverdue($handover))
                        <span class="cell-secondary block">Periode sudah berakhir</span>
                    @endif
                </td>
                <td class="cell-actions">
                    <x-ui.button :variant="$needsAnswer ? 'primary' : 'ghost'" size="xs" :href="route('teacher.handovers.show', $handover)">
                        {{ $needsAnswer ? 'Tinjau' : 'Lihat' }}
                    </x-ui.button>
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="arrows-right-left" :title="$empty[$tab][0]" :description="$empty[$tab][1]">
                @if ($tab !== 'masuk')
                    <x-slot:actions><x-ui.button size="sm" variant="secondary" icon="plus" :href="route('teacher.handovers.create')">Ajukan serah terima</x-ui.button></x-slot:actions>
                @endif
            </x-ui.empty-state>
        </x-slot:empty>

        @if ($handovers->hasPages())
            <x-slot:footer>{{ $handovers->links() }}</x-slot:footer>
        @endif
    </x-ui.table>
</x-app-layout>
