<?php

namespace Database\Factories;

use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\TeacherAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 *
 * The enrollment and teacher assignment always belong to the same class as the
 * schedule; end_time is 90 minutes after start_time.
 */
class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startHour = $this->faker->numberBetween(8, 18);

        return [
            'class_id' => ClassName::factory()->active(),
            'day_of_week' => $this->faker->numberBetween(1, 7), // 1=Senin, 7=Minggu
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:30:00', $startHour + 1),
            'room' => $this->faker->randomElement(['Ruang A', 'Ruang B', 'Online', 'Lab 1']),
            'enrollment_id' => fn (array $attributes) => Enrollment::factory()->create(['class_id' => $attributes['class_id']])->id,
            'teacher_assignment_id' => fn (array $attributes) => TeacherAssignment::where('class_id', $attributes['class_id'])
                ->whereNull('unassigned_at')->value('id')
                ?? TeacherAssignment::factory()->create(['class_id' => $attributes['class_id']])->id,
            'is_active' => true,
        ];
    }
}
