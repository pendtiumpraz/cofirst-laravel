@extends('layouts.app')

@section('title', 'Leaderboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Leaderboard</h1>
            <p class="text-gray-600 mt-2">See how you rank against other learners</p>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow mb-6">
            <nav class="flex border-b border-gray-200" aria-label="Tabs">
                <a href="{{ route('gamification.leaderboard', ['type' => 'all']) }}" 
                   class="px-6 py-3 border-b-2 font-medium text-sm {{ $type == 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    All Time
                </a>
                <a href="{{ route('gamification.leaderboard', ['type' => 'monthly']) }}" 
                   class="px-6 py-3 border-b-2 font-medium text-sm {{ $type == 'monthly' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    This Month
                </a>
                <a href="{{ route('gamification.leaderboard', ['type' => 'weekly']) }}" 
                   class="px-6 py-3 border-b-2 font-medium text-sm {{ $type == 'weekly' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    This Week
                </a>
            </nav>
        </div>

        <!-- Your Rank Card -->
        @if($userRank)
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg p-6 mb-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">Your Rank</h3>
                    <p class="text-3xl font-bold mt-2">#{{ $userRank['rank'] }}</p>
                    <p class="text-sm opacity-90 mt-1">{{ number_format($userRank['points']) }} points</p>
                </div>
                <div class="text-6xl opacity-20">
                    <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
            </div>
        </div>
        @endif

        @if(isset($studentLeaderboard) && isset($teacherLeaderboard))
        <!-- Admin/SuperAdmin View: Separate Student and Teacher Leaderboards -->
        <div class="space-y-8">
            <!-- Student Leaderboard -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-blue-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-blue-900">Student Leaderboard</h3>
                </div>
                @include('gamification.partials.leaderboard-table', ['leaderboard' => $studentLeaderboard, 'type' => $type])
            </div>

            <!-- Teacher Leaderboard -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-green-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-green-900">Teacher Leaderboard</h3>
                </div>
                @include('gamification.partials.leaderboard-table', ['leaderboard' => $teacherLeaderboard, 'type' => $type])
            </div>
        </div>
        @else
        <!-- Single Leaderboard View -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @include('gamification.partials.leaderboard-table', ['leaderboard' => $leaderboard, 'type' => $type])
        </div>
        @endif

        <!-- Back to Dashboard -->
        <div class="mt-6 text-center">
            <a href="{{ route('gamification.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Back to Gamification Dashboard
            </a>
        </div>
    </div>
</div>
@endsection