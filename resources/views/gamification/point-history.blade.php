@extends('layouts.app')

@section('title', 'Point History')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Point History</h1>
            <p class="text-gray-600 mt-2">Track your point earnings and spending</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Current Balance</p>
                        <p class="text-xl font-semibold text-gray-900">{{ number_format($stats['current_balance']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Earned</p>
                        <p class="text-xl font-semibold text-gray-900">{{ number_format($stats['total_earned']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Spent</p>
                        <p class="text-xl font-semibold text-gray-900">{{ number_format($stats['total_spent']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Current Level</p>
                        <p class="text-xl font-semibold text-gray-900">{{ $stats['current_level'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Transaction History</h2>
            </div>
            
            @if($transactions->count() > 0)
            <div class="table-wrapper">
                <table class="min-w-full" data-enhance="true" data-searchable="true" data-sortable="true" data-show-no="true">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Points
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Balance
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transaction->created_at->format('M d, Y') }}
                            <br>
                            <span class="text-xs text-gray-400">{{ $transaction->created_at->format('h:i A') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $transaction->description }}</div>
                            @if($transaction->metadata)
                                <div class="text-xs text-gray-500 mt-1">
                                    @if(isset($transaction->metadata['badge_name']))
                                        Badge: {{ $transaction->metadata['badge_name'] }}
                                    @elseif(isset($transaction->metadata['reward_name']))
                                        Reward: {{ $transaction->metadata['reward_name'] }}
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $transaction->type == 'earned' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                            <span class="{{ $transaction->type == 'earned' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->type == 'earned' ? '+' : '-' }}{{ number_format($transaction->points) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                            {{ number_format($transaction->balance_after) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $transactions->links() }}
            </div>
            @else
            <div class="px-6 py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No transactions yet</h3>
                <p class="mt-1 text-sm text-gray-500">Start earning points by completing activities!</p>
            </div>
            @endif
        </div>

        <!-- Back to Dashboard -->
        <div class="mt-6 text-center">
            <a href="{{ route('gamification.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Back to Gamification Dashboard
            </a>
        </div>
    </div>
</div>
@endsection