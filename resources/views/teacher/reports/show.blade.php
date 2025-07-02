<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Report Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-semibold text-gray-800">{{ $report->title }}</h3>
                        <a href="{{ route('teacher.reports.index') }}" class="text-blue-600 hover:text-blue-800 transition-colors">
                            &larr; Back to Reports
                        </a>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Student</dt>
                                <dd class="mt-1 text-md text-gray-900">{{ $report->student->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Class</dt>
                                <dd class="mt-1 text-md text-gray-900">{{ $report->class->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Report Type</dt>
                                <dd class="mt-1 text-md text-gray-900">{{ ucfirst($report->report_type) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Score</dt>
                                <dd class="mt-1 text-md text-gray-900">{{ $report->score ?? 'N/A' }}</dd>
                            </div>
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Content</dt>
                                <dd class="mt-1 text-md text-gray-900 whitespace-pre-wrap">{{ $report->content }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-6 border-t pt-6 flex justify-end">
                         <a href="{{ route('teacher.reports.edit', $report) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Edit Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
