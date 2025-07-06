@extends('layouts.app')

@section('title', 'Rewards Store')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Rewards Store</h1>
                <p class="text-gray-600 mt-2">Redeem your points for exciting rewards</p>
            </div>
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg px-6 py-3">
                <p class="text-sm">Your Points</p>
                <p class="text-2xl font-bold">{{ number_format($userPoints) }}</p>
            </div>
        </div>

        <!-- Category Filters -->
        <div class="mb-6">
            <div class="flex flex-wrap gap-2">
                <button onclick="filterRewards('all')" class="filter-btn active px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 text-white">
                    All Rewards
                </button>
                <button onclick="filterRewards('physical')" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300">
                    Physical Items
                </button>
                <button onclick="filterRewards('digital')" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300">
                    Digital Goods
                </button>
                <button onclick="filterRewards('privilege')" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300">
                    Privileges
                </button>
                <button onclick="filterRewards('discount')" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300">
                    Discounts
                </button>
            </div>
        </div>

        <!-- Rewards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($rewards as $reward)
            <div class="reward-item bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow" data-category="{{ $reward->category }}">
                @if($reward->image_path)
                <img src="{{ asset('storage/' . $reward->image_path) }}" 
                     alt="{{ $reward->name }}" 
                     class="w-full h-48 object-cover rounded-t-lg">
                @else
                <div class="w-full h-48 bg-gradient-to-br from-gray-200 to-gray-300 rounded-t-lg flex items-center justify-center">
                    <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                    </svg>
                </div>
                @endif
                
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $reward->name }}</h3>
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $reward->category == 'physical' ? 'bg-blue-100 text-blue-700' : ($reward->category == 'digital' ? 'bg-green-100 text-green-700' : ($reward->category == 'privilege' ? 'bg-purple-100 text-purple-700' : 'bg-yellow-100 text-yellow-700')) }}">
                            {{ ucfirst($reward->category) }}
                        </span>
                    </div>
                    
                    <p class="text-gray-600 text-sm mb-4">{{ $reward->description }}</p>
                    
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($reward->points_cost) }}</p>
                            <p class="text-xs text-gray-500">points</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Available</p>
                            <p class="text-lg font-semibold {{ $reward->quantity_available > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $reward->quantity_available > 0 ? $reward->quantity_available : 'Out of Stock' }}
                            </p>
                        </div>
                    </div>
                    
                    @if($userPoints >= $reward->points_cost && $reward->quantity_available > 0)
                        <form action="{{ route('gamification.rewards.redeem', $reward->id) }}" method="POST" onsubmit="return confirmRedeem('{{ $reward->name }}', {{ $reward->points_cost }})">
                            @csrf
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                                Redeem Now
                            </button>
                        </form>
                    @elseif($reward->quantity_available == 0)
                        <button disabled class="w-full bg-gray-300 text-gray-500 py-2 px-4 rounded-lg cursor-not-allowed">
                            Out of Stock
                        </button>
                    @else
                        <button disabled class="w-full bg-gray-300 text-gray-500 py-2 px-4 rounded-lg cursor-not-allowed">
                            Need {{ number_format($reward->points_cost - $userPoints) }} more points
                        </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        @if($rewards->isEmpty())
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No rewards available</h3>
            <p class="mt-1 text-sm text-gray-500">Check back later for new rewards!</p>
        </div>
        @endif

        <!-- Links -->
        <div class="mt-8 flex justify-center space-x-6">
            <a href="{{ route('gamification.redemptions') }}" class="text-blue-600 hover:text-blue-800">
                View My Redemptions
            </a>
            <a href="{{ route('gamification.index') }}" class="text-blue-600 hover:text-blue-800">
                Back to Dashboard
            </a>
        </div>
    </div>
</div>

<script>
function filterRewards(category) {
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-blue-600', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700');
    });
    event.target.classList.remove('bg-gray-200', 'text-gray-700');
    event.target.classList.add('active', 'bg-blue-600', 'text-white');
    
    // Filter rewards
    const rewards = document.querySelectorAll('.reward-item');
    rewards.forEach(reward => {
        if (category === 'all' || reward.dataset.category === category) {
            reward.style.display = 'block';
        } else {
            reward.style.display = 'none';
        }
    });
}

function confirmRedeem(rewardName, pointsCost) {
    return confirm(`Are you sure you want to redeem "${rewardName}" for ${pointsCost.toLocaleString()} points?`);
}
</script>
@endsection