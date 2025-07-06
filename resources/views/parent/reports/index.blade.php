<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Children Reports') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Your Children's Reports</h3>
                        <p class="mt-1 text-sm text-gray-600">View all reports about your children's academic progress and performance.</p>
                    </div>

                    @if($children->isEmpty())
                        <div class="text-center py-8">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No children found</h3>
                            <p class="mt-1 text-sm text-gray-500">You don't have any children enrolled in classes yet.</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($children as $child)
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-blue-600 font-medium">{{ strtoupper(substr($child->name, 0, 2)) }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <h4 class="text-lg font-medium text-gray-900">{{ $child->name }}</h4>
                                                <p class="text-sm text-gray-500">{{ $child->email }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('parent.child-reports', $child->id) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200">
                                            View Reports
                                        </a>
                                    </div>

                                    @if($child->studentReports && $child->studentReports->count() > 0)
                                        <div class="space-y-3">
                                            <h5 class="font-medium text-gray-900">Recent Reports</h5>
                                            @foreach($child->studentReports->take(3) as $report)
                                                <div class="border rounded-lg p-4">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <h6 class="font-medium text-gray-900">{{ $report->title ?? 'Progress Report' }}</h6>
                                                        <span class="text-sm text-gray-500">{{ $report->created_at->format('M d, Y') }}</span>
                                                    </div>
                                                    @if($report->description)
                                                        <p class="text-sm text-gray-600 mb-2">{{ Str::limit($report->description, 100) }}</p>
                                                    @endif
                                                    <div class="flex items-center justify-between">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            {{ ucfirst($report->status ?? 'published') }}
                                                        </span>
                                                        @if($report->teacher)
                                                            <span class="text-xs text-gray-500">by {{ $report->teacher->name }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if($child->studentReports->count() > 3)
                                                <div class="text-center">
                                                    <a href="{{ route('parent.child-reports', $child->id) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                                        View all {{ $child->studentReports->count() }} reports
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <p class="text-sm text-gray-500">No reports available for this child yet.</p>
                                        </div>
                                    @endif

                                    @if($child->enrollments && $child->enrollments->count() > 0)
                                        <div class="mt-4 pt-4 border-t">
                                            <h6 class="text-sm font-medium text-gray-900 mb-2">Enrolled Classes</h6>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($child->enrollments as $enrollment)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $enrollment->className->name ?? 'Unknown Class' }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 