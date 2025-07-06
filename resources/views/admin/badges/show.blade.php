@extends('layouts.app')

@section('title', 'Badge Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $badge->name }}</h1>
                <p class="text-gray-600 mt-2">{{ $badge->description }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('badges.edit', $badge->id) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Edit Badge
                </a>
                <form action="{{ route('badges.toggle-status', $badge->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" 
                            class="px-4 py-2 {{ $badge->is_active ? 'bg-gray-600' : 'bg-green-600' }} text-white rounded-lg hover:opacity-90">
                        {{ $badge->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Badge Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Badge Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Badge Information</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Category</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full 
                                {{ $badge->category == 'academic' ? 'bg-blue-100 text-blue-800' : 
                                   ($badge->category == 'social' ? 'bg-green-100 text-green-800' : 
                                   ($badge->category == 'attendance' ? 'bg-yellow-100 text-yellow-800' : 
                                   'bg-purple-100 text-purple-800')) }}">
                                {{ ucfirst($badge->category) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Level</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full 
                                {{ $badge->level == 'bronze' ? 'bg-orange-100 text-orange-800' : 
                                   ($badge->level == 'silver' ? 'bg-gray-100 text-gray-800' : 
                                   ($badge->level == 'gold' ? 'bg-yellow-100 text-yellow-800' : 
                                   'bg-purple-100 text-purple-800')) }}">
                                {{ ucfirst($badge->level) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Points Awarded</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ number_format($badge->points_required) }} points</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            @if($badge->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Sort Order</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $badge->sort_order }}</dd>
                    </div>
                    @if($badge->icon)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Icon</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <i class="{{ $badge->icon }} text-2xl"></i>
                            <span class="ml-2 text-xs text-gray-500">{{ $badge->icon }}</span>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Badge Stats -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Users Earned</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $badge->users()->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $badge->created_at->format('F d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $badge->updated_at->format('F d, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Criteria Section -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Earning Criteria</h2>
            @if($badge->criteria && count($badge->criteria) > 0)
                <ul class="space-y-2">
                    @foreach($badge->criteria as $criterion)
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">{{ $criterion }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500">No specific criteria defined for this badge.</p>
            @endif
        </div>

        <!-- Users Who Earned This Badge -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Users Who Earned This Badge</h2>
            @if($badge->users->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Earned Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Featured</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($badge->users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($user->profile_photo)
                                                <img class="h-10 w-10 rounded-full object-cover" 
                                                     src="{{ asset('storage/' . $user->profile_photo) }}" 
                                                     alt="{{ $user->name }}">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-gray-600 font-medium">{{ substr($user->name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ ucfirst($user->getRoleNames()->first()) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->pivot->earned_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($user->pivot->is_featured)
                                        <span class="text-yellow-500">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500">No users have earned this badge yet.</p>
            @endif
        </div>

        <!-- Back Link -->
        <div class="mt-6 text-center">
            <a href="{{ route('admin.badges.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Back to Badges
            </a>
        </div>
    </div>
</div>
@endsection