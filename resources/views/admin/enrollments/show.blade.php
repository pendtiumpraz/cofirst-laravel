@php
    use App\Models\Schedule;
    use App\Services\Admin\AdminClock;

    $statusTone = ['pending' => 'warning', 'active' => 'success', 'suspended' => 'warning', 'completed' => 'info', 'dropped' => 'neutral', 'cancelled' => 'neutral', 'inactive' => 'neutral'];
    $paymentTone = ['unpaid' => 'danger', 'pending' => 'warning', 'paid' => 'success', 'refunded' => 'info'];
    $txTone = ['paid' => 'success', 'pending' => 'warning', 'overdue' => 'danger', 'failed' => 'danger', 'refunded' => 'info', 'cancelled' => 'neutral'];
    $class = $enrollment->className;
    $slots = App\Services\Scheduling\ScheduleSlots::group($enrollment->schedules);
@endphp

<x-app-layout title="Detail pendaftaran">
    <x-ui.page-header :title="$enrollment->student?->name ?? 'Siswa'" :subtitle="($class?->name ?? 'Belum ada kelas').' · '.($class?->course?->name ?? $enrollment->course?->name ?? 'Tanpa kursus')"
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Pendaftaran' => route('admin.enrollments.index'), 'Detail' => null]">
        <x-slot:badge>
            <x-ui.badge :tone="$statusTone[$enrollment->status] ?? 'neutral'" dot>{{ $enrollment->status_label }}</x-ui.badge>
        </x-slot:badge>
        <x-slot:meta>
            <span>#{{ $enrollment->id }}</span>
            <span>Terdaftar {{ AdminClock::format($enrollment->enrollment_date, 'j F Y') }}</span>
        </x-slot:meta>
        <x-slot:actions>
            @can('update', $enrollment)
                @if (in_array($enrollment->status, ['pending', 'active', 'suspended'], true))
                    <form method="POST" action="{{ route('admin.enrollments.toggle-status', $enrollment) }}">
                        @csrf
                        <x-ui.button type="submit" variant="secondary" :icon="$enrollment->status === 'active' ? 'pause-circle' : 'check-circle'">
                            {{ $enrollment->status === 'pending' ? 'Setujui' : ($enrollment->status === 'active' ? 'Tangguhkan' : 'Aktifkan kembali') }}
                        </x-ui.button>
                    </form>
                @endif
                <x-ui.button icon="pencil-square" :href="route('admin.enrollments.edit', $enrollment)">Ubah</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mt-8 grid grid-cols-1 gap-gutter lg:grid-cols-3">
        <div class="flex flex-col gap-gutter lg:col-span-2">
            <x-ui.card title="Jadwal mingguan siswa" padding="none">
                @if ($slots->isEmpty())
                    <x-ui.empty-state icon="calendar-days" title="Belum ada jadwal untuk siswa ini."
                        description="Jadwal muncul setelah kelas memiliki jadwal mingguan dan pendaftaran aktif." />
                @else
                    <ul class="divide-y divide-line" role="list">
                        @foreach ($slots as $slot)
                            <li class="flex flex-wrap items-center gap-x-4 gap-y-1 px-card py-3">
                                <span class="w-20 font-medium text-fg">{{ $slot->dayName() }}</span>
                                <span class="num font-mono text-sm">{{ $slot->timeRange() }}</span>
                                <span class="min-w-0 flex-1 text-sm text-fg-muted">{{ $slot->room() ?? 'Lokasi belum ditentukan' }}</span>
                                @unless ($slot->isActive())<x-ui.badge tone="warning">Nonaktif</x-ui.badge>@endunless
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            <x-ui.table caption="Tagihan pendaftaran ini" mobile="stack">
                <x-slot:toolbar><h2 class="card-title">Tagihan & pembayaran</h2></x-slot:toolbar>
                <x-slot:head>
                    <x-ui.th>Tagihan</x-ui.th>
                    <x-ui.th>Status</x-ui.th>
                    <x-ui.th numeric>Jumlah</x-ui.th>
                    <x-ui.th numeric>Jatuh tempo</x-ui.th>
                </x-slot:head>
                @foreach ($enrollment->transactions as $tx)
                    <tr>
                        <td class="cell-primary" data-label="Tagihan"><span class="font-mono text-xs">{{ $tx->invoice_number ?? '#'.$tx->id }}</span></td>
                        <td data-label="Status"><x-ui.badge :tone="$txTone[$tx->display_status] ?? 'neutral'" dot>{{ $tx->status_label }}</x-ui.badge></td>
                        <td class="num" data-label="Jumlah">{{ AdminClock::rupiah($tx->amount) }}</td>
                        <td class="num" data-label="Jatuh tempo">{{ AdminClock::format($tx->due_date) }}</td>
                    </tr>
                @endforeach
                <x-slot:empty>
                    <x-ui.empty-state icon="banknotes" title="Belum ada tagihan untuk pendaftaran ini." description="Tagihan dibuat oleh tim keuangan." />
                </x-slot:empty>
            </x-ui.table>
        </div>

        <div class="flex flex-col gap-gutter">
            <x-ui.card title="Ringkasan">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-fg-muted">Siswa</dt>
                        <dd class="mt-1"><a href="{{ route('admin.users.show', $enrollment->student_id) }}" class="link">{{ $enrollment->student?->name ?? '—' }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-fg-muted">Kelas</dt>
                        <dd class="mt-1">
                            @if ($class)
                                <a href="{{ route('admin.classes.show', $class) }}" class="link">{{ $class->name }}</a>
                            @else
                                <span class="text-fg-subtle">Belum ada kelas</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-fg-muted">Pengajar</dt>
                        <dd class="mt-1 text-fg">{{ $class?->teachers->pluck('name')->implode(', ') ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-fg-muted">Pembayaran</dt>
                        <dd class="mt-1"><x-ui.badge :tone="$paymentTone[$enrollment->payment_status] ?? 'neutral'">{{ $paymentStatuses[$enrollment->payment_status] ?? $enrollment->payment_status }}</x-ui.badge></dd>
                    </div>
                    <div>
                        <dt class="text-fg-muted">Orang tua/wali</dt>
                        <dd class="mt-1 text-fg">{{ $enrollment->student?->parents->pluck('name')->implode(', ') ?: 'Belum tertaut' }}</dd>
                    </div>
                </dl>
                @if (filled($enrollment->notes))
                    <div class="mt-5 border-t border-line pt-4">
                        <p class="eyebrow mb-1">Catatan</p>
                        <p class="whitespace-pre-line text-sm text-fg-muted">{{ $enrollment->notes }}</p>
                    </div>
                @endif
            </x-ui.card>

            @can('delete', $enrollment)
                <x-ui.card variant="callout" title="Hapus pendaftaran" style="--callout-c: var(--cf-danger)"
                    subtitle="Hanya untuk data salah input tanpa riwayat pembayaran atau absensi. Selain itu, ubah status menjadi Keluar atau Dibatalkan.">
                    <x-ui.confirm-delete :action="route('admin.enrollments.destroy', $enrollment)" variant="danger" label="Hapus pendaftaran"
                        title="Hapus pendaftaran ini?" message="Jadwal mingguan siswa di kelas ini ikut dihapus. Tindakan ini tidak dapat dibatalkan." />
                </x-ui.card>
            @endcan
        </div>
    </div>
</x-app-layout>
