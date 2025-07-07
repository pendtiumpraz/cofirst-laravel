@extends('layouts.app')

@section('title', 'Certificates')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Certificates</h1>
            <p class="text-gray-600">Manage and view certificates</p>
        </div>
        @if(auth()->user()->hasRole(['admin', 'teacher']))
        <div class="space-x-3">
            <a href="{{ route('certificates.bulk-create') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                Bulk Create
            </a>
            <a href="{{ route('certificates.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Create Certificate
            </a>
        </div>
        @endif
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('certificates.index') }}" class="flex gap-4">
            <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Types</option>
                <option value="completion" {{ request('type') == 'completion' ? 'selected' : '' }}>Completion</option>
                <option value="achievement" {{ request('type') == 'achievement' ? 'selected' : '' }}>Achievement</option>
                <option value="participation" {{ request('type') == 'participation' ? 'selected' : '' }}>Participation</option>
            </select>
            
            <select name="is_valid" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="1" {{ request('is_valid') == '1' ? 'selected' : '' }}>Valid</option>
                <option value="0" {{ request('is_valid') == '0' ? 'selected' : '' }}>Invalid</option>
            </select>
            
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                Filter
            </button>
            
            <a href="{{ route('certificates.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Clear
            </a>
        </form>
    </div>

    <!-- Certificates Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="table-wrapper">
            <table class="min-w-full divide-y divide-gray-200" data-enhance="true" data-searchable="true" data-sortable="true" data-show-no="true">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Certificate Number
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Student
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Course/Class
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Issue Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($certificates as $certificate)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $certificate->certificate_number }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $certificate->student->name }}</div>
                        <div class="text-sm text-gray-500">{{ $certificate->student->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($certificate->type == 'completion') bg-green-100 text-green-800
                            @elseif($certificate->type == 'achievement') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($certificate->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            {{ $certificate->course ? $certificate->course->name : '-' }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $certificate->class ? $certificate->class->name : '-' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $certificate->issue_date->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($certificate->is_valid && !$certificate->isExpired())
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Valid
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Invalid
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <a href="{{ route('certificates.show', $certificate) }}" class="text-indigo-600 hover:text-indigo-900">
                            View
                        </a>
                        <a href="{{ route('certificates.download', $certificate) }}" class="text-green-600 hover:text-green-900">
                            Download
                        </a>
                        @if(auth()->user()->hasRole(['admin', 'superadmin']) && $certificate->is_valid)
                        <form action="{{ route('certificates.invalidate', $certificate) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">
                                Invalidate
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        No certificates found.
                    </td>
                </tr>
                @endforelse
            </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $certificates->links() }}
        </div>
    </div>
</div>
@endsection