<?php

namespace App\Http\Requests\Teacher;

use App\Models\Attendance;
use App\Models\ClassName;
use App\Services\Teacher\AttendanceRecorder;
use App\Services\Teacher\TeachingSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Attendance sheet of one meeting: POST teacher/attendance/classes/{class}.
 *
 * Only a teacher of the (planned/active) class may submit; the date may not be
 * in the future nor older than AttendanceRecorder::EDIT_WINDOW_DAYS; every row
 * must belong to a student on the meeting's roster.
 */
class StoreAttendanceRequest extends FormRequest
{
    /**
     * The teacher must currently teach the class.
     */
    public function authorize(): bool
    {
        $class = $this->route('class');

        return $class instanceof ClassName
            && $this->user()?->taughtClasses()->where('class_names.id', $class->id)->exists();
    }

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $today = TeachingSchedule::today();

        return [
            'date' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:'.$today->toDateString(),
                'after_or_equal:'.$today->subDays(AttendanceRecorder::EDIT_WINDOW_DAYS)->toDateString(),
            ],
            'attendance' => ['required', 'array', 'min:1'],
            'attendance.*' => ['required', 'array'],
            'attendance.*.status' => ['required', 'string', Rule::in(array_keys(Attendance::statuses()))],
            'attendance.*.notes' => ['nullable', 'string', 'max:500'],
            'attendance.*.minutes_late' => ['nullable', 'integer', 'min:0', 'max:240'],
        ];
    }

    /**
     * Cross-field checks: class period and roster membership.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var ClassName $class */
                $class = $this->route('class');
                $date = TeachingSchedule::parseDate($this->input('date'));

                if (! $date) {
                    return;
                }

                if ($class->start_date && $date->format('Y-m-d') < $class->start_date->format('Y-m-d')) {
                    $validator->errors()->add('date', 'Tanggal pertemuan sebelum kelas dimulai ('.TeachingSchedule::format($class->start_date, 'j M Y').').');

                    return;
                }

                $roster = app(AttendanceRecorder::class)->roster($class, CarbonImmutable::parse($date))
                    ->map(fn (array $row) => (int) $row['student']->id)
                    ->all();

                foreach (array_keys((array) $this->input('attendance', [])) as $studentId) {
                    if (! in_array((int) $studentId, $roster, true)) {
                        $validator->errors()->add('attendance', 'Ada siswa yang tidak terdaftar di kelas ini. Muat ulang halaman lalu coba lagi.');

                        return;
                    }
                }
            },
        ];
    }

    /**
     * Indonesian attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'date' => 'tanggal pertemuan',
            'attendance' => 'daftar kehadiran',
            'attendance.*.status' => 'status kehadiran',
            'attendance.*.notes' => 'catatan',
            'attendance.*.minutes_late' => 'menit terlambat',
        ];
    }

    /**
     * Custom messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.before_or_equal' => 'Absensi tidak dapat diisi untuk tanggal yang belum terjadi.',
            'date.after_or_equal' => 'Absensi hanya dapat diisi atau diubah paling lama '.AttendanceRecorder::EDIT_WINDOW_DAYS.' hari setelah pertemuan.',
            'attendance.required' => 'Tandai kehadiran minimal satu siswa.',
            'attendance.*.status.required' => 'Pilih status kehadiran untuk setiap siswa.',
            'attendance.*.status.in' => 'Status kehadiran tidak dikenal.',
        ];
    }
}
