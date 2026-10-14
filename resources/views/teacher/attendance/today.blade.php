@php
    use App\Services\Teacher\AttendanceRecorder;
    use App\Services\Teacher\TeachingSchedule;

    $done = $sessions->filter(fn ($s) => $s->hasAttendance())->count();
@endphp

<x-app-layout title="Absensi Hari Ini">
    <x-ui.page-header title="Absensi hari ini" :eyebrow="TeachingSchedule::format($now, 'l, j F Y')"
        :subtitle="$sessions->isEmpty() ? 'Tidak ada sesi terjadwal hari ini.' : $done.' dari '.$sessions->count().' sesi sudah diabsen.'">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="clock" :href="route('teacher.attendance.index')">Riwayat absensi</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card title="Sesi hari ini" padding="none">
        @if ($sessions->isEmpty())
            <x-ui.empty-state icon="calendar" title="Tidak ada sesi hari ini."
                description="Untuk pertemuan tambahan di luar jadwal, buka kelasnya lalu pilih Isi absensi.">
                <x-slot:actions>
                    <x-ui.button variant="secondary" size="sm" :href="route('teacher.classes')">Kelas saya</x-ui.button>
                </x-slot:actions>
            </x-ui.empty-state>
        @else
            <ul class="divide-y divide-line" role="list">
                @foreach ($sessions as $session)
                    @include('teacher.schedules.partials.session', ['session' => $session, 'now' => $now])
                @endforeach
            </ul>
        @endif
    </x-ui.card>

    <x-ui.card title="Belum diabsen" class="mt-6" padding="none"
        subtitle="Sesi {{ AttendanceRecorder::EDIT_WINDOW_DAYS }} hari terakhir yang belum memiliki data kehadiran">
        @if ($missed->isEmpty())
            <x-ui.empty-state icon="check-circle" title="Semua sesi sudah diabsen."
                description="Data kehadiran yang lengkap membantu orang tua memantau anak." />
        @else
            <ul class="divide-y divide-line" role="list">
                @foreach ($missed as $session)
                    @include('teacher.schedules.partials.session', ['session' => $session, 'now' => $now, 'showDate' => true])
                @endforeach
            </ul>
        @endif
    </x-ui.card>

    @if ($classes->isNotEmpty())
        <x-ui.card title="Pertemuan di luar jadwal" class="mt-6"
            subtitle="Pertemuan pengganti atau tambahan? Pilih kelas dan tanggalnya.">
            <form method="GET" action="{{ route('teacher.attendance.today') }}" class="grid gap-4 sm:grid-cols-[1fr_12rem_auto] sm:items-end">
                <x-ui.field label="Kelas" for="class">
                    <x-ui.select name="class" placeholder="Pilih kelas" required :options="$classes->pluck('name', 'id')" />
                </x-ui.field>
                <x-ui.field label="Tanggal" for="date">
                    <x-ui.input name="date" type="date" :value="$now->toDateString()" max="{{ $now->toDateString() }}"
                        min="{{ $now->subDays(AttendanceRecorder::EDIT_WINDOW_DAYS)->toDateString() }}" required />
                </x-ui.field>
                <x-ui.button type="submit" variant="secondary" icon-right="arrow-right">Buka lembar absensi</x-ui.button>
            </form>
        </x-ui.card>
    @endif
</x-app-layout>
