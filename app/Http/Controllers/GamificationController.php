<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GamificationService;
use App\Models\Badge;
use App\Models\Reward;
use App\Models\PointTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GamificationController extends Controller
{
    protected $gamificationService;
    
    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }
    
    /**
     * Display user's gamification dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $user->initializePoints();
        
        $points = $user->points;
        $recentTransactions = $user->pointTransactions()
            ->latest()
            ->limit(10)
            ->get();
            
        $badges = $user->badges()->get();
        $availableBadges = Badge::active()
            ->whereNotIn('id', $badges->pluck('id'))
            ->orderBy('category')
            ->orderBy('level')
            ->get();
            
        $rank = $this->gamificationService->getUserRank($user);
        $leaderboard = $this->gamificationService->getLeaderboard('weekly', 5);
        
        return view('gamification.index', compact(
            'user',
            'points',
            'recentTransactions',
            'badges',
            'availableBadges',
            'rank',
            'leaderboard'
        ));
    }
    
    /**
     * Display leaderboard
     */
    public function leaderboard(Request $request)
    {
        $type = $request->get('type', 'all');
        $leaderboard = $this->gamificationService->getLeaderboard($type, 50);
        
        $user = Auth::user();
        $userRank = $this->gamificationService->getUserRank($user);
        
        return view('gamification.leaderboard', compact('leaderboard', 'type', 'userRank'));
    }
    
    /**
     * Display badges page
     */
    public function badges()
    {
        $user = Auth::user();
        $earnedBadges = $user->badges()->get();
        $allBadges = Badge::active()
            ->orderBy('category')
            ->orderBy('level')
            ->get()
            ->groupBy('category');
            
        return view('gamification.badges', compact('earnedBadges', 'allBadges'));
    }
    
    /**
     * Toggle badge featured status
     */
    public function toggleBadgeFeatured(Badge $badge)
    {
        $user = Auth::user();
        $userBadge = $user->badges()->where('badge_id', $badge->id)->first();
        
        if (!$userBadge) {
            return back()->with('error', 'You have not earned this badge yet.');
        }
        
        // Limit featured badges to 5
        if (!$userBadge->pivot->is_featured && $user->featuredBadges()->count() >= 5) {
            return back()->with('error', 'You can only feature up to 5 badges.');
        }
        
        $userBadge->pivot->is_featured = !$userBadge->pivot->is_featured;
        $userBadge->pivot->save();
        
        return back()->with('success', 'Badge featured status updated.');
    }
    
    /**
     * Display rewards catalog
     */
    public function rewards()
    {
        $user = Auth::user();
        $user->initializePoints();
        
        $rewards = Reward::available()
            ->orderBy('points_cost')
            ->get();
            
        $userPoints = $user->points->points;
        
        return view('gamification.rewards', compact('rewards', 'userPoints'));
    }
    
    /**
     * Redeem a reward
     */
    public function redeemReward(Reward $reward)
    {
        $user = Auth::user();
        
        try {
            $redemption = $reward->redeemFor($user);
            
            return redirect()->route('gamification.rewards')
                ->with('success', "Successfully redeemed {$reward->name}! Your redemption is being processed.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * View user's redemption history
     */
    public function redemptions()
    {
        $user = Auth::user();
        $redemptions = $user->rewardRedemptions()
            ->with('reward')
            ->latest()
            ->paginate(10);
            
        return view('gamification.redemptions', compact('redemptions'));
    }
    
    /**
     * Point history
     */
    public function pointHistory()
    {
        $user = Auth::user();
        $transactions = $user->pointTransactions()
            ->latest()
            ->paginate(20);
            
        $stats = [
            'total_earned' => $user->points->total_earned ?? 0,
            'total_spent' => $user->points->total_spent ?? 0,
            'current_balance' => $user->points->points ?? 0,
            'current_level' => $user->points->level ?? 1,
        ];
        
        return view('gamification.point-history', compact('transactions', 'stats'));
    }
}