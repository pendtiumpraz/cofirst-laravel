<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\GamificationService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AwardLoginPoints
{
    protected $gamificationService;
    
    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Only check once per session
            $sessionKey = 'login_points_awarded_' . $user->id . '_' . Carbon::today()->format('Y-m-d');
            
            if (!session($sessionKey)) {
                // Award daily login points
                $this->gamificationService->awardDailyLoginPoints($user);
                
                // Mark as awarded for this session
                session([$sessionKey => true]);
            }
        }
        
        return $next($request);
    }
}