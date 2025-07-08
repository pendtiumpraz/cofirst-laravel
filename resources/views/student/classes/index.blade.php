@extends('layouts.app')

@section('title', 'My Classes')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Classes</h1>
            <p class="text-gray-600">View all your enrolled classes and their details</p>
        </div>
    </div>

    <!-- Classes Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6">
            @if($enrollments->count() > 0)
                <div class="table-wrapper">
                    <div class="shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200" data-enhance="true" data-searchable="true" data-sortable="true" data-show-no="true">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrolled Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($enrollments as $enrollment)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                                                    <span class="text-sm font-medium text-white">{{ substr($enrollment->class->name, 0, 1) }}</span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $enrollment->class->name }}</div>
                                                    @if($enrollment->class->description)
                                                        <div class="text-sm text-gray-500">{{ Str::limit($enrollment->class->description, 50) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $enrollment->class->course->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $enrollment->class->course->level ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 flex-shrink-0 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center">
                                                    <span class="text-xs font-medium text-white">{{ substr($enrollment->class->teachers->first()->name ?? 'N', 0, 1) }}</span>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">{{ $enrollment->class->teachers->first()->name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($enrollment->class->type === 'private_home_call' || $enrollment->class->type === 'private_office_1on1' || $enrollment->class->type === 'private_online_1on1') bg-purple-100 text-purple-800
                                                @elseif($enrollment->class->type === 'group_class_3_5_kids') bg-blue-100 text-blue-800
                                                @elseif($enrollment->class->type === 'public_school_extracurricular') bg-green-100 text-green-800
                                                @elseif($enrollment->class->type === 'offline_seminar' || $enrollment->class->type === 'online_webinar') bg-yellow-100 text-yellow-800
                                                @elseif($enrollment->class->type === 'free_webinar' || $enrollment->class->type === 'free_trial_30min') bg-orange-100 text-orange-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $enrollment->class->type)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zM4 7h12v11H4V7z"/>
                                                </svg>
                                                <span class="text-sm text-gray-900">{{ $enrollment->enrollment_date->format('M d, Y') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $enrollment->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                <span class="w-2 h-2 mr-1 rounded-full {{ $enrollment->status === 'active' ? 'bg-green-400' : 'bg-yellow-400' }}"></span>
                                                {{ ucfirst($enrollment->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-3">
                                                <a href="{{ route('student.classes.show', $enrollment->class->id) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"/>
                                                    </svg>
                                                    View
                                                </a>
                                                <a href="{{ route('student.materials.by-class', $enrollment->class->id) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a2 2 0 00-2 2v6a2 2 0 002 2h2.586l-1.293-1.293a1 1 0 111.414-1.414L10 14.586V7a2 2 0 012-2h2a2 2 0 012 2v7.414l2.293-2.293a1 1 0 111.414 1.414L15.414 18H14a2 2 0 01-2-2v-2H6a2 2 0 01-2-2V5z"/>
                                                    </svg>
                                                    Materials
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Pagination -->
                @if(method_exists($enrollments, 'hasPages') && $enrollments->hasPages())
                <div class="mt-6">
                    {{ $enrollments->links() }}
                </div>
                @endif
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">No classes found</h3>
                    <p class="mt-1 text-sm text-gray-500">You are not enrolled in any classes yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection