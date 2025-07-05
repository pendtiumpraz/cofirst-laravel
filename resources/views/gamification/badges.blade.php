@extends('layouts.app')

@section('title', 'My Badges')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Badge Collection</h1>
            <p class="text-gray-600 mt-2">Unlock achievements and showcase your progress</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Badges Earned</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $earnedBadges->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Featured Badges</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $earnedBadges->where('pivot.is_featured', true)->count() }} / 5</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Completion Rate</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ $allBadges->flatten()->count() > 0 ? round(($earnedBadges->count() / $allBadges->flatten()->count()) * 100) : 0 }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earned Badges -->
        @if($earnedBadges->count() > 0)
        <div class="mb-12">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Your Badges</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($earnedBadges as $userBadge)
                <div class="relative group">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 text-center">
                        <div class="relative">
                            <img src="{{ asset($userBadge->image_path ?? 'images/badges/default.png') }}" 
                                 alt="{{ $userBadge->name }}" 
                                 class="w-20 h-20 mx-auto mb-2">
                            @if($userBadge->pivot->is_featured)
                            <div class="absolute -top-1 -right-1">
                                <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            @endif
                        </div>
                        <h3 class="text-sm font-medium text-gray-900">{{ $userBadge->name }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ ucfirst($userBadge->level) }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $userBadge->pivot->earned_at->format('M d, Y') }}</p>
                        
                        <!-- Toggle Featured Button -->
                        <form action="{{ route('gamification.badges.toggle-featured', $userBadge->id) }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" 
                                    class="text-xs px-2 py-1 rounded {{ $userBadge->pivot->is_featured ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600' }} hover:opacity-80">
                                {{ $userBadge->pivot->is_featured ? 'Featured' : 'Feature' }}
                            </button>
                        </form>
                    </div>
                    
                    <!-- Tooltip -->
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 w-48">
                        <p>{{ $userBadge->description }}</p>
                        <p class="mt-1 text-gray-300">{{ $userBadge->criteria }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- All Badges by Category -->
        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">All Badges</h2>
            
            @foreach($allBadges as $category => $badges)
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-700 mb-3 capitalize">{{ str_replace('_', ' ', $category) }}</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($badges as $badge)
                    <div class="relative group">
                        <div class="bg-white rounded-lg shadow-sm p-4 text-center {{ $earnedBadges->contains('id', $badge->id) ? '' : 'opacity-50' }}">
                            <img src="{{ asset($badge->image_path ?? 'images/badges/default.png') }}" 
                                 alt="{{ $badge->name }}" 
                                 class="w-20 h-20 mx-auto mb-2 {{ $earnedBadges->contains('id', $badge->id) ? '' : 'grayscale' }}">
                            <h3 class="text-sm font-medium text-gray-900">{{ $badge->name }}</h3>
                            <p class="text-xs text-gray-500 mt-1">{{ ucfirst($badge->level) }}</p>
                            @if($earnedBadges->contains('id', $badge->id))
                                <p class="text-xs text-green-600 mt-1">✓ Earned</p>
                            @else
                                <p class="text-xs text-gray-400 mt-1">Locked</p>
                            @endif
                        </div>
                        
                        <!-- Tooltip -->
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 w-48">
                            <p>{{ $badge->description }}</p>
                            <p class="mt-1 text-gray-300">{{ $badge->criteria }}</p>
                            <p class="mt-1 text-yellow-300">+{{ $badge->points }} points</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <!-- Back to Dashboard -->
        <div class="mt-8 text-center">
            <a href="{{ route('gamification.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Back to Gamification Dashboard
            </a>
        </div>
    </div>
</div>
@endsection