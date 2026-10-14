<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Material;
use App\Models\Syllabus;
use Database\Seeders\Data\CurriculumContent;
use Illuminate\Database\Seeder;

/**
 * One curriculum per catalogue course with its meetings (syllabuses) and materials.
 *
 * Regular courses get 8 meetings, "(1 Pertemuan)" workshops 1. Production-safe:
 * rows are created only when missing (curriculum by course, syllabus by meeting
 * number, materials only for a meeting that has none), so content edited,
 * renamed or archived in the admin is never overwritten or re-created, and
 * courses an admin added are not touched. Materials have no placeholder links.
 */
class EnhancedCurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reason: only catalogue courses get a seeded curriculum, so courses an admin
        // created later are left alone. The catalogue name (not the possibly renamed
        // course name) selects the content.
        $courses = collect(CourseSeeder::catalogue())
            ->mapWithKeys(fn (array $item) => [$item['name'] => CourseSeeder::find($item['name'])])
            ->filter(fn (?Course $course) => $course !== null && ! $course->trashed());

        if ($courses->isEmpty()) {
            $this->command?->info('No courses found. Please run CourseSeeder first.');

            return;
        }

        $meetings = 0;

        foreach ($courses as $courseName => $course) {
            $total = $this->meetingCount($courseName);

            $curriculum = Curriculum::withTrashed()->firstOrCreate(
                ['course_id' => $course->id],
                [
                    'title' => 'Kurikulum '.$courseName,
                    'description' => 'Kurikulum lengkap '.$courseName.' dalam '.$total.' pertemuan.',
                    'type' => $this->curriculumType($courseName),
                    'status' => 'active',
                    'duration_weeks' => $total,
                    'objectives' => CurriculumContent::objectives()[$courseName] ?? ['Memahami konsep dasar', 'Membangun proyek nyata'],
                ]
            );

            // An archived curriculum stays as the admin left it.
            if ($curriculum->trashed()) {
                continue;
            }

            for ($number = 1; $number <= $total; $number++) {
                $this->seedMeeting($curriculum, $courseName, $number, $total);
                $meetings++;
            }
        }

        $this->command?->info("Curriculum: {$courses->count()} curriculums, {$meetings} meetings.");
    }

    /**
     * Create one syllabus (meeting) and its materials when missing.
     */
    private function seedMeeting(Curriculum $curriculum, string $courseName, int $number, int $total): void
    {
        $data = CurriculumContent::meeting($courseName, $number, $total);

        $syllabus = Syllabus::withTrashed()->firstOrCreate(
            ['curriculum_id' => $curriculum->id, 'meeting_number' => $number],
            [
                'title' => $data['title'],
                'description' => $data['description'],
                'learning_objectives' => $data['learning_objectives'],
                'activities' => $data['activities'],
                'duration_minutes' => $data['duration_minutes'],
                'status' => 'active',
            ]
        );

        // Reason: materials have no stable key (an admin may rename them), so they are
        // only seeded into a meeting that has none yet (archived ones included).
        if (Material::withTrashed()->where('syllabus_id', $syllabus->id)->exists()) {
            return;
        }

        foreach ($data['materials'] as $index => $material) {
            Material::create(
                [
                    'syllabus_id' => $syllabus->id,
                    'title' => $material['title'],
                    'content' => CurriculumContent::materialContent($material['title'], $material['type']),
                    'description' => 'Materi pertemuan '.$number.': '.$data['title'],
                    'meeting_start' => $number,
                    'meeting_end' => $number,
                    'type' => $material['type'],
                    'file_path' => null,
                    'external_url' => null,
                    'status' => 'active',
                    'order' => $index + 1,
                ]
            );
        }
    }

    /**
     * Number of meetings for a course (1 for one-meeting workshops).
     */
    private function meetingCount(string $courseName): int
    {
        return str_contains($courseName, '1 Pertemuan') ? 1 : 8;
    }

    /**
     * Curriculum type by course name.
     *
     * Reason: match with str_contains so "Lego Spike (Robotik)" is recognised.
     */
    private function curriculumType(string $courseName): string
    {
        return match (true) {
            str_contains($courseName, '1 Pertemuan') => 'fast-track',
            in_array($courseName, ['Laravel', 'Python', 'Javascript', 'Unity 2D'], true) => 'expert',
            str_contains($courseName, 'Scratch'), str_contains($courseName, 'Lego Spike'), str_contains($courseName, 'Minecraft') => 'beginner',
            default => 'regular',
        };
    }
}
