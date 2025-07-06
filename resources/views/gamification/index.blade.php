@extends('layouts.app')

@section('title', 'My Progress & Rewards')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- User Stats Summary -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}'s Progress</h2>
                <span class="text-sm text-gray-500">Rank #{{ $rank['rank'] ?? 'N/A' }}</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Current Points -->
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-blue-600 font-medium">Current Points</p>
                            <p class="text-2xl font-bold text-blue-900">{{ number_format($points->points) }}</p>
                        </div>
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Level -->
                <div class="bg-purple-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-purple-600 font-medium">Level</p>
                            <p class="text-2xl font-bold text-purple-900">{{ $points->level }}</p>
                            <div class="mt-2">
                                <div class="w-full bg-purple-200 rounded-full h-2">
                                    <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $points->level_progress }}%"></div>
                                </div>
                                <p class="text-xs text-purple-600 mt-1">{{ $points->points_for_next_level }} pts to next level</p>
                            </div>
                        </div>
                        <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Current Streak -->
                <div class="bg-orange-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-orange-600 font-medium">Current Streak</p>
                            <p class="text-2xl font-bold text-orange-900">{{ $points->current_streak }} days</p>
                            <p class="text-xs text-orange-600">Best: {{ $points->longest_streak }} days</p>
                        </div>
                        <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Badges Earned -->
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-green-600 font-medium">Badges Earned</p>
                            <p class="text-2xl font-bold text-green-900">{{ $badges->count() }}</p>
                            <a href="{{ route('gamification.badges') }}" class="text-xs text-green-600 hover:text-green-700">View all →</a>
                        </div>
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Activity -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                    
                    @if($recentTransactions->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentTransactions as $transaction)
                                <div class="flex items-center justify-between py-3 border-b last:border-0">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-full {{ $transaction->points > 0 ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                                            <span class="text-lg {{ $transaction->points > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $transaction->points > 0 ? '+' : '' }}{{ $transaction->points }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $transaction->formatted_description }}</p>
                                            <p class="text-sm text-gray-500">{{ $transaction->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4">
                            <a href="{{ route('gamification.point-history') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                View all history →
                            </a>
                        </div>
                    @else
                        <p class="text-gray-500">No activity yet. Start earning points!</p>
                    @endif
                </div>
            </div>
            
            <!-- Weekly Leaderboard -->
            <div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Weekly Leaders</h3>
                        <a href="{{ route('gamification.leaderboard') }}" class="text-sm text-blue-600 hover:text-blue-700">
                            View all →
                        </a>
                    </div>
                    
                    @if($leaderboard->count() > 0)
                        <div class="space-y-3">
                            @foreach($leaderboard as $index => $entry)
                                <div class="flex items-center space-x-3 {{ $entry->user_id === $user->id ? 'bg-blue-50 -mx-2 px-2 py-1 rounded' : '' }}">
                                    <div class="w-8 h-8 rounded-full {{ $index === 0 ? 'bg-yellow-400' : ($index === 1 ? 'bg-gray-300' : ($index === 2 ? 'bg-orange-400' : 'bg-gray-100')) }} flex items-center justify-center">
                                        <span class="text-sm font-bold {{ $index < 3 ? 'text-white' : 'text-gray-600' }}">{{ $index + 1 }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 {{ $entry->user_id === $user->id ? 'text-blue-700' : '' }}">
                                            {{ $entry->user->name }}
                                            @if($entry->user_id === $user->id)
                                                <span class="text-xs text-blue-600">(You)</span>
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-500">Level {{ $entry->level }} • {{ number_format($entry->total_earned) }} pts</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No leaderboard data yet.</p>
                    @endif
                </div>
                
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('gamification.badges') }}" class="block w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-center">
                            View Badges
                        </a>
                        <a href="{{ route('gamification.rewards') }}" class="block w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-center">
                            Browse Rewards
                        </a>
                        <a href="{{ route('gamification.redemptions') }}" class="block w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-center">
                            My Redemptions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection