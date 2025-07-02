<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Debug Information
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-6">
                <!-- User Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">User Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p><strong>ID:</strong> {{ auth()->user()->id }}</p>
                                <p><strong>Name:</strong> {{ auth()->user()->name }}</p>
                                <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                                <p><strong>Active:</strong> {{ auth()->user()->is_active ? 'Yes' : 'No' }}</p>
                            </div>
                            <div>
                                <p><strong>Roles:</strong> {{ auth()->user()->roles->pluck('name')->join(', ') }}</p>
                                <p><strong>Has Admin Role:</strong> {{ auth()->user()->hasRole('admin') ? 'Yes' : 'No' }}</p>
                                <p><strong>Has Teacher Role:</strong> {{ auth()->user()->hasRole('teacher') ? 'Yes' : 'No' }}</p>
                                <p><strong>Has Student Role:</strong> {{ auth()->user()->hasRole('student') ? 'Yes' : 'No' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CSS Test -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">CSS Test</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-blue-100 p-4 rounded-lg">
                                <h4 class="font-semibold text-blue-800">Blue Card</h4>
                                <p class="text-blue-600">This should be blue if Tailwind is working</p>
                                <button class="mt-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Blue Button
                                </button>
                            </div>
                            <div class="bg-green-100 p-4 rounded-lg">
                                <h4 class="font-semibold text-green-800">Green Card</h4>
                                <p class="text-green-600">This should be green if Tailwind is working</p>
                                <button class="mt-2 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Green Button
                                </button>
                            </div>
                            <div class="bg-red-100 p-4 rounded-lg">
                                <h4 class="font-semibold text-red-800">Red Card</h4>
                                <p class="text-red-600">This should be red if Tailwind is working</p>
                                <button class="mt-2 bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    Red Button
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Counts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Database Counts</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ \App\Models\ClassName::count() }}</div>
                                <div class="text-sm text-gray-600">Total Classes</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ \App\Models\Course::count() }}</div>
                                <div class="text-sm text-gray-600">Total Courses</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ \App\Models\User::count() }}</div>
                                <div class="text-sm text-gray-600">Total Users</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600">{{ \App\Models\Enrollment::count() }}</div>
                                <div class="text-sm text-gray-600">Total Enrollments</div>
                            </div>
                        </div>
                    </div>
                </div>

                @hasrole('admin|superadmin')
                <!-- Admin Classes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Admin Classes</h3>
                        @php
                            $adminClasses = \App\Models\ClassName::with(['course', 'teacher'])->withCount('enrollments')->take(5)->get();
                        @endphp
                        @if($adminClasses->count() > 0)
                            <div class="space-y-2">
                                @foreach($adminClasses as $class)
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                        <div>
                                            <strong>{{ $class->name }}</strong> - {{ $class->course->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            Teacher: {{ $class->teacher->name ?? 'N/A' }}, Students: {{ $class->enrollments_count }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-600">No classes found</p>
                        @endif
                    </div>
                </div>
                @endhasrole

                @hasrole('teacher')
                <!-- Teacher Classes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">My Teaching Classes</h3>
                        @php
                            $teacherClasses = \App\Models\ClassName::where('teacher_id', auth()->id())
                                ->with(['course', 'enrollments.student'])
                                ->withCount('enrollments')
                                ->where('status', 'active')
                                ->get();
                        @endphp
                        @if($teacherClasses->count() > 0)
                            <div class="space-y-2">
                                @foreach($teacherClasses as $class)
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                        <div>
                                            <strong>{{ $class->name }}</strong> - {{ $class->course->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            Students: {{ $class->enrollments_count }}, Status: {{ $class->status }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-600">No classes assigned to you</p>
                        @endif
                    </div>
                </div>
                @endhasrole

                @hasrole('student')
                <!-- Student Enrollments -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">My Enrollments</h3>
                        @php
                            $studentEnrollments = \App\Models\Enrollment::where('student_id', auth()->id())
                                ->with(['class.course', 'class.teacher'])
                                ->where('status', 'active')
                                ->get();
                        @endphp
                        @if($studentEnrollments->count() > 0)
                            <div class="space-y-2">
                                @foreach($studentEnrollments as $enrollment)
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                        <div>
                                            <strong>{{ $enrollment->class->name }}</strong> - {{ $enrollment->class->course->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            Teacher: {{ $enrollment->class->teacher->name ?? 'N/A' }}, Status: {{ $enrollment->status }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-600">No active enrollments found</p>
                        @endif
                    </div>
                </div>
                @endhasrole
            </div>
        </div>
    </div>
</x-app-layout> 