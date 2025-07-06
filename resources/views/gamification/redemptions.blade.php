@extends('layouts.app')

@section('title', 'My Redemptions')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Redemptions</h1>
            <p class="text-gray-600 mt-2">Track your reward redemption history</p>
        </div>

        <!-- Redemptions List -->
        @if($redemptions->count() > 0)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reward
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Points
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Code
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($redemptions as $redemption)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($redemption->reward->image_path)
                                    <img src="{{ asset('storage/' . $redemption->reward->image_path) }}" 
                                         alt="{{ $redemption->reward->name }}" 
                                         class="h-10 w-10 rounded-lg object-cover">
                                @else
                                    <div class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $redemption->reward->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ ucfirst($redemption->reward->category) }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ number_format($redemption->points_spent) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $redemption->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($redemption->status == 'processing' ? 'bg-blue-100 text-blue-800' : 
                                   ($redemption->status == 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($redemption->status == 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'))) }}">
                                {{ ucfirst($redemption->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $redemption->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($redemption->redemption_code)
                                <div class="flex items-center">
                                    <code class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">{{ $redemption->redemption_code }}</code>
                                    <button onclick="copyCode('{{ $redemption->redemption_code }}')" 
                                            class="ml-2 text-gray-400 hover:text-gray-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $redemptions->links() }}
        </div>

        <!-- Notes -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-900 mb-2">Redemption Information</h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• <strong>Pending:</strong> Your redemption is awaiting approval</li>
                <li>• <strong>Processing:</strong> Your reward is being prepared</li>
                <li>• <strong>Completed:</strong> Your reward has been delivered or is ready for collection</li>
                <li>• <strong>Cancelled:</strong> The redemption was cancelled (points refunded)</li>
            </ul>
        </div>
        @else
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No redemptions yet</h3>
            <p class="mt-1 text-sm text-gray-500">Visit the rewards store to redeem your points!</p>
            <div class="mt-6">
                <a href="{{ route('gamification.rewards') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                    Browse Rewards
                </a>
            </div>
        </div>
        @endif

        <!-- Back Links -->
        <div class="mt-8 flex justify-center space-x-6">
            <a href="{{ route('gamification.rewards') }}" class="text-blue-600 hover:text-blue-800">
                ← Back to Rewards Store
            </a>
            <a href="{{ route('gamification.index') }}" class="text-blue-600 hover:text-blue-800">
                Back to Dashboard
            </a>
        </div>
    </div>
</div>

<script>
function copyCode(code) {
    navigator.clipboard.writeText(code).then(function() {
        // Show temporary success message
        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;
        button.innerHTML = '<svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
        
        setTimeout(() => {
            button.innerHTML = originalHtml;
        }, 2000);
    });
}
</script>
@endsection