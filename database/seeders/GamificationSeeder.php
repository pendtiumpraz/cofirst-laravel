<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Reward;
use Illuminate\Database\Seeder;

/**
 * The badge and reward catalogue (Indonesian names).
 *
 * Production-safe and idempotent: badges are keyed by their stable slug, and the
 * reward catalogue is only seeded into an empty rewards table (so legacy English
 * rewards, and rewards an admin renamed or removed, are left alone). Existing rows
 * are never overwritten. Criteria use the keys Badge::checkCriteria() supports;
 * badges with other keys are awarded manually.
 */
class GamificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->badges() as $index => $badge) {
            Badge::firstOrCreate(['slug' => $badge['slug']], $badge + ['is_active' => true, 'sort_order' => $index]);
        }

        // Reason: rewards have no stable key and are hard-deleted, so the catalogue is
        // only seeded into an empty table; a reward an admin renamed or removed is
        // never re-created by a later db:seed.
        if (! Reward::query()->exists()) {
            foreach ($this->rewards() as $reward) {
                unset($reward['legacy']);
                Reward::create($reward + ['quantity_redeemed' => 0, 'is_active' => true]);
            }
        }

        $this->command?->info('Gamification catalogue: '.Badge::count().' badges, '.Reward::count().' rewards.');
    }

    /**
     * Badge definitions (slug is the stable key).
     *
     * @return array<int, array<string, mixed>>
     */
    private function badges(): array
    {
        $badge = fn (string $slug, string $name, string $description, string $category, string $level, int $points, string $icon, array $criteria) => [
            'slug' => $slug, 'name' => $name, 'description' => $description, 'category' => $category, 'level' => $level,
            'points_required' => $points, 'icon' => $icon, 'image_path' => "images/badges/{$slug}.svg", 'criteria' => $criteria,
        ];

        return [
            $badge('first-assignment', 'Langkah Pertama', 'Menyelesaikan pertemuan pertama', Badge::CATEGORY_ACADEMIC, Badge::LEVEL_BRONZE, 10, 'document-text', ['meetings_completed' => 1]),
            $badge('quiz-master', 'Jago Kuis', 'Mendapat nilai tinggi di banyak kuis', Badge::CATEGORY_ACADEMIC, Badge::LEVEL_SILVER, 50, 'brain', ['quiz_scores_90_plus' => 5]),
            $badge('perfect-score', 'Nilai Sempurna', 'Meraih nilai 100 pada kuis', Badge::CATEGORY_ACADEMIC, Badge::LEVEL_GOLD, 100, 'star', ['perfect_scores' => 3]),
            $badge('study-streak', 'Rajin Belajar', 'Aktif belajar 7 hari berturut-turut', Badge::CATEGORY_ACADEMIC, Badge::LEVEL_GOLD, 75, 'fire', ['study_streak' => 7]),
            $badge('reading-champion', 'Kutu Buku', 'Membuka 20 materi pelajaran', Badge::CATEGORY_ACADEMIC, Badge::LEVEL_SILVER, 60, 'book-open', ['materials_read' => 20]),
            $badge('team-player', 'Kerja Sama Tim', 'Bekerja sama dengan baik di kelas grup', Badge::CATEGORY_SOCIAL, Badge::LEVEL_BRONZE, 30, 'users', ['team_activities' => 5]),
            $badge('helpful-hand', 'Teman Penolong', 'Membantu teman sekelas', Badge::CATEGORY_SOCIAL, Badge::LEVEL_SILVER, 50, 'hand-heart', ['help_given' => 10]),
            $badge('mentor', 'Mentor Cilik', 'Membimbing siswa yang lebih baru', Badge::CATEGORY_SOCIAL, Badge::LEVEL_PLATINUM, 200, 'user-graduate', ['mentorship_hours' => 20]),
            $badge('perfect-attendance', 'Kehadiran Sempurna', 'Hadir di 12 pertemuan', Badge::CATEGORY_ATTENDANCE, Badge::LEVEL_GOLD, 100, 'calendar-check', ['attendance_count' => 12]),
            $badge('early-bird', 'Datang Lebih Awal', 'Hadir di 5 pertemuan', Badge::CATEGORY_ATTENDANCE, Badge::LEVEL_BRONZE, 25, 'sunrise', ['attendance_count' => 5]),
            $badge('first-login', 'Selamat Datang', 'Pertama kali masuk ke Coding First', Badge::CATEGORY_SPECIAL, Badge::LEVEL_BRONZE, 5, 'login', ['first_login' => true]),
            $badge('legend', 'Legenda', 'Mengumpulkan 1.000 poin', Badge::CATEGORY_SPECIAL, Badge::LEVEL_PLATINUM, 500, 'crown', ['total_points' => 1000]),
            $badge('milestone', 'Pencapaian', 'Menyelesaikan 16 pertemuan', Badge::CATEGORY_SPECIAL, Badge::LEVEL_SILVER, 150, 'trophy', ['meetings_completed' => 16]),
            $badge('creative-genius', 'Kreator Hebat', 'Memamerkan 3 proyek di galeri', Badge::CATEGORY_SPECIAL, Badge::LEVEL_GOLD, 200, 'palette', ['creative_projects' => 3]),
        ];
    }

    /**
     * Reward definitions; `legacy` is the former English name.
     *
     * @return array<int, array<string, mixed>>
     */
    private function rewards(): array
    {
        return [
            ['name' => 'Tambahan Waktu Tugas', 'legacy' => 'Extra Assignment Time', 'description' => 'Tambahan 24 jam untuk mengumpulkan tugas berikutnya', 'type' => Reward::TYPE_PRIVILEGE, 'points_cost' => 100, 'quantity_available' => null, 'metadata' => ['hours' => 24]],
            ['name' => 'Lewati Satu Kuis', 'legacy' => 'Skip One Quiz', 'description' => 'Melewati satu kuis tanpa pengurangan nilai', 'type' => Reward::TYPE_PRIVILEGE, 'points_cost' => 300, 'quantity_available' => null, 'metadata' => ['quiz_count' => 1]],
            ['name' => 'Desain Sertifikat Khusus', 'legacy' => 'Certificate Customization', 'description' => 'Desain khusus untuk sertifikat berikutnya', 'type' => Reward::TYPE_DIGITAL, 'points_cost' => 150, 'quantity_available' => null, 'metadata' => ['customization' => true]],
            ['name' => 'Kaos Coding First', 'legacy' => 'School T-Shirt', 'description' => 'Kaos resmi Coding First', 'type' => Reward::TYPE_PHYSICAL, 'points_cost' => 500, 'quantity_available' => 50, 'metadata' => ['sizes' => ['S', 'M', 'L', 'XL']]],
            ['name' => 'Set Buku Catatan', 'legacy' => 'Notebook Set', 'description' => 'Tiga buku catatan dengan logo Coding First', 'type' => Reward::TYPE_PHYSICAL, 'points_cost' => 200, 'quantity_available' => 100, 'metadata' => []],
            ['name' => 'Mug Coding First', 'legacy' => 'School Mug', 'description' => 'Mug keramik bergambar logo Coding First', 'type' => Reward::TYPE_PHYSICAL, 'points_cost' => 250, 'quantity_available' => 75, 'metadata' => []],
            ['name' => 'Diskon Kursus 10%', 'legacy' => '10% Course Discount', 'description' => 'Potongan 10% untuk pendaftaran kursus berikutnya', 'type' => Reward::TYPE_DISCOUNT, 'points_cost' => 400, 'quantity_available' => null, 'metadata' => ['discount_percentage' => 10]],
            ['name' => 'Diskon Kursus 25%', 'legacy' => '25% Course Discount', 'description' => 'Potongan 25% untuk pendaftaran kursus berikutnya', 'type' => Reward::TYPE_DISCOUNT, 'points_cost' => 800, 'quantity_available' => 20, 'metadata' => ['discount_percentage' => 25]],
            ['name' => 'Bantuan Prioritas', 'legacy' => 'Priority Support', 'description' => 'Konsultasi prioritas dengan mentor selama 1 bulan', 'type' => Reward::TYPE_PRIVILEGE, 'points_cost' => 350, 'quantity_available' => null, 'metadata' => ['duration_days' => 30]],
            ['name' => 'Akses Awal Kursus Baru', 'legacy' => 'Early Course Access', 'description' => 'Akses lebih awal ke kursus baru selama 3 bulan', 'type' => Reward::TYPE_PRIVILEGE, 'points_cost' => 600, 'quantity_available' => null, 'metadata' => ['duration_days' => 90]],
        ];
    }
}
