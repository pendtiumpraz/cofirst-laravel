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
    $class = $handover->className;
    $temporary = $handover->type === TeacherHandover::TYPE_TEMPORARY;
    $typeLabel = TeacherHandover::types()[$handover->type] ?? $handover->type;
    $period = TeachingSchedule::format($handover->effective_from, 'j M Y')
        .($handover->effective_until ? ' – '.TeachingSchedule::format($handover->effective_until, 'j M Y') : '');
    $fromName = $handover->fromTeacher?->name ?? 'Pengajar';
    $toName = $handover->toTeacher?->name ?? 'Pengajar';
    $responded = in_array($handover->status, [TeacherHandover::STATUS_APPROVED, TeacherHandover::STATUS_COMPLETED, TeacherHandover::STATUS_REJECTED], true);
@endphp

<x-app-layout :title="'Serah Terima '.($class?->name ?? '')">
    <x-ui.page-header :title="$class?->name ?? 'Kelas diarsipkan'" :eyebrow="'Serah terima '.mb_strtolower($typeLabel)"
        :subtitle="$fromName.' → '.$toName.' · '.$period"
        :breadcrumbs="['Serah terima' => route('teacher.handovers.index'), ($class?->name ?? 'Permintaan') => null]">
        <x-slot:badge>
            <x-ui.badge :tone="$statusTone[$handover->status] ?? 'neutral'" dot>{{ $handover->status_label }}</x-ui.badge>
        </x-slot:badge>
        @if ($canRespond || $canEdit || $canComplete)
            <x-slot:actions>
                @if ($canRespond)
                    <form method="POST" action="{{ route('teacher.handovers.reject', $handover) }}"
                        data-confirm="Permintaan ini akan ditolak dan {{ $fromName }} akan melihat statusnya." data-confirm-title="Tolak permintaan?"
                        data-confirm-ok="Ya, tolak" data-confirm-tone="danger">
                        @csrf
                        <x-ui.button type="submit" variant="danger-ghost" icon="x-mark">Tolak</x-ui.button>
                    </form>
                    <form method="POST" action="{{ route('teacher.handovers.approve', $handover) }}"
                        data-confirm="{{ $temporary ? 'Anda akan ditambahkan sebagai pengajar kelas ini selama periode pengganti.' : 'Kelas ini akan dipindahkan sepenuhnya kepada Anda.' }}"
                        data-confirm-title="Terima serah terima?" data-confirm-ok="Ya, terima" data-confirm-tone="primary">
                        @csrf
                        <x-ui.button type="submit" icon="check">Terima</x-ui.button>
                    </form>
                @endif
                @if ($canEdit)
                    <x-ui.confirm-delete :action="route('teacher.handovers.destroy', $handover)" label="Batalkan" icon="x-mark"
                        title="Batalkan permintaan?" message="Permintaan tetap tersimpan di riwayat dengan status dibatalkan."
                        confirm-text="Ya, batalkan" cancel-text="Kembali" />
                    <x-ui.button variant="secondary" icon="pencil-square" :href="route('teacher.handovers.edit', $handover)">Ubah</x-ui.button>
                @endif
                @if ($canComplete)
                    <form method="POST" action="{{ route('teacher.handovers.complete', $handover) }}"
                        data-confirm="{{ $toName }} akan dilepas dari kelas ini." data-confirm-title="Tandai serah terima selesai?"
                        data-confirm-ok="Ya, selesai" data-confirm-tone="primary">
                        @csrf
                        <x-ui.button type="submit" :variant="$overdue ? 'primary' : 'secondary'" icon="check-circle">Tandai selesai</x-ui.button>
                    </form>
                @endif
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <div class="space-y-4">
        @if ($canRespond)
            <x-ui.alert tone="info" title="{{ $fromName }} menunggu jawaban Anda">
                @if ($temporary)
                    Bila diterima, Anda menjadi pengajar kelas ini mulai {{ TeachingSchedule::format($handover->effective_from, 'j M Y') }}
                    sampai {{ TeachingSchedule::format($handover->effective_until, 'j M Y') }}: jadwal, absensi dan berita acara kelas akan tampil di portal Anda.
                @else
                    Bila diterima, kelas ini dipindahkan sepenuhnya kepada Anda dan {{ $fromName }} tidak lagi mengajarnya.
                @endif
            </x-ui.alert>
        @endif
        @if ($overdue)
            <x-ui.alert tone="warning" title="Periode pengganti sudah berakhir">
                Tandai serah terima selesai agar {{ $toName }} dilepas dari kelas. Sistem juga menyelesaikannya otomatis setiap jam.
            </x-ui.alert>
        @endif
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Rincian">
                <dl class="grid gap-x-8 gap-y-5 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-fg-subtle">Kelas</dt>
                        <dd class="mt-0.5 text-fg">
                            @if ($class && $teacher->teachesClass($class))
                                <a href="{{ route('teacher.class-detail', $class) }}" class="link">{{ $class->name }}</a>
                            @else
                                {{ $class?->name ?? 'Kelas diarsipkan' }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-fg-subtle">Kursus</dt>
                        <dd class="mt-0.5 text-fg">{{ $class?->course?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-fg-subtle">Dari</dt>
                        <dd class="mt-1 flex items-center gap-2 text-fg"><x-ui.avatar :user="$handover->fromTeacher" :name="$fromName" size="xs" decorative />{{ $fromName }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-fg-subtle">Kepada</dt>
                        <dd class="mt-1 flex items-center gap-2 text-fg"><x-ui.avatar :user="$handover->toTeacher" :name="$toName" size="xs" decorative />{{ $toName }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-fg-subtle">Jenis</dt>
                        <dd class="mt-0.5 text-fg">{{ $typeLabel }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-fg-subtle">Periode</dt>
                        <dd class="mt-0.5 text-fg">{{ $period }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-fg-subtle">Pertemuan terakhir</dt>
                        <dd class="mt-0.5 text-fg">{{ $handover->last_meeting_number ? 'Pertemuan '.$handover->last_meeting_number : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-fg-subtle">Siswa aktif</dt>
                        <dd class="mt-0.5 text-fg">{{ $class ? $class->active_students_count.' siswa' : '—' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card title="Alasan & catatan">
                <div class="space-y-5 text-sm">
                    <div>
                        <p class="text-xs text-fg-subtle">Alasan</p>
                        <p class="mt-1 whitespace-pre-line text-fg">{{ $handover->reason }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-fg-subtle">Catatan serah terima</p>
                        <p class="mt-1 whitespace-pre-line text-fg-muted">{{ $handover->handover_notes ?: 'Tidak ada catatan tambahan.' }}</p>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <x-ui.card title="Riwayat" as="aside" class="lg:self-start">
            <ol class="relative space-y-5 border-l border-line pl-5" role="list">
                <li class="relative">
                    <span class="absolute -left-[1.6rem] top-1 size-2.5 rounded-full bg-brand ring-4 ring-surface" aria-hidden="true"></span>
                    <p class="text-sm font-medium text-fg">Diajukan oleh {{ $fromName }}</p>
                    <p class="text-xs text-fg-subtle">{{ TeachingSchedule::formatDateTime($handover->created_at) }}</p>
                </li>
                @if ($responded)
                    <li class="relative">
                        <span @class(['absolute -left-[1.6rem] top-1 size-2.5 rounded-full ring-4 ring-surface', 'bg-danger-solid' => $handover->status === TeacherHandover::STATUS_REJECTED, 'bg-success-solid' => $handover->status !== TeacherHandover::STATUS_REJECTED]) aria-hidden="true"></span>
                        <p class="text-sm font-medium text-fg">
                            {{ $handover->status === TeacherHandover::STATUS_REJECTED ? 'Ditolak' : 'Diterima' }} oleh {{ $handover->approver?->name ?? $toName }}
                        </p>
                        <p class="text-xs text-fg-subtle">{{ TeachingSchedule::formatDateTime($handover->approved_at) }}</p>
                    </li>
                @endif
                @if ($handover->status === TeacherHandover::STATUS_COMPLETED)
                    <li class="relative">
                        <span class="absolute -left-[1.6rem] top-1 size-2.5 rounded-full bg-success-solid ring-4 ring-surface" aria-hidden="true"></span>
                        <p class="text-sm font-medium text-fg">Selesai</p>
                        <p class="text-xs text-fg-subtle">{{ TeachingSchedule::formatDateTime($handover->updated_at) }}</p>
                    </li>
                @elseif ($handover->status === TeacherHandover::STATUS_CANCELLED)
                    <li class="relative">
                        <span class="absolute -left-[1.6rem] top-1 size-2.5 rounded-full bg-fg-subtle ring-4 ring-surface" aria-hidden="true"></span>
                        <p class="text-sm font-medium text-fg">Dibatalkan</p>
                        <p class="text-xs text-fg-subtle">{{ TeachingSchedule::formatDateTime($handover->updated_at) }}</p>
                    </li>
                @elseif ($handover->status === TeacherHandover::STATUS_PENDING)
                    <li class="relative">
                        <span class="absolute -left-[1.6rem] top-1 size-2.5 rounded-full border-2 border-warning bg-surface ring-4 ring-surface" aria-hidden="true"></span>
                        <p class="text-sm font-medium text-fg">Menunggu jawaban {{ $toName }}</p>
                    </li>
                @endif
            </ol>
        </x-ui.card>
    </div>
</x-app-layout>
