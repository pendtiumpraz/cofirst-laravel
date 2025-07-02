<div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Schedules (Next 7 Days)</h3>
    <div class="overflow-x-auto">
        <div class="flex space-x-4">
            @php
                $today = \Carbon\Carbon::today();
            @endphp

            @for ($i = 0; $i < 7; $i++)
                @php
                    $date = $today->copy()->addDays($i);
                    $daySchedules = $schedules->filter(function ($schedule) use ($date) {
                        return \Carbon\Carbon::parse($schedule->schedule_date)->isSameDay($date);
                    })->sortBy('schedule_time');
                @endphp

                <div class="flex-none w-64 bg-gray-50 rounded-lg p-4">
                    <h4 class="font-bold text-gray-800 mb-3">{{ $date->format('D, M d') }}</h4>
                    @if ($daySchedules->isEmpty())
                        <p class="text-gray-500 text-sm">No schedules</p>
                    @else
                        <ul class="space-y-2">
                            @foreach ($daySchedules as $schedule)
                                <li class="bg-white p-3 rounded-md shadow-sm border border-gray-200">
                                    <p class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($schedule->schedule_time)->format('H:i') }}</p>
                                    <p class="text-xs text-gray-700">Class: {{ $schedule->className->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-700">Teacher: {{ $schedule->teacherAssignment->teacher->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-700">Student: {{ $schedule->enrollment->student->name ?? 'N/A' }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endfor
        </div>
    </div>
</div>
