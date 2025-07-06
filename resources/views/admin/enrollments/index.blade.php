<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Enrollments') }}
        </h2>
            <a href="{{ route('admin.enrollments.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                {{ __('Create Enrollment') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                                    @if($enrollments->count() > 0)
                    <div class="table-wrapper">
                        <div class="shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200" data-enhance="true" data-searchable="true" data-sortable="true" data-show-no="true">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrollment Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($enrollments as $enrollment)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-10 w-10 flex-shrink-0 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center">
                                                        <span class="text-sm font-medium text-white">{{ substr($enrollment->student->name, 0, 1) }}</span>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ $enrollment->student->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $enrollment->student->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $enrollment->class->name }}</div>
                                                <div class="text-sm text-gray-500">{{ ucfirst($enrollment->class->type) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-8 w-8 flex-shrink-0 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                                                        <span class="text-xs font-medium text-white">{{ substr($enrollment->class->course->name, 0, 1) }}</span>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ $enrollment->class->course->name }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                                                    </svg>
                                                    <span class="text-sm text-gray-900">{{ $enrollment->enrollment_date->toFormattedDateString() }}</span>
                                                </div>
                                            </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $enrollment->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    <span class="w-2 h-2 mr-1 rounded-full {{ $enrollment->status === 'active' ? 'bg-green-400' : 'bg-yellow-400' }}"></span>
                                                    {{ ucfirst($enrollment->status) }}
                                            </span>
                                        </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center space-x-3">
                                                    <a href="{{ route('admin.enrollments.show', $enrollment) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"/>
                                                        </svg>
                                                        View
                                                    </a>
                                                    <a href="{{ route('admin.enrollments.edit', $enrollment) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                                        </svg>
                                                        Edit
                                                    </a>
                                                    <form action="{{ route('admin.enrollments.destroy', $enrollment) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Are you sure?')">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"/>
                                                            </svg>
                                                            Delete
                                                        </button>
                                            </form>
                                                </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"/>
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">No enrollments found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new enrollment.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.enrollments.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                                    </svg>
                                    Create Enrollment
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($enrollments->hasPages())
                        <div class="mt-6">
                        {{ $enrollments->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
