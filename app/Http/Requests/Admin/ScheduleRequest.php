<?php

namespace App\Http\Requests\Admin;

use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Services\Scheduling\ScheduleConflictChecker;
use App\Services\Scheduling\ScheduleSlots;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validation for a weekly slot (Admin › Jadwal): class, day (1 = Senin …
 * 7 = Minggu), time, room, teacher of that class and the class's students.
 *
 * Conflict detection rejects the same teacher, the same physical room or the
 * same student at overlapping times (ScheduleConflictChecker).
 */
class ScheduleRequest extends AdminRequest
{
    /** Create needs create permission; update needs edit permission. */
    public function authorize(): bool
    {
        $schedule = $this->route('schedule');

        return $schedule instanceof Schedule
            ? $this->user()->can('update', $schedule)
            : $this->user()->can('create', Schedule::class);
    }

    /**
     * Normalise booleans and trim the room.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
            'room' => is_string($this->input('room')) ? (trim($this->input('room')) ?: null) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $classId = (int) $this->input('class_id');

        return [
            'class_id' => ['required', 'integer', Rule::exists('class_names', 'id')
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->whereIn('status', [ClassName::STATUS_PLANNED, ClassName::STATUS_ACTIVE])],
            'day_of_week' => ['required', 'integer', Rule::in(array_keys(Schedule::DAYS))],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:100'],
            'teacher_id' => ['required', 'integer', Rule::exists('class_teacher', 'teacher_id')->where('class_id', $classId)],
            'enrollment_ids' => ['required', 'array', 'min:1'],
            'enrollment_ids.*' => ['integer', 'distinct', Rule::exists('enrollments', 'id')
                ->where('class_id', $classId)
                ->whereIn('status', Enrollment::SEAT_STATUSES)],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Reject clashes with other active slots.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty() || ! $this->boolean('is_active')) {
                    return;
                }

                $schedule = $this->route('schedule');
                $ignore = $schedule instanceof Schedule
                    ? ScheduleSlots::siblingsQuery($schedule)->pluck('id')->map(fn ($id) => (int) $id)->all()
                    : [];

                $conflicts = app(ScheduleConflictChecker::class)->check($this->only([
                    'class_id', 'day_of_week', 'start_time', 'end_time', 'room', 'teacher_id', 'enrollment_ids',
                ]), $ignore);

                foreach ($conflicts as $field => $message) {
                    $validator->errors()->add($field, $message);
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'class_id' => 'kelas',
            'day_of_week' => 'hari',
            'start_time' => 'jam mulai',
            'end_time' => 'jam selesai',
            'room' => 'ruang/lokasi',
            'teacher_id' => 'pengajar',
            'enrollment_ids' => 'siswa',
            'enrollment_ids.*' => 'siswa',
            'is_active' => 'status',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'class_id.exists' => 'Pilih kelas yang aktif (direncanakan atau berlangsung).',
            'teacher_id.exists' => 'Pengajar harus salah satu pengajar kelas ini.',
            'enrollment_ids.required' => 'Pilih minimal satu siswa untuk jadwal ini.',
            'enrollment_ids.*.exists' => 'Siswa yang dipilih bukan siswa aktif di kelas ini.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
        ];
    }
}
