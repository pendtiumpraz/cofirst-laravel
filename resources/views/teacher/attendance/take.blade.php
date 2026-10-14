@php
    use App\Models\Attendance;
    use App\Services\Teacher\AttendanceRecorder;
    use App\Services\Teacher\TeachingSchedule;

    $icons = [
        Attendance::STATUS_PRESENT => 'check-circle',
        Attendance::STATUS_LATE => 'clock',
        Attendance::STATUS_EXCUSED => 'hand-raised',
        Attendance::STATUS_ABSENT => 'x-circle',
    ];
    // Order on the sheet: the most common choice first.
    $order = [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE, Attendance::STATUS_EXCUSED, Attendance::STATUS_ABSENT];
    $checked = [
        Attendance::STATUS_PRESENT => 'has-[:checked]:border-success has-[:checked]:bg-success-soft has-[:checked]:text-success',
        Attendance::STATUS_LATE => 'has-[:checked]:border-warning has-[:checked]:bg-warning-soft has-[:checked]:text-warning',
        Attendance::STATUS_EXCUSED => 'has-[:checked]:border-info has-[:checked]:bg-info-soft has-[:checked]:text-info',
        Attendance::STATUS_ABSENT => 'has-[:checked]:border-danger has-[:checked]:bg-danger-soft has-[:checked]:text-danger',
    ];
    $initial = $roster->mapWithKeys(fn ($row) => [
        (string) $row['student']->id => old('attendance.'.$row['student']->id.'.status', $row['attendance']?->status ?? ''),
    ]);
    $dayName = TeachingSchedule::format($date, 'l');
@endphp

