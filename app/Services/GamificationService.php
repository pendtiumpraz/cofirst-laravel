<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;
use App\Models\PointTransaction;
use App\Models\UserPoint;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    /**
     * Award points for daily login
     */
    public function awardDailyLoginPoints(User $user)
    {
        $user->initializePoints();
        // Load the points relationship to ensure we have the latest data
        $user->load('points');
        $points = $user->points;
        
        // Check if already awarded today
        $alreadyAwarded = $user->pointTransactions()
            ->where('reason', PointTransaction::REASON_LOGIN)
            ->whereDate('created_at', Carbon::today())
            ->exists();
            
        if ($alreadyAwarded) {
            return false;
        }
        
        // Award login points
        $basePoints = PointTransaction::POINTS[PointTransaction::REASON_LOGIN];
        $points->addPoints($basePoints, PointTransaction::REASON_LOGIN);
        
        // Check for streak bonuses
        $this->checkStreakBonus($user);
        
        // Check for login-related badges
        $this->checkLoginBadges($user);
        
        return true;
    }
    
    /**
     * Check and award streak bonuses
     */
    protected function checkStreakBonus(User $user)
    {
        $streak = $user->points->current_streak;
        $streakBonuses = PointTransaction::POINTS[PointTransaction::REASON_STREAK_BONUS];
        
        foreach ($streakBonuses as $days => $points) {
            if ($streak == $days) {
                $user->points->addPoints(
                    $points,
                    PointTransaction::REASON_STREAK_BONUS,
                    "{$days} day login streak bonus"
                );
                break;
            }
        }
    }
    
    /**
     * Award points for completing assignment
     */
    public function awardAssignmentPoints(User $user, $assignment, $score = null)
    {
        $user->initializePoints();
        
        $points = PointTransaction::POINTS[PointTransaction::REASON_ASSIGNMENT_COMPLETE];
        $description = "Completed assignment: {$assignment->title}";
        
        $user->points->addPoints(
            $points,
            PointTransaction::REASON_ASSIGNMENT_COMPLETE,
            $description,
            $assignment
        );
        
        // Check for assignment-related badges
        $this->checkAcademicBadges($user);
    }
    
    /**
     * Award points for quiz completion
     */
    public function awardQuizPoints(User $user, $quiz, $score)
    {
        $user->initializePoints();
        
        // Base points for completion
        $points = PointTransaction::POINTS[PointTransaction::REASON_QUIZ_COMPLETE];
        $description = "Completed quiz: {$quiz->title} (Score: {$score}%)";
        
        $user->points->addPoints(
            $points,
            PointTransaction::REASON_QUIZ_COMPLETE,
            $description,
            $quiz
        );
        
        // Bonus for perfect score
        if ($score >= 100) {
            $perfectPoints = PointTransaction::POINTS[PointTransaction::REASON_QUIZ_PERFECT];
            $user->points->addPoints(
                $perfectPoints,
                PointTransaction::REASON_QUIZ_PERFECT,
                "Perfect score on quiz: {$quiz->title}",
                $quiz
            );
        }
        
        // Check for quiz-related badges
        $this->checkAcademicBadges($user);
    }
    
    /**
     * Award points for class attendance
     */
    public function awardAttendancePoints(User $user, $class)
    {
        $user->initializePoints();
        
        $points = PointTransaction::POINTS[PointTransaction::REASON_ATTENDANCE];
        $description = "Attended class: {$class->name}";
        
        $user->points->addPoints(
            $points,
            PointTransaction::REASON_ATTENDANCE,
            $description,
            $class
        );
        
        // Check for attendance-related badges
        $this->checkAttendanceBadges($user);
    }
    
    /**
     * Check and award login-related badges
     */
    protected function checkLoginBadges(User $user)
    {
        $points = $user->points;
        
        // First Login badge
        $firstLoginBadge = Badge::where('slug', 'first-login')->first();
        if ($firstLoginBadge && !$firstLoginBadge->isEarnedBy($user)) {
            $firstLoginBadge->awardTo($user, ['first_login_date' => now()]);
        }
        
        // Streak badges
        $streakBadges = [
            7 => 'week-warrior',
            30 => 'dedicated-learner',
            100 => 'century-streak',
        ];
        
        foreach ($streakBadges as $days => $slug) {
            if ($points->current_streak >= $days) {
                $badge = Badge::where('slug', $slug)->first();
                if ($badge && !$badge->isEarnedBy($user)) {
                    $badge->awardTo($user, ['streak_days' => $days]);
                }
            }
        }
    }
    
    /**
     * Check and award academic badges
     */
    protected function checkAcademicBadges(User $user)
    {
        // Assignment badges
        $assignmentCount = $user->pointTransactions()
            ->where('reason', PointTransaction::REASON_ASSIGNMENT_COMPLETE)
            ->count();
            
        $assignmentBadges = [
            5 => 'assignment-starter',
            25 => 'assignment-achiever',
            100 => 'assignment-master',
        ];
        
        foreach ($assignmentBadges as $count => $slug) {
            if ($assignmentCount >= $count) {
                $badge = Badge::where('slug', $slug)->first();
                if ($badge && !$badge->isEarnedBy($user)) {
                    $badge->awardTo($user, ['assignments_completed' => $count]);
                }
            }
        }
        
        // Perfect quiz badges
        $perfectQuizCount = $user->pointTransactions()
            ->where('reason', PointTransaction::REASON_QUIZ_PERFECT)
            ->count();
            
        if ($perfectQuizCount >= 5) {
            $badge = Badge::where('slug', 'perfectionist')->first();
            if ($badge && !$badge->isEarnedBy($user)) {
                $badge->awardTo($user, ['perfect_quizzes' => $perfectQuizCount]);
            }
        }
    }
    
    /**
     * Check and award attendance badges
     */
    protected function checkAttendanceBadges(User $user)
    {
        $attendanceCount = $user->pointTransactions()
            ->where('reason', PointTransaction::REASON_ATTENDANCE)
            ->count();
            
        $attendanceBadges = [
            10 => 'regular-attendee',
            50 => 'punctual-student',
            100 => 'perfect-attendance',
        ];
        
        foreach ($attendanceBadges as $count => $slug) {
            if ($attendanceCount >= $count) {
                $badge = Badge::where('slug', $slug)->first();
                if ($badge && !$badge->isEarnedBy($user)) {
                    $badge->awardTo($user, ['classes_attended' => $count]);
                }
            }
        }
    }
    
    /**
     * Get leaderboard data
     */
    public function getLeaderboard($type = 'all', $limit = 10, $roles = null)
    {
        switch ($type) {
            case 'weekly':
                // Get points earned this week using subquery
                $weeklyPoints = DB::table('point_transactions')
                    ->select('user_id', DB::raw('SUM(points) as weekly_points'))
                    ->where('created_at', '>=', Carbon::now()->startOfWeek())
                    ->where('type', 'earned')
                    ->groupBy('user_id');
                    
                $query = UserPoint::with(['user'])
                    ->joinSub($weeklyPoints, 'weekly', function ($join) {
                        $join->on('user_points.user_id', '=', 'weekly.user_id');
                    })
                    ->whereHas('user', function ($q) use ($roles) {
                        $q->where('is_active', true);
                        if ($roles) {
                            $q->whereHas('roles', function ($roleQuery) use ($roles) {
                                $roleQuery->whereIn('name', is_array($roles) ? $roles : [$roles]);
                            });
                        }
                    })
                    ->orderBy('weekly.weekly_points', 'desc')
                    ->limit($limit);
                    
                return $query->get();
                    
            case 'monthly':
                // Get points earned this month using subquery
                $monthlyPoints = DB::table('point_transactions')
                    ->select('user_id', DB::raw('SUM(points) as monthly_points'))
                    ->where('created_at', '>=', Carbon::now()->startOfMonth())
                    ->where('type', 'earned')
                    ->groupBy('user_id');
                    
                $query = UserPoint::with(['user'])
                    ->joinSub($monthlyPoints, 'monthly', function ($join) {
                        $join->on('user_points.user_id', '=', 'monthly.user_id');
                    })
                    ->whereHas('user', function ($q) use ($roles) {
                        $q->where('is_active', true);
                        if ($roles) {
                            $q->whereHas('roles', function ($roleQuery) use ($roles) {
                                $roleQuery->whereIn('name', is_array($roles) ? $roles : [$roles]);
                            });
                        }
                    })
                    ->orderBy('monthly.monthly_points', 'desc')
                    ->limit($limit);
                    
                return $query->get();
                    
            default:
                // All time - order by total earned
                $query = UserPoint::with(['user'])
                    ->whereHas('user', function ($q) use ($roles) {
                        $q->where('is_active', true);
                        if ($roles) {
                            $q->whereHas('roles', function ($roleQuery) use ($roles) {
                                $roleQuery->whereIn('name', is_array($roles) ? $roles : [$roles]);
                            });
                        }
                    })
                    ->orderBy('total_earned', 'desc')
                    ->limit($limit);
                    
                return $query->get();
        }
    }
    
    /**
     * Get user's rank
     */
    public function getUserRank(User $user)
    {
        if (!$user->points) {
            return null;
        }
        
        $rank = UserPoint::where('total_earned', '>', $user->points->total_earned)->count() + 1;
        
        return [
            'rank' => $rank,
            'points' => $user->points->total_earned
        ];
    }
}