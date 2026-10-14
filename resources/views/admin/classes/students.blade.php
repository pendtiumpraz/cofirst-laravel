@php
    use App\Models\Enrollment;
    use App\Services\Admin\AdminClock;

    $taken = (int) $class->seats_taken;
    $full = $taken >= $class->max_students;
    $statusTone = ['pending' => 'warning', 'active' => 'success', 'suspended' => 'warning', 'completed' => 'info', 'dropped' => 'neutral', 'cancelled' => 'neutral', 'inactive' => 'neutral'];
    $payments = App\Http\Requests\Admin\EnrollmentRequest::PAYMENT_STATUSES;
    $paymentTone = ['unpaid' => 'danger', 'pending' => 'warning', 'paid' => 'success', 'refunded' => 'info'];
    $filters = ['seat' => 'Terdaftar', 'active' => 'Aktif', 'pending' => 'Menunggu', 'suspended' => 'Ditangguhkan', 'completed' => 'Selesai', 'dropped' => 'Keluar'];
    $quickStatuses = ['active' => ['Aktifkan', 'check-circle'], 'suspended' => ['Tangguhkan', 'pause-circle'], 'completed' => ['Tandai selesai', 'flag']];
@endphp

<x-app-layout :title="'Siswa '.$class->name">
    <x-ui.page-header :title="'Siswa · '.$class->name" subtitle="Tambah, tangguhkan atau keluarkan siswa. Kapasitas dihitung dari siswa aktif dan yang menunggu."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Kelas' => route('admin.classes.index'), $class->name => route('admin.classes.show', $class), 'Siswa' => null]">
        <x-slot:meta>
            <span>{{ $class->course?->name ?? 'Tanpa kursus' }}</span>
            <span>{{ $class->status_label }}</span>
        </x-slot:meta>
    </x-ui.page-header>

    <div class="mt-6 grid grid-cols-1 gap-gutter lg:grid-cols-3">
        <x-ui.card title="Kapasitas" class="lg:order-2">
            <p class="text-kpi font-num text-fg">{{ $taken }}<span class="text-base text-fg-subtle">/{{ $class->max_students }}</span></p>
            <x-ui.progress class="mt-3" :value="$taken" :max="max(1, $class->max_students)" :tone="$full ? 'danger' : ($taken / max(1, $class->max_students) >= 0.8 ? 'warning' : 'brand')" label="Kursi terisi" />
            <p class="mt-2 text-sm text-fg-muted">{{ $full ? 'Kelas penuh. Naikkan kapasitas atau keluarkan siswa untuk menambah.' : ($class->max_students - $taken).' kursi tersisa.' }}</p>

            @can('manageRoster', $class)
                @if (! in_array($class->status, ['completed', 'cancelled'], true))
                    <form method="POST" action="{{ route('admin.classes.add-student', $class) }}" class="mt-6 space-y-4 border-t border-line pt-6"
                        x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
                        @csrf
                        <h2 class="card-title">Tambah siswa</h2>
                        <x-ui.field label="Siswa" for="student-picker" :error="'student_id'" required>
                            @include('admin.users.partials.user-picker', ['name' => 'student_id', 'id' => 'student-picker', 'role' => 'student',
                                'multiple' => false, 'selected' => [], 'excludeClass' => $class->id, 'placeholder' => 'Cari nama siswa…'])
                        </x-ui.field>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                            <x-ui.field label="Status" for="status">
                                <x-ui.select name="status" :options="['active' => 'Aktif', 'pending' => 'Menunggu pembayaran']" value="active" />
                            </x-ui.field>
                            <x-ui.field label="Pembayaran" for="payment_status">
                                <x-ui.select name="payment_status" :options="$payments" value="unpaid" />
                            </x-ui.field>
                        </div>
                        <x-ui.button type="submit" icon="user-plus" block :disabled="$full" loading-when="isSubmitting">Tambahkan ke kelas</x-ui.button>
                        <p class="hint">Siswa otomatis mendapat semua jadwal mingguan kelas ini.</p>
                    </form>
                @endif
            @endcan
        </x-ui.card>

        <div class="lg:order-1 lg:col-span-2">
            <x-ui.tabs label="Saring status" :active="$filter" :tabs="collect($filters)->mapWithKeys(fn ($label, $key) => [$key => ['label' => $label, 'href' => route('admin.classes.students', [$class, 'status' => $key])]])->all()" />

            <x-ui.table caption="Daftar siswa kelas" mobile="stack" class="mt-4">
                <x-slot:head>
                    <x-ui.th>Siswa</x-ui.th>
                    <x-ui.th>Status</x-ui.th>
                    <x-ui.th>Pembayaran</x-ui.th>
                    <x-ui.th numeric>Kehadiran</x-ui.th>
                    <x-ui.th align="right"><span class="sr-only">Aksi</span></x-ui.th>
                </x-slot:head>

                @foreach ($enrollments as $enrollment)
                    @php $student = $enrollment->student; @endphp
                    <tr>
                        <td class="cell-primary" data-label="Siswa">
                            <div class="flex items-center gap-3">
                                <x-ui.avatar :user="$student" size="sm" decorative />
                                <div class="min-w-0">
                                    <a href="{{ route('admin.users.show', $enrollment->student_id) }}" class="block truncate font-medium text-fg hover:text-brand-text">{{ $student?->name ?? 'Siswa' }}</a>
                                    <p class="cell-secondary">
                                        Sejak {{ AdminClock::format($enrollment->enrollment_date, 'j M Y') }}
                                        @if ($student?->profile?->isChild()) · <span class="text-info">Anak</span> @endif
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td data-label="Status"><x-ui.badge :tone="$statusTone[$enrollment->status] ?? 'neutral'" dot>{{ $enrollment->status_label }}</x-ui.badge></td>
                        <td data-label="Pembayaran"><x-ui.badge :tone="$paymentTone[$enrollment->payment_status] ?? 'neutral'">{{ $payments[$enrollment->payment_status] ?? $enrollment->payment_status }}</x-ui.badge></td>
                        <td class="num" data-label="Kehadiran">
                            {{ isset($rates[$enrollment->student_id]) ? number_format($rates[$enrollment->student_id], 1, ',', '.').'%' : '—' }}
                        </td>
                        <td class="cell-actions">
                            @can('manageRoster', $class)
                                <div class="inline-flex items-center gap-1">
                                    <x-ui.dropdown align="right" width="56">
                                        <x-slot:trigger>
                                            <button type="button" class="btn btn-ghost btn-icon btn-sm" aria-label="Ubah status {{ $student?->name }}" aria-haspopup="true" x-bind:aria-expanded="open.toString()">
                                                <x-ui.icon name="ellipsis-horizontal" />
                                            </button>
                                        </x-slot:trigger>
                                        <p class="dropdown-label">Ubah status</p>
                                        @foreach ($quickStatuses as $status => [$label, $icon])
                                            @continue($enrollment->status === $status)
                                            <x-ui.dropdown-item :icon="$icon" method="patch" :href="route('admin.classes.student-status', [$class, $enrollment]).'?status='.$status">{{ $label }}</x-ui.dropdown-item>
                                        @endforeach
                                        <div class="dropdown-divider"></div>
                                        <x-ui.dropdown-item icon="document-text" :href="route('admin.enrollments.show', $enrollment)">Detail pendaftaran</x-ui.dropdown-item>
                                    </x-ui.dropdown>
                                    @if (in_array($enrollment->status, Enrollment::SEAT_STATUSES, true))
                                        <x-ui.confirm-delete :action="route('admin.classes.remove-student', [$class, $enrollment->student_id])" icon-only size="sm" icon="user-minus"
                                            label="Keluarkan" title="Keluarkan siswa dari kelas?" confirm-text="Ya, keluarkan"
                                            :message="($student?->name ?? 'Siswa ini').' akan berstatus Keluar dan jadwalnya dinonaktifkan. Riwayat absensi, laporan dan pembayaran tetap tersimpan.'" />
                                    @endif
                                </div>
                            @endcan
                        </td>
                    </tr>
                @endforeach

                <x-slot:empty>
                    <x-ui.empty-state icon="users" :title="$filter === 'seat' ? 'Belum ada siswa di kelas ini.' : 'Tidak ada siswa dengan status ini.'"
                        :description="$filter === 'seat' ? 'Gunakan formulir Tambah siswa untuk mendaftarkan siswa.' : null" />
                </x-slot:empty>

                <x-slot:footer>{{ $enrollments->links() }}</x-slot:footer>
            </x-ui.table>
        </div>
    </div>
</x-app-layout>
