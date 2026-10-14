<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\Admin\AdminClock;
use App\Services\Admin\AdminDashboardService;
use App\Support\Navigation\RouteAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

/**
 * Admin › Beranda (cockpit) and Admin › Kinerja Pengajar.
 */
class DashboardController extends Controller
{
    /**
     * The admin cockpit: KPIs, trends, today's sessions, approvals and capacity.
     */
    public function index(Request $request, AdminDashboardService $dashboard): View
    {
        $user = $request->user();
        $data = $dashboard->build();

        $data['quickActions'] = collect([
            ['label' => 'Tambah pengguna', 'icon' => 'user-plus', 'route' => 'admin.users.create'],
            ['label' => 'Buat kelas', 'icon' => 'rectangle-group', 'route' => 'admin.classes.create'],
            ['label' => 'Daftarkan siswa', 'icon' => 'clipboard-document-check', 'route' => 'admin.enrollments.create'],
            ['label' => 'Atur jadwal', 'icon' => 'calendar-days', 'route' => 'admin.schedules.create'],
            ['label' => 'Catat pembayaran', 'icon' => 'banknotes', 'route' => 'finance.transactions.create'],
            ['label' => 'Pengaturan', 'icon' => 'cog-6-tooth', 'route' => 'admin.settings.index'],
        ])->filter(fn ($action) => Route::has($action['route']) && RouteAccess::allows($user, $action['route']))
            ->map(fn ($action) => $action + ['url' => route($action['route'])])
            ->values();

        $data['links'] = [
            'finance' => collect(['admin.finance.dashboard', 'finance.transactions.index'])
                ->first(fn ($name) => Route::has($name) && RouteAccess::allows($user, $name)),
            'privacy' => Route::has('admin.privacy.dashboard') && RouteAccess::allows($user, 'admin.privacy.dashboard') ? 'admin.privacy.dashboard' : null,
        ];
        $data['isSuperadmin'] = $user->hasRole('superadmin');
        $data['greeting'] = $this->greeting($data['now']->hour);

        return view('admin.dashboard', $data);
    }

    /**
     * Teacher performance overview for a period (this month, 3 months, all time).
     */
    public function teachers(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $period = in_array($request->query('period'), ['month', 'quarter', 'all'], true) ? $request->query('period') : 'month';
        $now = AdminClock::now();
        $from = match ($period) {
            'month' => $now->copy()->startOfMonth()->toDateString(),
            'quarter' => $now->copy()->startOfMonth()->subMonthsNoOverflow(2)->toDateString(),
            default => null,
        };

        $teachers = User::whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
            ->where('is_active', true)
            ->withCount(['taughtClasses as classes_count'])
            ->orderBy('name')
            ->get(['id', 'name', 'profile_photo_path']);

        $students = DB::table('class_teacher')
            ->join('enrollments', 'enrollments.class_id', '=', 'class_teacher.class_id')
            ->where('enrollments.status', Enrollment::STATUS_ACTIVE)
            ->groupBy('class_teacher.teacher_id')
            ->selectRaw('class_teacher.teacher_id, count(distinct enrollments.student_id) as total')
            ->pluck('total', 'teacher_id');

        $reports = DB::table('class_reports')
            ->when($from, fn ($q) => $q->where('report_date', '>=', $from))
            ->select(['teacher_id', 'class_id', 'report_date'])
            ->get();
        $meetings = $reports->unique(fn ($r) => $r->teacher_id.'|'.$r->class_id.'|'.$r->report_date)->countBy('teacher_id');
        $reportCounts = $reports->countBy('teacher_id');

        $attendance = DB::table('attendance')
            ->join('class_teacher', 'class_teacher.class_id', '=', 'attendance.class_id')
            ->when($from, fn ($q) => $q->where('attendance.attendance_date', '>=', $from))
            ->groupBy('class_teacher.teacher_id')
            ->selectRaw("class_teacher.teacher_id, count(*) as total, sum(case when attendance.status in ('".implode("','", Attendance::ATTENDED_STATUSES)."') then 1 else 0 end) as attended")
            ->get()
            ->keyBy('teacher_id');

        $rows = $teachers->map(function (User $teacher) use ($students, $meetings, $reportCounts, $attendance) {
            $att = $attendance->get($teacher->id);

            return [
                'teacher' => $teacher,
                'classes' => (int) $teacher->classes_count,
                'students' => (int) ($students[$teacher->id] ?? 0),
                'meetings' => (int) ($meetings[$teacher->id] ?? 0),
                'reports' => (int) ($reportCounts[$teacher->id] ?? 0),
                'attendance' => $att && $att->total > 0 ? round($att->attended / $att->total * 100, 1) : null,
            ];
        });

        $sort = in_array($request->query('sort'), ['students', 'meetings', 'reports', 'attendance', 'classes'], true) ? $request->query('sort') : 'meetings';
        $rows = $rows->sortByDesc(fn ($row) => $row[$sort] ?? -1)->values();

        return view('admin.teachers.ranking', [
            'rows' => $rows,
            'period' => $period,
            'sort' => $sort,
            'periods' => ['month' => 'Bulan ini', 'quarter' => '3 bulan terakhir', 'all' => 'Sepanjang waktu'],
        ]);
    }

    /**
     * Indonesian greeting for the hour of day (business timezone).
     */
    private function greeting(int $hour): string
    {
        return match (true) {
            $hour < 11 => 'Selamat pagi',
            $hour < 15 => 'Selamat siang',
            $hour < 19 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }
}