<x-app-layout :title="'Absensi '.$class->name">
    <x-ui.page-header :title="$class->name" eyebrow="Lembar absensi"
        :subtitle="TeachingSchedule::format($date, 'l, j F Y').($slot ? ' · '.$slot['start'].'–'.$slot['end'].($slot['room'] ? ' · '.$slot['room'] : '') : '')"
        :breadcrumbs="['Absensi' => route('teacher.attendance.today'), $class->name => route('teacher.class-detail', $class), TeachingSchedule::format($date, 'j M') => null]">
        <x-slot:actions>
            <form method="GET" action="{{ route('teacher.attendance.take', $class) }}" class="flex items-end gap-2">
                <div>
                    <label for="ganti-tanggal" class="sr-only">Tanggal pertemuan</label>
                    <input id="ganti-tanggal" type="date" name="date" class="input w-auto" value="{{ $date->toDateString() }}"
                        max="{{ $today->toDateString() }}" required>
                </div>
                <x-ui.button type="submit" variant="secondary">Ganti tanggal</x-ui.button>
            </form>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="space-y-4">
        @if ($locked)
            <x-ui.alert tone="warning" title="Hanya dapat dilihat">
                Absensi hanya dapat diisi atau diubah paling lama {{ AttendanceRecorder::EDIT_WINDOW_DAYS }} hari setelah pertemuan.
                Hubungi admin bila ada data yang perlu dikoreksi.
            </x-ui.alert>
        @elseif (! $scheduled)
            <x-ui.alert tone="info" title="Di luar jadwal rutin">
                Kelas ini tidak dijadwalkan pada hari {{ $dayName }}. Anda tetap dapat mencatat absensi untuk pertemuan pengganti atau tambahan.
            </x-ui.alert>
        @endif

        @if ($recorded && ! $locked)
            <x-ui.alert tone="neutral" icon="information-circle">
                Absensi pertemuan ini sudah tercatat. Perubahan yang Anda simpan akan memperbarui data sebelumnya.
            </x-ui.alert>
        @endif
    </div>

    @if ($roster->isEmpty())
        <x-ui.empty-state size="page" class="mt-6" icon="user-group" title="Belum ada siswa aktif di kelas ini."
            description="Lembar absensi tersedia setelah ada siswa yang terdaftar dan aktif.">
            <x-slot:actions><x-ui.button variant="secondary" :href="route('teacher.class-detail', $class)">Kembali ke kelas</x-ui.button></x-slot:actions>
        </x-ui.empty-state>
    @else
        <form method="POST" action="{{ route('teacher.attendance.store', $class) }}" class="mt-6"
            x-data="{
                rows: @js((object) $initial->all()),
                markAll(status) { Object.keys(this.rows).forEach((key) => this.rows[key] = status) },
                count(status) { return Object.values(this.rows).filter((value) => value === status).length },
                get remaining() { return Object.values(this.rows).filter((value) => ! value).length },
            }">
            @csrf
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">

            <x-ui.card padding="none">
                <div class="flex flex-col gap-3 border-b border-line px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <div>
                        <h2 class="card-title">{{ $roster->count() }} siswa</h2>
                        <p class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-sm text-fg-muted" aria-live="polite">
                            @foreach ($order as $status)
                                <span>{{ $statuses[$status] }} <strong class="font-num text-fg" x-text="count('{{ $status }}')">{{ $roster->filter(fn ($r) => ($initial[(string) $r['student']->id] ?? '') === $status)->count() }}</strong></span>
                            @endforeach
                            <span x-show="remaining > 0" x-cloak class="text-warning">· <span x-text="remaining"></span> belum ditandai</span>
                        </p>
                    </div>
                    @unless ($locked)
                        <x-ui.button variant="secondary" size="sm" icon="check-circle" x-on:click="markAll('{{ Attendance::STATUS_PRESENT }}')">Tandai semua hadir</x-ui.button>
                    @endunless
                </div>

                @error('attendance')
                    <div class="border-b border-line px-4 py-3 sm:px-5">
                        <p class="error-text"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>
                    </div>
                @enderror

                <ul class="divide-y divide-line" role="list">
                    @foreach ($roster as $row)
                        @php
                            $student = $row['student'];
                            $id = (string) $student->id;
                            $notes = old('attendance.'.$id.'.notes', $row['attendance']?->notes);
                            $late = old('attendance.'.$id.'.minutes_late', $row['attendance']?->minutes_late);
                            $rowError = $errors->first('attendance.'.$id.'.status');
                        @endphp
                        <li class="px-4 py-4 sm:px-5">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:gap-6">
                                <div class="flex min-w-0 items-center gap-3 md:w-56 md:shrink-0 md:pt-1.5">
                                    <x-ui.avatar :user="$student" size="sm" decorative />
                                    <div class="min-w-0">
                                        <p class="font-medium text-fg">{{ $student->name }}</p>
                                        <p class="text-xs text-fg-subtle">
                                            {{ $row['rate'] !== null ? 'Kehadiran '.number_format($row['rate'], 0, ',', '.').'%' : 'Pertemuan pertama' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1 space-y-3">
                                    <fieldset @if ($rowError) aria-describedby="status-error-{{ $id }}" @endif>
                                        <legend class="sr-only">Status kehadiran {{ $student->name }}</legend>
                                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                            @foreach ($order as $status)
                                                <label @class([
                                                    'relative flex min-h-11 cursor-pointer select-none items-center justify-center gap-2 rounded-md border border-line-input bg-surface px-3 text-sm font-medium text-fg-muted',
                                                    'transition-colors duration-fast hover:border-line-strong motion-reduce:transition-none',
                                                    'has-[:focus-visible]:outline has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-ring',
                                                    'has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-70',
                                                    $checked[$status],
                                                ])>
                                                    <input type="radio" class="sr-only" name="attendance[{{ $id }}][status]" value="{{ $status }}"
                                                        x-model="rows['{{ $id }}']" @checked(($initial[$id] ?? '') === $status) @disabled($locked) required>
                                                    <x-ui.icon :name="$icons[$status]" size="16" />
                                                    <span>{{ $statuses[$status] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @if ($rowError)
                                            <p id="status-error-{{ $id }}" class="error-text mt-2"><x-ui.icon name="m-exclamation-circle" /><span>{{ $rowError }}</span></p>
                                        @endif
                                    </fieldset>

                                    <div class="flex flex-col gap-2 sm:flex-row">
                                        <div class="min-w-0 flex-1">
                                            <label for="notes-{{ $id }}" class="sr-only">Catatan untuk {{ $student->name }}</label>
                                            <input id="notes-{{ $id }}" type="text" class="input" name="attendance[{{ $id }}][notes]" value="{{ $notes }}"
                                                maxlength="500" placeholder="Catatan (opsional), mis. izin sakit" @disabled($locked)>
                                        </div>
                                        <div class="sm:w-40" x-show="rows['{{ $id }}'] === '{{ Attendance::STATUS_LATE }}'" @if (($initial[$id] ?? '') !== Attendance::STATUS_LATE) x-cloak @endif>
                                            <label for="late-{{ $id }}" class="sr-only">Menit terlambat {{ $student->name }}</label>
                                            <div class="input-group">
                                                <input id="late-{{ $id }}" type="number" class="input pr-16" name="attendance[{{ $id }}][minutes_late]" value="{{ $late }}"
                                                    min="0" max="240" inputmode="numeric" placeholder="Terlambat" @disabled($locked)>
                                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-fg-subtle">menit</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                @unless ($locked)
                    <x-slot:footer class="flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-fg-subtle">
                            Terlambat: datang lebih dari {{ $lateTolerance }} menit setelah kelas dimulai.
                            @if (setting('gamification.enabled', true)) Siswa yang hadir mendapat poin kehadiran sekali per pertemuan. @endif
                        </p>
                        <div class="flex flex-col-reverse gap-2 sm:flex-row">
                            <x-ui.button variant="ghost" :href="route('teacher.attendance.today')">Batal</x-ui.button>
                            <x-ui.button type="submit" icon="check">Simpan absensi</x-ui.button>
                        </div>
                    </x-slot:footer>
                @endunless
            </x-ui.card>
        </form>
    @endif
</x-app-layout>
