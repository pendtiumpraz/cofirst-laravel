<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::role('admin')->first();

        if (!$adminUser) {
            $this->command->info('Tidak ada user admin, lewati AnnouncementSeeder.');
            return;
        }

        Announcement::factory()->create([
            'user_id' => $adminUser->id,
            'title' => 'Selamat Datang di CodingFirst!',
            'content' => 'Selamat datang para siswa baru! Jadwal kelas sudah bisa diakses di dashboard masing-masing.',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
        ]);

        Announcement::factory()->create([
            'user_id' => $adminUser->id,
            'title' => 'Libur Nasional Hari Kemerdekaan',
            'content' => 'Diberitahukan bahwa pada tanggal 17 Agustus kegiatan belajar mengajar akan diliburkan.',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(18),
        ]);
    }
}
