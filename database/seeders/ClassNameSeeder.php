<?php

namespace Database\Seeders;

use App\Models\ClassName;
use App\Models\Curriculum;
use Database\Seeders\Data\DemoBlueprint;
use Database\Seeders\Data\DemoContext;
use Illuminate\Database\Seeder;

/**
 * 18 demo classes in mixed statuses (active, planned, completed) and types.
 *
 * Dates are relative to today and agree with the status; each class follows the
 * curriculum of its own course and has 1-2 teachers, assigned through
 * ClassName::syncTeachers() so class_teacher and teacher_assignments agree.
 */
class ClassNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (DemoBlueprint::classes() as $code => $plan) {
            // Reason: CourseSeeder::find() also recognises a catalogue course an admin renamed.
            $course = CourseSeeder::find($plan['course']);

            if ($course?->trashed()) {
                $course = null;
            }

            if (! $course) {
                $this->command?->warn("Kursus {$plan['course']} tidak ditemukan, kelas {$code} dilewati.");

                continue;
            }

            $dates = DemoContext::meetingDates($plan);

            $class = ClassName::withTrashed()->updateOrCreate(
                ['name' => $plan['name']],
                [
                    'course_id' => $course->id,
                    'curriculum_id' => Curriculum::where('course_id', $course->id)->orderBy('id')->value('id'),
                    'description' => $this->description($plan, $course->name),
                    'start_date' => $dates->first()->toDateString(),
                    'end_date' => $dates->last()->toDateString(),
                    'max_students' => $plan['max'],
                    'status' => $plan['status'],
                    'type' => $plan['type'],
                    'delivery_method' => $plan['delivery'],
                    'is_active' => true,
                ]
            );

            if ($class->trashed()) {
                $class->restore();
            }

            $class->syncTeachers(array_map(fn (string $key) => DemoContext::user($key)->id, $plan['teachers']));
        }

        DemoContext::flush();
        $this->command?->info('Demo classes: '.count(DemoContext::classes()).' (with teachers).');
    }

    /**
     * Short Indonesian description of a class.
     */
    private function description(array $plan, string $courseName): string
    {
        $where = $plan['delivery'] === 'online' ? 'online' : 'tatap muka di '.$plan['room'];

        return "Kelas {$courseName} ({$plan['meetings']} pertemuan), {$where}, maksimal {$plan['max']} peserta.";
    }
}
