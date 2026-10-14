<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The course catalogue (real Coding First programmes).
 *
 * Production-safe: a course is created only when no course with that name or slug
 * exists (archived ones included), so prices or texts edited by an admin are never
 * overwritten, and a renamed course is not re-created by a later db:seed.
 */
class CourseSeeder extends Seeder
{
    /**
     * The catalogue: name, description, price (IDR), level, duration (weeks).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function catalogue(): array
    {
        return [
            ['name' => 'Scratch', 'description' => 'Pemrograman visual untuk anak-anak menggunakan Scratch. Belajar konsep dasar programming dengan cara yang menyenangkan.', 'price' => 1500000, 'level' => 'beginner', 'duration_weeks' => 8],
            ['name' => 'Lego Spike (Robotik)', 'description' => 'Belajar robotika menggunakan Lego Spike Prime. Kombinasi programming dan engineering untuk anak-anak.', 'price' => 2500000, 'level' => 'beginner', 'duration_weeks' => 8],
            ['name' => 'Microbit', 'description' => 'Pemrograman mikrokontroler BBC micro:bit. Belajar IoT dan embedded programming untuk pemula.', 'price' => 1800000, 'level' => 'beginner', 'duration_weeks' => 8],
            ['name' => 'Arduino', 'description' => 'Pemrograman mikrokontroler Arduino. Belajar elektronika dan IoT dengan platform open-source.', 'price' => 2200000, 'level' => 'intermediate', 'duration_weeks' => 8],
            ['name' => 'AI Generatif (1 Pertemuan)', 'description' => 'Workshop intensif tentang AI Generatif, ChatGPT, dan tools AI modern dalam satu pertemuan.', 'price' => 500000, 'level' => 'beginner', 'duration_weeks' => 1],
            ['name' => 'Wordpress x DIVI x AI (1 Pertemuan)', 'description' => 'Workshop membuat website dengan WordPress, theme DIVI, dan integrasi AI dalam satu pertemuan.', 'price' => 750000, 'level' => 'beginner', 'duration_weeks' => 1],
            ['name' => 'Python', 'description' => 'Pemrograman Python dari dasar hingga mahir. Cocok untuk data science, web development, dan automation.', 'price' => 3000000, 'level' => 'intermediate', 'duration_weeks' => 8],
            ['name' => 'Laravel', 'description' => 'Framework PHP Laravel untuk web development. Belajar membuat aplikasi web modern dan scalable.', 'price' => 3500000, 'level' => 'advanced', 'duration_weeks' => 8],
            ['name' => 'Javascript', 'description' => 'Pemrograman JavaScript modern (ES6+). Dari fundamental hingga framework seperti React dan Node.js.', 'price' => 3200000, 'level' => 'intermediate', 'duration_weeks' => 8],
            ['name' => 'Minecraft EDU', 'description' => 'Belajar programming dan computational thinking melalui Minecraft Education Edition.', 'price' => 1600000, 'level' => 'beginner', 'duration_weeks' => 8],
            ['name' => 'Roblox', 'description' => 'Game development menggunakan Roblox Studio dan Lua scripting. Buat game sendiri di platform Roblox.', 'price' => 2000000, 'level' => 'intermediate', 'duration_weeks' => 8],
            ['name' => 'Unity 2D', 'description' => 'Game development 2D menggunakan Unity Engine dan C#. Belajar membuat game dari konsep hingga publish.', 'price' => 2800000, 'level' => 'intermediate', 'duration_weeks' => 8],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $benefits = "Kurikulum terstruktur per pertemuan\nMentor berpengalaman dari perusahaan teknologi Jepang\nLaporan perkembangan untuk orang tua\nSertifikat setelah lulus";

        foreach (self::catalogue() as $course) {
            if (! self::find($course['name'])) {
                // 90-minute meetings; one-meeting workshops last about 4 hours.
                $hours = $course['duration_weeks'] === 1 ? 4 : (int) round($course['duration_weeks'] * 1.5);
                Course::create($course + ['benefits' => $benefits, 'duration_hours' => $hours, 'is_active' => true]);
            }
        }

        $this->command?->info('Courses: '.Course::count().' in catalogue.');
    }

    /**
     * The existing course (archived ones included) for a catalogue name, if any.
     *
     * Reason: the slug is set once on create and never changes, so a course an
     * admin renamed (e.g. "Python" -> "Python Dasar") is still recognised and not
     * seeded a second time.
     *
     * Args:
     *     name (string): Catalogue course name.
     *
     * Returns:
     *     Course|null: The matching course.
     */
    public static function find(string $name): ?Course
    {
        return Course::withTrashed()
            ->where(fn ($query) => $query->where('name', $name)->orWhere('slug', Str::slug($name)))
            ->orderByRaw('CASE WHEN name = ? THEN 0 ELSE 1 END', [$name])
            ->orderBy('id')
            ->first();
    }
}
