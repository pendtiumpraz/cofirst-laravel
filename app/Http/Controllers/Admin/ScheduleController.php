<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScheduleRequest;
use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\Admin\AdminAudit;
use App\Services\Scheduling\ScheduleSlot;
use App\Services\Scheduling\ScheduleSlots;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Admin › Jadwal: weekly slots of every class (calendar + list), with clash
 * detection. A slot is stored as one row per enrolled student; this controller
 * always works on the whole slot (ScheduleSlots::siblingsQuery()).
 */
class ScheduleController extends Controller
{
    /**
     * Weekly calendar or list of slots with filters.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Schedule::class);

        $view = $request->query('view') === 'list' ? 'list' : 'calendar';
        $classStatus = $request->query('class_status', 'ongoing');

        $rows = Schedule::query()
            ->with(['className:id,name,course_id,status,is_active,start_date,end_date', 'className.course:id,name',
                'teacherAssignment:id,teacher_id', 'teacherAssignment.teacher:id,name'])
            ->when($classStatus === 'ongoing', fn ($q) => $q->whereHas('className', fn ($c) => $c->activeAndOngoing()))
            ->when(in_array($classStatus, [ClassName::STATUS_PLANNED, ClassName::STATUS_ACTIVE, ClassName::STATUS_COMPLETED, ClassName::STATUS_CANCELLED], true),
                fn ($q) => $q->byClassStatus($classStatus))
            ->when($request->query('state') === 'inactive', fn ($q) => $q->where('schedules.is_active', false))
            ->when($request->query('state') === 'active', fn ($q) => $q->where('schedules.is_active', true))
            ->when($request->filled('class'), fn ($q) => $q->where('class_id', (int) $request->query('class')))
            ->when($request->filled('teacher'), fn ($q) => $q->whereHas('teacherAssignment', fn ($a) => $a->where('teacher_id', (int) $request->query('teacher'))))
            ->when($request->filled('day'), fn ($q) => $q->where('day_of_week', (int) $request->query('day')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], trim((string) $request->query('search'))).'%';
                $q->where(fn ($w) => $w->where('room', 'like', $term)->orWhereHas('className', fn ($c) => $c->where('name', 'like', $term)));
            })
            ->get();

        $slots = ScheduleSlots::group($rows);
        $clashes = ScheduleSlots::clashes($slots);

        $list = null;
        if ($view === 'list') {
            $page = LengthAwarePaginator::resolveCurrentPage();
            $list = new LengthAwarePaginator($slots->forPage($page, 25)->values(), $slots->count(), 25, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        return view('admin.schedules.index', [
            'view' => $view,
            'week' => $view === 'calendar' ? $this->week($slots) : [],
            'list' => $list,
            'slotCount' => $slots->count(),
            'clashes' => $clashes,
            'todayIso' => \App\Services\Admin\AdminClock::isoWeekday(),
            'classStatus' => $classStatus,
            'classes' => ClassName::activeAndOngoing()->orderBy('name')->pluck('name', 'id'),
            'teachers' => User::role('teacher')->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Create form.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Schedule::class);

        return view('admin.schedules.create', [
            'schedule' => null,
            'classes' => $this->classOptions(),
            'preset' => [
                'class_id' => $request->integer('class') ?: null,
                'day_of_week' => $request->integer('day') ?: null,
                'start_time' => null,
                'end_time' => null,
                'room' => null,
                'teacher_id' => null,
                'enrollment_ids' => [],
                'is_active' => true,
            ],
            'duration' => (int) setting('academic.session_duration_minutes', 90),
            'rooms' => $this->knownRooms(),
        ]);
    }

    /**
     * Create a slot: one row per selected student.
     */
    public function store(ScheduleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $first = DB::transaction(function () use ($data) {
            $assignmentId = $this->assignmentId((int) $data['class_id'], (int) $data['teacher_id']);
            $first = null;

            foreach (array_unique(array_map('intval', $data['enrollment_ids'])) as $enrollmentId) {
                $row = Schedule::create([
                    'class_id' => $data['class_id'],
                    'day_of_week' => $data['day_of_week'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'room' => $data['room'] ?? null,
                    'teacher_assignment_id' => $assignmentId,
                    'enrollment_id' => $enrollmentId,
                    'is_active' => $data['is_active'],
                ]);
                $first ??= $row;
            }

            return $first;
        });

        AdminAudit::log('schedule.created', $first, ['students' => count($data['enrollment_ids'])]);

        return redirect()->route('admin.schedules.show', $first)
            ->with('success', 'Jadwal '.Schedule::DAYS[$data['day_of_week']].' '.$data['start_time'].' berhasil dibuat.');
    }

    /**
     * Slot detail with its students and any clashes.
     */
    public function show(Schedule $schedule): View
    {
        $this->authorize('view', $schedule);

        $slot = $this->slot($schedule);
        $sameDay = Schedule::query()->forCalendar()->where('day_of_week', $schedule->day_of_week)
            ->with(['className:id,name', 'teacherAssignment.teacher:id,name'])->get();
        $clashes = ScheduleSlots::clashes(ScheduleSlots::group($sameDay->push(...$slot->rows)->unique('id')));

        return view('admin.schedules.show', [
            'slot' => $slot,
            'schedule' => $schedule,
            'clashReasons' => $clashes[$slot->key] ?? [],
        ]);
    }

    /**
     * Edit form for the whole slot.
     */
    public function edit(Schedule $schedule): View
    {
        $this->authorize('update', $schedule);

        $slot = $this->slot($schedule);

        return view('admin.schedules.edit', [
            'schedule' => $schedule,
            'slot' => $slot,
            'classes' => $this->classOptions($schedule->class_id),
            'preset' => [
                'class_id' => $schedule->class_id,
                'day_of_week' => $schedule->day_of_week,
                'start_time' => $slot->start(),
                'end_time' => $slot->end(),
                'room' => $schedule->room,
                'teacher_id' => $slot->teacher()?->id,
                'enrollment_ids' => $slot->rows->pluck('enrollment_id')->filter()->map(fn ($id) => (int) $id)->values()->all(),
                'is_active' => $slot->isActive(),
            ],
            'duration' => (int) setting('academic.session_duration_minutes', 90),
            'rooms' => $this->knownRooms(),
        ]);
    }

    /**
     * Update the whole slot: time/room/teacher for every row, add rows for
     * newly selected students and remove rows of deselected students.
     */
    public function update(ScheduleRequest $request, Schedule $schedule): RedirectResponse
    {
        $data = $request->validated();
        $selected = array_values(array_unique(array_map('intval', $data['enrollment_ids'])));

        $representative = DB::transaction(function () use ($schedule, $data, $selected) {
            $rows = ScheduleSlots::siblingsQuery($schedule)->get();
            $assignmentId = $this->assignmentId((int) $data['class_id'], (int) $data['teacher_id']);
            $attributes = [
                'class_id' => $data['class_id'],
                'day_of_week' => $data['day_of_week'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'room' => $data['room'] ?? null,
                'teacher_assignment_id' => $assignmentId,
                'is_active' => $data['is_active'],
            ];

            // Reason: rows of deselected students are deleted; class reports and
            // attendance keep their data (schedule_id is set to null).
            $rows->filter(fn (Schedule $row) => ! in_array((int) $row->enrollment_id, $selected, true))->each->delete();

            $kept = $rows->filter(fn (Schedule $row) => in_array((int) $row->enrollment_id, $selected, true));
            $kept->each(fn (Schedule $row) => $row->update($attributes));

            $existing = $kept->pluck('enrollment_id')->map(fn ($id) => (int) $id)->all();
            foreach (array_diff($selected, $existing) as $enrollmentId) {
                $kept->push(Schedule::create($attributes + ['enrollment_id' => $enrollmentId]));
            }

            return $kept->sortBy('id')->first();
        });

        AdminAudit::log('schedule.updated', $representative, ['students' => count($selected)]);

        return redirect()->route('admin.schedules.show', $representative)->with('success', 'Perubahan jadwal disimpan untuk '.count($selected).' siswa.');
    }

    /**
     * Activate or deactivate the whole slot.
     */
    public function toggleStatus(Schedule $schedule): RedirectResponse
    {
        $this->authorize('update', $schedule);

        $slot = $this->slot($schedule);
        $activate = ! $slot->isActive();

        if ($activate && ! $schedule->className?->canShowInCalendar()) {
            return back()->with('error', 'Kelas ini sudah selesai atau dibatalkan, jadwalnya tidak dapat diaktifkan.');
        }

        ScheduleSlots::siblingsQuery($schedule)->update(['is_active' => $activate]);
        AdminAudit::log($activate ? 'schedule.activated' : 'schedule.deactivated', $schedule);

        return back()->with('success', $activate ? 'Jadwal diaktifkan kembali.' : 'Jadwal dinonaktifkan dan tidak tampil di kalender.');
    }

    /**
     * Delete the whole slot (reports and attendance keep their data).
     */
    public function destroy(Schedule $schedule): RedirectResponse
    {
        $this->authorize('delete', $schedule);

        $count = DB::transaction(function () use ($schedule) {
            $rows = ScheduleSlots::siblingsQuery($schedule)->get();
            $rows->each->delete();

            return $rows->count();
        });
        AdminAudit::log('schedule.deleted', $schedule, ['rows' => $count]);

        return redirect()->route('admin.schedules.index')->with('success', "Jadwal dihapus ({$count} siswa). Riwayat laporan dan absensi tetap tersimpan.");
    }

    /**
     * Load a slot (all sibling rows) with the relations the views need.
     */
    private function slot(Schedule $schedule): ScheduleSlot
    {
        $rows = ScheduleSlots::siblingsQuery($schedule)
            ->with(['className.course:id,name', 'className.teachers:id,name', 'teacherAssignment.teacher:id,name,profile_photo_path',
                'enrollment:id,student_id,status', 'enrollment.student:id,name,profile_photo_path'])
            ->get();

        return new ScheduleSlot(ScheduleSlots::key($schedule), $rows->isEmpty() ? collect([$schedule]) : $rows);
    }

    /**
     * Seven weekday columns of slots.
     *
     * @return array<int, array{name: string, slots: \Illuminate\Support\Collection}>
     */
    private function week($slots): array
    {
        $week = [];
        foreach (Schedule::DAYS as $day => $name) {
            $week[$day] = ['name' => $name, 'slots' => $slots->filter(fn (ScheduleSlot $s) => $s->day() === $day)->values()];
        }

        return $week;
    }

    /**
     * Open classes with teachers for the form (name + course; details load by JSON).
     *
     * @return array<int, array{value: int, label: string}>
     */
    private function classOptions(?int $keepClassId = null): array
    {
        return ClassName::query()
            ->where(fn ($q) => $q->activeAndOngoing()->when($keepClassId, fn ($w) => $w->orWhere('class_names.id', $keepClassId)))
            ->whereHas('teachers')
            ->with('course:id,name')
            ->withCount(['enrollments as seat_count' => fn ($q) => $q->whereIn('status', Enrollment::SEAT_STATUSES)])
            ->orderBy('name')
            ->get()
            ->map(fn (ClassName $c) => [
                'value' => $c->id,
                'label' => $c->name.' · '.($c->course?->name ?? 'Tanpa kursus').' · '.$c->seat_count.' siswa',
                'disabled' => $c->seat_count === 0 && $c->id !== $keepClassId,
            ])
            ->all();
    }

    /**
     * Rooms already in use, for the room suggestions list.
     *
     * @return array<int, string>
     */
    private function knownRooms(): array
    {
        return Schedule::query()->whereNotNull('room')->where('room', '!=', '')
            ->distinct()->orderBy('room')->limit(30)->pluck('room')->prepend('Online')->unique()->values()->all();
    }

    /**
     * Current teacher-assignment id for a teacher of the class (created when missing).
     */
    private function assignmentId(int $classId, int $teacherId): int
    {
        $assignment = TeacherAssignment::where('class_id', $classId)->where('teacher_id', $teacherId)->whereNull('unassigned_at')->first()
            ?? TeacherAssignment::create(['class_id' => $classId, 'teacher_id' => $teacherId, 'assigned_at' => now()]);

        return (int) $assignment->id;
    }
}
