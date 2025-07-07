<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application\'s database.
     */
    public function run(): void
    {
        // Urutan seeder ini sangat penting untuk menjaga relasi data (foreign key).
        // 1. Buat Roles terlebih dahulu.
        $this->call(RoleSeeder::class);

        // 2. Buat Users dan Profiles, lalu assign Roles.
        $this->call(UserSeeder::class);

        // 3. Buat data master akademik.
        $this->call(CourseSeeder::class);
        
        // 4. Buat curriculum, syllabus, dan materials dengan data detail
        $this->call(EnhancedCurriculumSeeder::class);

        // 5. Buat instance kelas dari course yang ada.
        $this->call(ClassNameSeeder::class);

        // 6. Buat penugasan guru untuk setiap kelas.
        $this->call(TeacherAssignmentSeeder::class);

        // 7. Daftarkan siswa ke dalam kelas.
        $this->call(EnrollmentSeeder::class); // Dipindahkan ke atas ScheduleSeeder

        // 8. Buat jadwal.
        $this->call(ScheduleSeeder::class);

        // 9. Buat laporan, transaksi keuangan, dan pengumuman.
        $this->call(ReportSeeder::class);
        $this->call(FinancialTransactionSeeder::class);
        $this->call(AnnouncementSeeder::class);
        
        // 10. Seed gamification data (badges and rewards).
        $this->call(GamificationSeeder::class);
        
        // 11. Seed testimonials from parents.
        $this->call(TestimonialSeeder::class);
        
        // 12. [Optional] Tambahan dummy data untuk testing pagination (uncomment jika diperlukan)
        // $this->call(CurriculumSeeder::class); // Tambah 25 dummy curriculums
    }
}
