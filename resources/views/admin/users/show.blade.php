@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">User Details</h1>
                    <p class="text-gray-600">View user information</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.users.edit', $user) }}" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Edit User
                    </a>
                    <a href="{{ route('admin.users.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Back to Users
                    </a>
                </div>
            </div>

            <!-- User Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <x-user-avatar :user="$user" size="lg" gradient="blue" />
                        <div>
                            <h4 class="text-xl font-semibold text-gray-900">{{ $user->name }}</h4>
                            <p class="text-gray-600">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Role</label>
                            <div class="flex flex-wrap gap-2 mt-1">
                                @foreach($user->roles as $role)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Member Since</label>
                            <p class="text-gray-900">{{ $user->created_at->format('F d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parent-Student Relationships -->
            @if($user->hasRole('parent') && $user->children->count() > 0)
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Children</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($user->children as $child)
                            <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <x-user-avatar :user="$child" size="sm" gradient="green" />
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">{{ $child->name }}</h4>
                                        <p class="text-xs text-gray-500">{{ $child->email }}</p>
                                        <div class="flex items-center mt-1">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                                Student
                                            </span>
                                            <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full 
                                                {{ $child->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $child->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('admin.users.show', $child) }}" 
                                       class="text-xs text-blue-600 hover:text-blue-800 transition-colors">
                                        View Details →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($user->hasRole('student') && $user->parents->count() > 0)
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Parents</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($user->parents as $parent)
                            <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <x-user-avatar :user="$parent" size="sm" gradient="purple" />
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">{{ $parent->name }}</h4>
                                        <p class="text-xs text-gray-500">{{ $parent->email }}</p>
                                        <div class="flex items-center mt-1">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                                Parent
                                            </span>
                                            <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full 
                                                {{ $parent->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $parent->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('admin.users.show', $parent) }}" 
                                       class="text-xs text-blue-600 hover:text-blue-800 transition-colors">
                                        View Details →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Show message if no relationships exist -->
            @if(($user->hasRole('parent') && $user->children->count() == 0) || ($user->hasRole('student') && $user->parents->count() == 0))
                <div class="border-t border-gray-200 pt-6">
                    <div class="text-center py-8">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            @if($user->hasRole('parent'))
                                No children linked
                            @elseif($user->hasRole('student'))
                                No parents linked
                            @else
                                No family relationships
                            @endif
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if($user->hasRole('parent'))
                                This parent account has no linked children.
                            @elseif($user->hasRole('student'))
                                This student account has no linked parents.
                            @else
                                This user has no family relationships.
                            @endif
                        </p>
                        @if($user->hasRole('parent') || $user->hasRole('student'))
                            <div class="mt-6">
                                <a href="{{ route('admin.users.edit', $user) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Edit Relationships
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 