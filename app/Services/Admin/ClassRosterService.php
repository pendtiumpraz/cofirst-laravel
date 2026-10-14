<?php

namespace App\Services\Admin;

use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\User;
use App\Services\Scheduling\ScheduleSlots;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Enrolment rules shared by the class roster and the enrollment screens:
 * capacity (pending + active seats), duplicates, and keeping the per-student
 * schedule rows in step with the class's weekly slots.
 */
class ClassRosterService
{
    /**
     * Enrol a student in a class.
     *
     * Runs in a transaction and locks the class row so two admins cannot
     * overbook the last seat at the same time.
     *
     * Args:
     *     class (ClassName): Target class.
     *     student (User): Must hold the student role.
     *     attributes (array): Optional enrollment_date, status, payment_status, notes.
     *     field (string): Validation field used for error messages.
     *
     * Returns:
     *     Enrollment: The new enrollment.
     *
     * Raises:
     *     ValidationException: Duplicate enrolment, full class or invalid student.
     */
    public function enroll(ClassName $class, User $student, array $attributes = [], string $field = 'student_id'): Enrollment
    {
        if (! $student->hasRole('student')) {
            throw ValidationException::withMessages([$field => 'Pengguna yang dipilih bukan siswa.']);
        }

        return DB::transaction(function () use ($class, $student, $attributes, $field) {
            $locked = ClassName::whereKey($class->id)->lockForUpdate()->firstOrFail();

            $existing = Enrollment::where('class_id', $locked->id)->where('student_id', $student->id)->first();
            if ($existing && in_array($existing->status, Enrollment::SEAT_STATUSES, true)) {
                throw ValidationException::withMessages([$field => 'Siswa ini sudah terdaftar di kelas tersebut.']);
            }

            $status = $attributes['status'] ?? Enrollment::STATUS_ACTIVE;
            if (in_array($status, Enrollment::SEAT_STATUSES, true) && ! $locked->hasCapacity()) {
                throw ValidationException::withMessages([$field => "Kelas {$locked->name} sudah penuh ({$locked->max_students} kursi)."]);
            }

            $data = [
                'course_id' => $locked->course_id,
                'enrollment_date' => $attributes['enrollment_date'] ?? AdminClock::now()->toDateString(),
                'status' => $status,
                'payment_status' => $attributes['payment_status'] ?? 'unpaid',
                'notes' => $attributes['notes'] ?? null,
            ];

            // Reason: re-enrolling a student who dropped out reuses the old row so
            // attendance and reports stay attached to one enrollment history.
            $enrollment = $existing
                ? tap($existing)->update($data)
                : Enrollment::create($data + ['class_id' => $locked->id, 'student_id' => $student->id]);

            if (in_array($status, Enrollment::SEAT_STATUSES, true)) {
                $this->attachToSlots($enrollment);
            }

            return $enrollment;
        });
    }

    /**
     * Change an enrollment's status, enforcing capacity when it takes a seat again.
     *
     * Args:
     *     enrollment (Enrollment): The enrollment.
     *     status (string): New status (Enrollment::statuses() key).
     *
     * Raises:
     *     ValidationException: The class is full.
     */
    public function changeStatus(Enrollment $enrollment, string $status): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $status) {
            $takesSeat = in_array($status, Enrollment::SEAT_STATUSES, true);
            $hadSeat = in_array($enrollment->status, Enrollment::SEAT_STATUSES, true);

            if ($takesSeat && ! $hadSeat && $enrollment->class_id) {
                $class = ClassName::whereKey($enrollment->class_id)->lockForUpdate()->first();
                if ($class && ! $class->hasCapacity()) {
                    throw ValidationException::withMessages(['status' => "Kelas {$class->name} sudah penuh."]);
                }
            }

            $enrollment->update(['status' => $status]);

            if ($takesSeat) {
                $this->attachToSlots($enrollment);
            } else {
                // Reason: a student who left must disappear from teacher calendars,
                // but rows are kept (inactive) so reports linked to them survive.
                $enrollment->schedules()->update(['is_active' => false]);
            }

            return $enrollment;
        });
    }

    /**
     * Move an enrollment to another class (capacity re-checked).
     *
     * Raises:
     *     ValidationException: Target class full or student already there.
     */
    public function moveToClass(Enrollment $enrollment, ClassName $target): Enrollment
    {
        if ((int) $enrollment->class_id === (int) $target->id) {
            return $enrollment;
        }

        return DB::transaction(function () use ($enrollment, $target) {
            $locked = ClassName::whereKey($target->id)->lockForUpdate()->firstOrFail();

            if (Enrollment::where('class_id', $locked->id)->where('student_id', $enrollment->student_id)->whereKeyNot($enrollment->id)->exists()) {
                throw ValidationException::withMessages(['class_id' => 'Siswa ini sudah terdaftar di kelas tujuan.']);
            }

            if (in_array($enrollment->status, Enrollment::SEAT_STATUSES, true) && ! $locked->hasCapacity()) {
                throw ValidationException::withMessages(['class_id' => "Kelas {$locked->name} sudah penuh."]);
            }

            // The old class's slots no longer apply to this student.
            $enrollment->schedules()->delete();
            $enrollment->update(['class_id' => $locked->id, 'course_id' => $locked->course_id]);

            if (in_array($enrollment->status, Enrollment::SEAT_STATUSES, true)) {
                $this->attachToSlots($enrollment);
            }

            return $enrollment;
        });
    }

    /**
     * Give the enrollment a schedule row in every weekly slot of its class
     * (re-activating rows it already has).
     */
    public function attachToSlots(Enrollment $enrollment): void
    {
        if (! $enrollment->class_id) {
            return;
        }

        $enrollment->schedules()->update(['is_active' => true]);

        $classRows = Schedule::where('class_id', $enrollment->class_id)->where('is_active', true)->get();
        $owned = $enrollment->schedules()->get()->map(fn (Schedule $row) => ScheduleSlots::key($row))->all();

        foreach (ScheduleSlots::group($classRows) as $slot) {
            if (in_array($slot->key, $owned, true)) {
                continue;
            }

            $template = $slot->schedule();
            Schedule::create([
                'class_id' => $template->class_id,
                'day_of_week' => $template->day_of_week,
                'start_time' => $template->getRawOriginal('start_time'),
                'end_time' => $template->getRawOriginal('end_time'),
                'room' => $template->room,
                'teacher_assignment_id' => $template->teacher_assignment_id,
                'enrollment_id' => $enrollment->id,
                'is_active' => true,
            ]);
        }
    }
}
