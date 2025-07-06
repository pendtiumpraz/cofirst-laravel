<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reports for') }} {{ $student->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('parent.reports.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    ← Back to All Reports
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <div class="flex items-center">
                            <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-blue-600 font-medium text-lg">{{ strtoupper(substr($student->name, 0, 2)) }}</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ $student->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $student->email }}</p>
                            </div>
                        </div>
                    </div>

                    @if($reports->isEmpty())
                        <div class="text-center py-8">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No reports available</h3>
                            <p class="mt-1 text-sm text-gray-500">There are no reports for {{ $student->name }} yet.</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($reports as $report)
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <h4 class="text-lg font-medium text-gray-900">{{ $report->title ?? 'Progress Report' }}</h4>
                                                <p class="text-sm text-gray-500">
                                                    {{ $report->created_at->format('F j, Y') }}
                                                    @if($report->class)
                                                        • {{ $report->class->name }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ ucfirst($report->status ?? 'published') }}
                                            </span>
                                            @if($report->teacher)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $report->teacher->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($report->description)
                                        <div class="mb-4">
                                            <h5 class="font-medium text-gray-900 mb-2">Report Summary</h5>
                                            <p class="text-sm text-gray-700">{{ $report->description }}</p>
                                        </div>
                                    @endif

                                    @if($report->content)
                                        <div class="mb-4">
                                            <h5 class="font-medium text-gray-900 mb-2">Detailed Report</h5>
                                            <div class="prose prose-sm max-w-none text-gray-700">
                                                {!! nl2br(e($report->content)) !!}
                                            </div>
                                        </div>
                                    @endif

                                    @if($report->grade || $report->score)
                                        <div class="mb-4">
                                            <h5 class="font-medium text-gray-900 mb-2">Assessment</h5>
                                            <div class="flex items-center space-x-4">
                                                @if($report->grade)
                                                    <div class="flex items-center">
                                                        <span class="text-sm font-medium text-gray-600">Grade:</span>
                                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                            {{ $report->grade }}
                                                        </span>
                                                    </div>
                                                @endif
                                                @if($report->score)
                                                    <div class="flex items-center">
                                                        <span class="text-sm font-medium text-gray-600">Score:</span>
                                                        <span class="ml-2 text-sm font-semibold text-blue-600">{{ $report->score }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if($report->feedback)
                                        <div class="mb-4">
                                            <h5 class="font-medium text-gray-900 mb-2">Teacher's Feedback</h5>
                                            <div class="bg-blue-50 rounded-lg p-4">
                                                <p class="text-sm text-blue-800">{{ $report->feedback }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($report->recommendations)
                                        <div class="mb-4">
                                            <h5 class="font-medium text-gray-900 mb-2">Recommendations</h5>
                                            <div class="bg-yellow-50 rounded-lg p-4">
                                                <p class="text-sm text-yellow-800">{{ $report->recommendations }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="pt-4 border-t border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center text-sm text-gray-500">
                                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Last updated: {{ $report->updated_at->format('M j, Y \a\t g:i A') }}
                                            </div>
                                            @if($report->class && $report->class->course)
                                                <div class="flex items-center text-sm text-gray-500">
                                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                    </svg>
                                                    Course: {{ $report->class->course->name }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 