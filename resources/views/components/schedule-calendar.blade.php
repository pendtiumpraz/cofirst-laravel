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
                    $dayOfWeek = $date->dayOfWeek === 0 ? 7 : $date->dayOfWeek; // Convert Sunday from 0 to 7
                    $daySchedules = $schedules->filter(function ($schedule) use ($dayOfWeek) {
                        return $schedule->day_of_week == $dayOfWeek;
                    })->sortBy('start_time');
                @endphp

                <div class="flex-none w-64 bg-gray-50 rounded-lg p-4">
                    <h4 class="font-bold text-gray-800 mb-3">{{ $date->format('D, M d') }}</h4>
                    @if ($daySchedules->isEmpty())
                        <p class="text-gray-500 text-sm">No schedules</p>
                    @else
                        <ul class="space-y-2">
                            @foreach ($daySchedules as $schedule)
                                <li class="bg-white p-3 rounded-md shadow-sm border border-gray-200">
                                    @php
                                        // Handle both string and Carbon object time formats
                                        $startTime = is_string($schedule->start_time) ? $schedule->start_time : $schedule->start_time->format('H:i');
                                        $endTime = is_string($schedule->end_time) ? $schedule->end_time : $schedule->end_time->format('H:i');
                                    @endphp
                                    <p class="text-sm font-semibold text-gray-900">{{ $startTime }} - {{ $endTime }}</p>
                                    <p class="text-xs text-gray-700">Class: {{ $schedule->className->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-700">Teacher: {{ $schedule->teacherAssignment->teacher->name ?? $schedule->className->teacher->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-700">Student: {{ $schedule->enrollment->student->name ?? 'Current User' }}</p>
                                    @if(isset($schedule->room) && $schedule->room)
                                        <p class="text-xs text-gray-600">Room: {{ $schedule->room }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endfor
        </div>
    </div>
</div>
