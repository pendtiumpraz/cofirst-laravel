@php
    use App\Models\Enrollment;
    use App\Services\Admin\AdminClock;

    $statusTone = ['pending' => 'warning', 'active' => 'success', 'suspended' => 'warning', 'completed' => 'info', 'dropped' => 'neutral', 'cancelled' => 'neutral', 'inactive' => 'neutral'];
@endphp

<div class="space-y-gutter">
    @if ($user->hasRole('student'))
        <x-ui.table caption="Kelas yang diikuti" mobile="stack">
            <x-slot:toolbar>
                <div class="flex w-full flex-wrap items-center justify-between gap-3">
                    <h3 class="card-title">Kelas yang diikuti</h3>
                    @can('create', App\Models\Enrollment::class)
                        <x-ui.button size="sm" variant="secondary" icon="plus" :href="route('admin.enrollments.create', ['student' => $user->id])">Daftarkan ke kelas</x-ui.button>
                    @endcan
                </div>
            </x-slot:toolbar>
            <x-slot:head>
                <x-ui.th>Kelas</x-ui.th>
                <x-ui.th>Status</x-ui.th>
                <x-ui.th>Pembayaran</x-ui.th>
                <x-ui.th numeric>Tanggal daftar</x-ui.th>
            </x-slot:head>
            @foreach ($enrollments as $enrollment)
                <tr>
                    <td class="cell-primary" data-label="Kelas">
                        <a href="{{ route('admin.enrollments.show', $enrollment) }}" class="font-medium text-fg hover:text-brand-text">{{ $enrollment->className?->name ?? 'Belum ada kelas' }}</a>
                        <p class="cell-secondary">{{ $enrollment->className?->course?->name ?? '—' }}</p>
                    </td>
                    <td data-label="Status"><x-ui.badge :tone="$statusTone[$enrollment->status] ?? 'neutral'" dot>{{ $enrollment->status_label }}</x-ui.badge></td>
                    <td data-label="Pembayaran">{{ App\Http\Requests\Admin\EnrollmentRequest::PAYMENT_STATUSES[$enrollment->payment_status] ?? $enrollment->payment_status }}</td>
                    <td class="num" data-label="Tanggal daftar">{{ AdminClock::format($enrollment->enrollment_date) }}</td>
                </tr>
            @endforeach
            <x-slot:empty>
                <x-ui.empty-state icon="academic-cap" title="Belum terdaftar di kelas mana pun." />
            </x-slot:empty>
        </x-ui.table>
    @endif

    @if ($user->hasRole('teacher'))
        <x-ui.table caption="Kelas yang diajar" mobile="stack">
            <x-slot:toolbar><h3 class="card-title">Kelas yang diajar</h3></x-slot:toolbar>
            <x-slot:head>
                <x-ui.th>Kelas</x-ui.th>
                <x-ui.th>Status</x-ui.th>
                <x-ui.th numeric>Siswa</x-ui.th>
                <x-ui.th numeric>Periode</x-ui.th>
            </x-slot:head>
            @foreach ($taughtClasses as $class)
                <tr>
                    <td class="cell-primary" data-label="Kelas">
                        <a href="{{ route('admin.classes.show', $class) }}" class="font-medium text-fg hover:text-brand-text">{{ $class->name }}</a>
                        <p class="cell-secondary">{{ $class->course?->name ?? '—' }}</p>
                    </td>
                    <td data-label="Status"><x-ui.badge :tone="['active' => 'success', 'planned' => 'info', 'completed' => 'neutral', 'cancelled' => 'danger'][$class->status] ?? 'neutral'" dot>{{ $class->status_label }}</x-ui.badge></td>
                    <td class="num" data-label="Siswa">{{ (int) $class->seats_taken }}/{{ $class->max_students }}</td>
                    <td class="num" data-label="Periode">{{ AdminClock::format($class->start_date, 'j M') }} – {{ AdminClock::format($class->end_date, 'j M Y') }}</td>
                </tr>
            @endforeach
            <x-slot:empty>
                <x-ui.empty-state icon="rectangle-group" title="Belum mengajar kelas mana pun." />
            </x-slot:empty>
        </x-ui.table>
    @endif
</div>
