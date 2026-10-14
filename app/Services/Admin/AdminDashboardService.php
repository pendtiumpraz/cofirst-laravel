<?php

namespace App\Services\Admin;

use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\FinancialTransaction;
use App\Models\Schedule;
use App\Models\User;
use App\Services\Scheduling\ScheduleSlots;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Figures for the admin cockpit (Admin › Beranda).
 *
 * Revenue follows the finance rule: settled payments minus settled refunds,
 * dated by paid_date (falls back to transaction_date), grouped in the
 * business timezone.
 */
class AdminDashboardService
{
    /** Classes at or above this share of seats count as "nearly full". */
    public const NEAR_CAPACITY = 0.8;

    /**
     * Everything the dashboard view needs.
     *
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $now = AdminClock::now();
        $months = $this->months($now, 6);
        $enrollmentSeries = $this->enrollmentSeries($months);
        $revenueSeries = $this->revenueSeries($months);
        $thisMonth = array_key_last($months);
        $lastMonth = array_keys($months)[count($months) - 2];

        $overdue = FinancialTransaction::query()->overdue();

        // Reason: comparing a half-finished month with a whole month always looks like
        // a drop; KPIs compare month-to-date with the same days of last month.
        $prevStart = $now->copy()->startOfMonth()->subMonthNoOverflow();
        $prevEnd = $prevStart->copy()->day(min($now->day, $prevStart->daysInMonth))->endOfDay();

        return [
            'now' => $now,
            'kpis' => [
                'active_students' => Enrollment::query()->active()->distinct()->count('student_id'),
                'student_accounts' => User::whereHas('roles', fn ($q) => $q->where('name', 'student'))->where('is_active', true)->count(),
                'active_classes' => ClassName::where('status', ClassName::STATUS_ACTIVE)->where('is_active', true)->count(),
                'planned_classes' => ClassName::where('status', ClassName::STATUS_PLANNED)->where('is_active', true)->count(),
                'enrollments_month' => $enrollmentSeries[$thisMonth],
                'enrollments_prev' => $enrollmentSeries[$lastMonth],
                'enrollments_prev_to_date' => Enrollment::query()
                    ->whereBetween('enrollment_date', [$prevStart->toDateString(), $prevEnd->toDateString()])->count(),
                'revenue_month' => $revenueSeries[$thisMonth],
                'revenue_prev' => $revenueSeries[$lastMonth],
                'revenue_prev_to_date' => $this->netRevenueBetween($prevStart, $prevEnd),
                'overdue_count' => (clone $overdue)->count(),
                'overdue_amount' => (int) (clone $overdue)->sum('amount'),
                'pending_enrollments' => Enrollment::query()->pending()->count(),
                'privacy_open' => $this->openPrivacyRequests(),
            ],
            'chart' => [
                'labels' => array_values($months),
                'enrollments' => array_values($enrollmentSeries),
                'revenue' => array_values($revenueSeries),
            ],
            'today' => $this->today($now),
            'recentUsers' => User::with('roles:id,name')->latest()->limit(6)->get(['id', 'name', 'email', 'profile_photo_path', 'is_active', 'created_at']),
            'pendingEnrollments' => Enrollment::query()->pending()
                ->with(['student:id,name,profile_photo_path', 'className:id,name'])
                ->latest()->limit(5)->get(),
            'nearCapacity' => $this->nearCapacity(),
        ];
    }

    /**
     * Last N months as "Y-m" => "Okt" (oldest first).
     *
     * @return array<string, string>
     */
    private function months(Carbon $now, int $count): array
    {
        $months = [];
        for ($i = $count - 1; $i >= 0; $i--) {
            $month = $now->copy()->startOfMonth()->subMonthsNoOverflow($i);
            $months[$month->format('Y-m')] = $month->locale('id')->translatedFormat('M');
        }

        return $months;
    }

    /**
     * New enrollments per month (by enrollment_date).
     *
     * @param  array<string, string>  $months
     * @return array<string, int>
     */
    private function enrollmentSeries(array $months): array
    {
        $series = array_fill_keys(array_keys($months), 0);
        $from = array_key_first($months).'-01';

        Enrollment::query()->where('enrollment_date', '>=', $from)->pluck('enrollment_date')
            ->each(function ($date) use (&$series) {
                $key = Carbon::parse($date)->format('Y-m');
                if (isset($series[$key])) {
                    $series[$key]++;
                }
            });

        return $series;
    }

