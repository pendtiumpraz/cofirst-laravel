<?php

namespace App\Services\Academic;

use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Single source of truth for "which classes and curricula may this user see".
 *
 * Used by the academic policies and controllers so the teacher, student and parent
 * portals apply the same rules (the old controllers re-implemented them inline and
 * disagreed with each other). Results are memoised per request.
 */
class AcademicScope
{
    /** Enrollment statuses that give a student access to the class content. */
    public const ACCESS_ENROLLMENT_STATUSES = [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED];

    /** Class statuses a teacher works with (cancelled classes are hidden). */
    public const TEACHER_CLASS_STATUSES = [ClassName::STATUS_PLANNED, ClassName::STATUS_ACTIVE, ClassName::STATUS_COMPLETED];

    /** Whether the user is an admin or superadmin. */
    public function isAdmin(User $user): bool
    {
        return $this->remember('admin:'.$user->id, fn () => $user->hasAnyRole(['admin', 'superadmin']));
    }

    /**
     * IDs of the classes a teacher is assigned to (planned, active or completed).
     *
     * Returns:
     *     Collection<int, int>
     */
    public function teacherClassIds(User $teacher): Collection
    {
        return $this->remember('tclasses:'.$teacher->id, fn () => $teacher->teachingClasses()
            ->whereIn('class_names.status', self::TEACHER_CLASS_STATUSES)
            ->pluck('class_names.id')
            ->map(fn ($id) => (int) $id));
    }

    /**
     * Curricula used by the teacher's classes.
     *
     * Reason: access follows class_names.curriculum_id, not the course, because a
     * course may have several curriculum variants (fast-track, regular, …).
     *
     * Returns:
     *     Collection<int, int>
     */
    public function teacherCurriculumIds(User $teacher): Collection
    {
        return $this->remember('tcurr:'.$teacher->id, fn () => ClassName::query()
            ->whereIn('id', $this->teacherClassIds($teacher))
            ->whereNotNull('curriculum_id')
            ->distinct()
            ->pluck('curriculum_id')
            ->map(fn ($id) => (int) $id)
            ->values());
    }

    /** Whether the teacher teaches the class. */
    public function teacherTeaches(User $teacher, int|ClassName $class): bool
    {
        $id = $class instanceof ClassName ? $class->id : $class;

        return $this->teacherClassIds($teacher)->contains((int) $id);
    }

    /**
     * Enrollments that give a student access to class content, keyed by class id.
     *
     * Returns:
     *     Collection<int, Enrollment>
     */
    public function studentEnrollments(User $student): Collection
    {
        return $this->remember('senr:'.$student->id, fn () => Enrollment::query()
            ->where('student_id', $student->id)
            ->whereIn('status', self::ACCESS_ENROLLMENT_STATUSES)
            ->whereNotNull('class_id')
            ->whereHas('className')
            ->with('className:id,curriculum_id,course_id,name,status,start_date,end_date')
            ->get()
            ->keyBy('class_id'));
    }

    /** The access-giving enrollment of a student in a class, if any. */
    public function studentEnrollment(User $student, int|ClassName $class): ?Enrollment
    {
        $id = $class instanceof ClassName ? $class->id : $class;

        return $this->studentEnrollments($student)->get((int) $id);
    }

    /**
     * IDs of the student's classes that follow a curriculum.
     *
     * Returns:
     *     Collection<int, int>
     */
    public function studentClassIdsForCurriculum(User $student, int $curriculumId): Collection
    {
        return $this->studentEnrollments($student)
            ->filter(fn (Enrollment $e) => (int) $e->className?->curriculum_id === $curriculumId)
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    /**
     * IDs of a parent's children.
     *
     * Returns:
     *     Collection<int, int>
     */
    public function childIds(User $parent): Collection
    {
        return $this->remember('kids:'.$parent->id, fn () => $parent->children()
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id));
    }

    /** Whether the user is a parent of the student. */
    public function isParentOf(User $parent, int|User $student): bool
    {
        $id = $student instanceof User ? $student->id : $student;

        return $this->childIds($parent)->contains((int) $id);
    }

    /**
     * Restrict a class_names query to the classes a teacher teaches.
     */
    public function scopeTeacherClasses(Builder $query, User $teacher): Builder
    {
        return $query->whereIn('class_names.id', $this->teacherClassIds($teacher));
    }

    /**
     * Memoise a value for the current request.
     *
     * Reason: the memo lives on the request object, so every HTTP request (and every
     * test request) starts fresh while repeated policy checks in one page are cheap.
     */
    private function remember(string $key, \Closure $callback): mixed
    {
        $attributes = request()->attributes;
        $key = 'academic.scope.'.$key;

        if (! $attributes->has($key)) {
            $attributes->set($key, $callback());
        }

        return $attributes->get($key);
    }
}
