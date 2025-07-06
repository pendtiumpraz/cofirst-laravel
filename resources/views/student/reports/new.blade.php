<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Reports - New') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Header Section -->
                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">My Reports</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                View your progress reports and assessments
                            </p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-500">
                                <span class="font-medium">Total Reports:</span>
                                <span class="ml-1 bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                    {{ $reports->count() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Reports Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-blue-600">Total Reports</div>
                                    <div class="text-2xl font-bold text-blue-900">{{ $reports->count() }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-green-600">Progress Reports</div>
                                    <div class="text-2xl font-bold text-green-900">{{ $reports->where('type', 'progress')->count() }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-purple-600">Assessments</div>
                                    <div class="text-2xl font-bold text-purple-900">{{ $reports->where('type', 'assessment')->count() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reports Table -->
                    <div class="bg-white">
                        <div class="mb-4">
                            <h4 class="text-lg font-medium text-gray-900">All Reports</h4>
                            <p class="text-sm text-gray-600">Complete list of your progress reports and assessments</p>
                        </div>
                        
                        @if($reports->isNotEmpty())
                        <div class="shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                            <div class="table-wrapper">
                                <table class="min-w-full divide-y divide-gray-200" data-enhance="true" data-searchable="true" data-sortable="true" data-show-no="true">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Report
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Class
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Teacher
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Progress
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($reports as $report)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-blue-600">
                                                            <span class="text-sm font-medium text-white">
                                                                {{ substr($report->title ?? 'R', 0, 1) }}
                                                            </span>
                                                        </span>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $report->title ?? 'Progress Report' }}
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $report->type === 'progress' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                                {{ ucfirst($report->type ?? 'Progress') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $report->class->name ?? 'N/A' }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $report->class->course->name ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gradient-to-r from-green-400 to-green-600">
                                                            <span class="text-xs font-medium text-white">
                                                                {{ substr($report->teacher->name ?? 'T', 0, 2) }}
                                                            </span>
                                                        </span>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $report->teacher->name ?? 'N/A' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if(isset($report->progress_percentage) && $report->progress_percentage !== null)
                                                <div class="flex items-center">
                                                    <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                                        <div class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full" style="width: {{ $report->progress_percentage }}%"></div>
                                                    </div>
                                                    <span class="text-sm font-medium text-gray-900">{{ $report->progress_percentage }}%</span>
                                                </div>
                                                @else
                                                <span class="text-sm text-gray-500">Not specified</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $report->created_at ? $report->created_at->format('M d, Y') : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('student.reports.show', $report->id) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No reports found</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                You don't have any progress reports yet. Reports will appear here once your teacher creates them.
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('student.classes') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    View My Classes
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 