    /**
     * Net settled revenue per month in rupiah.
     *
     * @param  array<string, string>  $months
     * @return array<string, int>
     */
    private function revenueSeries(array $months): array
    {
        $series = array_fill_keys(array_keys($months), 0);
        $tz = AdminClock::timezone();
        $from = Carbon::parse(array_key_first($months).'-01', $tz)->startOfMonth()->subDay()->utc();

        FinancialTransaction::query()->settled()
            ->where(fn ($q) => $q->where('paid_date', '>=', $from)
                ->orWhere(fn ($w) => $w->whereNull('paid_date')->where('transaction_date', '>=', $from)))
            ->get(['type', 'amount', 'paid_date', 'transaction_date'])
            ->each(function (FinancialTransaction $tx) use (&$series, $tz) {
                $date = $tx->paid_date ?? $tx->transaction_date;
                if (! $date) {
                    return;
                }
                $key = Carbon::parse($date)->setTimezone($tz)->format('Y-m');
                if (! isset($series[$key])) {
                    return;
                }
                $amount = (int) round((float) $tx->amount);
                $series[$key] += $tx->type === FinancialTransaction::TYPE_REFUND ? -$amount : $amount;
            });

        return $series;
    }

    /**
     * Net settled revenue between two business-time instants (inclusive).
     */
    private function netRevenueBetween(Carbon $from, Carbon $to): int
    {
        $fromUtc = $from->copy()->utc();
        $toUtc = $to->copy()->utc();

        return (int) FinancialTransaction::query()->settled()
            ->where(fn ($q) => $q->whereBetween('paid_date', [$fromUtc, $toUtc])
                ->orWhere(fn ($w) => $w->whereNull('paid_date')->whereBetween('transaction_date', [$fromUtc, $toUtc])))
            ->get(['type', 'amount'])
            ->sum(fn (FinancialTransaction $tx) => ($tx->type === FinancialTransaction::TYPE_REFUND ? -1 : 1) * (int) round((float) $tx->amount));
    }

    /**
     * Today's sessions grouped per class slot (class start/end dates honoured).
     *
     * @return Collection<int, \App\Services\Scheduling\ScheduleSlot>
     */
    private function today(Carbon $now): Collection
    {
        $rows = Schedule::query()->forCalendar()->onDay($now->dayOfWeekIso)
            ->with(['className:id,name,course_id,status,is_active,start_date,end_date', 'className.course:id,name',
                'teacherAssignment:id,teacher_id', 'teacherAssignment.teacher:id,name,profile_photo_path'])
            ->get();

        return ScheduleSlots::agenda($rows, $now, 1)[0]['slots'];
    }

    /**
     * Ongoing classes whose seats are at least NEAR_CAPACITY taken.
     *
     * @return Collection<int, ClassName>
     */
    private function nearCapacity(): Collection
    {
        return ClassName::query()->activeAndOngoing()->withSeatsTaken()->with('course:id,name')
            ->where('max_students', '>', 0)
            ->get()
            ->filter(fn (ClassName $c) => $c->seatsTaken() / max(1, $c->max_students) >= self::NEAR_CAPACITY)
            ->sortByDesc(fn (ClassName $c) => $c->seatsTaken() / max(1, $c->max_students))
            ->take(5)
            ->values();
    }

    /**
     * Open data-subject requests when the privacy module is installed (null otherwise).
     */
    private function openPrivacyRequests(): ?int
    {
        $model = 'App\\Models\\Privacy\\DataSubjectRequest';
        if (! Route::has('admin.privacy.dashboard') || ! class_exists($model)) {
            return null;
        }

        try {
            $instance = new $model();
            if (! Schema::hasTable($instance->getTable())) {
                return null;
            }
            $query = $model::query();

            // Reason: use the module's own definition of "open" when it has one.
            return method_exists($instance, 'scopeOpen')
                ? $query->open()->count()
                : $query->whereNotIn('status', ['completed', 'rejected', 'closed', 'cancelled', 'withdrawn'])->count();
        } catch (Throwable) {
            return null;
        }
    }
}
