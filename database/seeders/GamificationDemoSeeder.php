<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Badge;
use App\Models\ClassProgress;
use App\Models\MaterialAccess;
use App\Models\PointTransaction;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\UserPoint;
use Database\Seeders\Data\DemoContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Points, badges and reward redemptions for the demo students, derived from
 * their seeded activity: +10 per attended meeting, +5 per login day, badge points,
 * minus the points spent on redemptions. user_points always equals the sum of
 * the student's point_transactions. Demo rows are rebuilt on every run.
 */
class GamificationDemoSeeder extends Seeder
{
    /**
     * Redemptions: student key => [reward name, status].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const REDEMPTIONS = [
        'raka' => ['Set Buku Catatan', RewardRedemption::STATUS_APPROVED],
        'kevin' => ['Desain Sertifikat Khusus', RewardRedemption::STATUS_PENDING],
        'alya' => ['Tambahan Waktu Tugas', RewardRedemption::STATUS_PENDING],
        'eko' => ['Tambahan Waktu Tugas', RewardRedemption::STATUS_DELIVERED],
        'salsa' => ['Set Buku Catatan', RewardRedemption::STATUS_PENDING],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studentIds = DemoContext::studentIds();
        DB::table('point_transactions')->whereIn('user_id', $studentIds)->delete();
        DB::table('user_badges')->whereIn('user_id', $studentIds)->delete();

        $badges = Badge::pluck('id', 'slug');
        $adminId = DemoContext::user('admin')->id;
        $today = Carbon::today();

        foreach (DemoContext::studentKeys() as $key) {
            $user = DemoContext::user($key);
            $transactions = [];
            $userBadges = [];
            $loginDays = 5 + DemoContext::pick('login:'.$key, 10);

            for ($d = $loginDays; $d >= 1; $d--) {
                $transactions[] = $this->tx($user->id, 5, PointTransaction::TYPE_EARNED, PointTransaction::REASON_LOGIN, 'Bonus login harian', $today->copy()->subDays($d)->setTime(7, 30));
            }

            $attended = Attendance::where('student_id', $user->id)->attended()->with('className:id,name')->orderBy('attendance_date')->get();

            foreach ($attended as $row) {
                $transactions[] = $this->tx($user->id, 10, PointTransaction::TYPE_EARNED, PointTransaction::REASON_ATTENDANCE, "Hadir: {$row->className?->name} pertemuan {$row->meeting_number}", $row->attendance_date->copy()->setTime(12, 0));
            }

            $earnedBadges = ['first-login' => $today->copy()->subDays($loginDays)];

            if (ClassProgress::where('student_id', $user->id)->where('status', 'completed')->exists()) {
                $earnedBadges['first-assignment'] = $attended->first()?->attendance_date ?? $today->copy()->subDays($loginDays);
            }

            if ($attended->count() >= 5) {
                $earnedBadges['early-bird'] = $attended[4]->attendance_date;
            }

            if (MaterialAccess::where('student_id', $user->id)->distinct()->count('material_id') >= 20) {
                $earnedBadges['reading-champion'] = $today->copy()->subDays(3);
            }

            foreach ($earnedBadges as $slug => $date) {
                if (! isset($badges[$slug])) {
                    continue;
                }

                $badge = Badge::find($badges[$slug]);
                $at = Carbon::parse($date)->setTime(13, 0);
                $userBadges[] = ['user_id' => $user->id, 'badge_id' => $badge->id, 'earned_at' => $at, 'earned_for' => null, 'is_featured' => $slug !== 'first-login', 'created_at' => $at, 'updated_at' => $at];
                $transactions[] = $this->tx($user->id, $badge->points_required, PointTransaction::TYPE_EARNED, PointTransaction::REASON_BADGE_EARNED, "Earned badge: {$badge->name}", $at);
            }

            $earned = array_sum(array_column($transactions, 'points'));
            $spent = $this->redeem($key, $user->id, $earned, $adminId, $transactions);

            DB::table('point_transactions')->insert($transactions);
            DB::table('user_badges')->insert($userBadges);

            UserPoint::updateOrCreate(['user_id' => $user->id], [
                'points' => $earned - $spent,
                'total_earned' => $earned,
                'total_spent' => $spent,
                'level' => $this->level($earned),
                'current_streak' => $loginDays,
                'longest_streak' => $loginDays + DemoContext::pick('streak:'.$key, 6),
                'last_activity_date' => $today->copy()->subDay()->toDateString(),
            ]);
        }

        // Reason: stock counters must match the redemptions that exist.
        Reward::whereNotNull('quantity_available')->get()->each(fn (Reward $reward) => $reward->update([
            'quantity_redeemed' => $reward->redemptions()->where('status', '!=', RewardRedemption::STATUS_CANCELLED)->count(),
        ]));

        $this->command?->info('Demo gamification: points, badges and '.count(self::REDEMPTIONS).' redemptions.');
    }

    /**
     * Create the student's redemption (when affordable) and its spent transaction.
     *
     * Returns:
     *     int: Points spent.
     */
    private function redeem(string $key, int $userId, int $earned, int $adminId, array &$transactions): int
    {
        if (! isset(self::REDEMPTIONS[$key])) {
            return 0;
        }

        [$rewardName, $status] = self::REDEMPTIONS[$key];
        $reward = Reward::where('name', $rewardName)->first();

        if (! $reward || $reward->points_cost > $earned) {
            return 0;
        }

        $at = Carbon::today()->subDays(4)->setTime(16, 0);
        $processed = $status !== RewardRedemption::STATUS_PENDING;

        RewardRedemption::updateOrCreate(['user_id' => $userId, 'reward_id' => $reward->id], [
            'points_spent' => $reward->points_cost,
            'status' => $status,
            'notes' => $processed ? 'Diproses oleh admin.' : null,
            'redeemed_at' => $at,
            'processed_at' => $processed ? $at->copy()->addDay() : null,
            'processed_by' => $processed ? $adminId : null,
        ]);

        $transactions[] = $this->tx($userId, -$reward->points_cost, PointTransaction::TYPE_SPENT, PointTransaction::REASON_REWARD_REDEMPTION, "Redeemed: {$reward->name}", $at);

        return $reward->points_cost;
    }

    /**
     * Level reached for a total of earned points.
     */
    private function level(int $earned): int
    {
        $level = 1;

        foreach (UserPoint::LEVEL_POINTS as $candidate => $required) {
            if ($earned >= $required) {
                $level = $candidate;
            }
        }

        return $level;
    }

    /**
     * One point_transactions row.
     */
    private function tx(int $userId, int $points, string $type, string $reason, string $description, Carbon $at): array
    {
        return [
            'user_id' => $userId, 'points' => $points, 'type' => $type, 'reason' => $reason, 'description' => $description,
            'related_type' => null, 'related_id' => null, 'metadata' => null, 'created_at' => $at, 'updated_at' => $at,
        ];
    }
}
