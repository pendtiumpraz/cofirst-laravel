<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Database\Seeders\Data\DemoContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Demo announcements: current, upcoming and expired. Keyed by title.
 */
class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authorId = DemoContext::user('admin')->id;
        $today = Carbon::today();

        $announcements = [
            ['title' => 'Selamat Datang di Coding First!', 'content' => 'Selamat datang para siswa baru! Jadwal kelas sudah bisa dilihat di dashboard masing-masing.', 'start' => $today->copy()->subDays(10), 'end' => $today->copy()->addDays(20)],
            ['title' => 'Pendaftaran Kelas Javascript & Unity Dibuka', 'content' => 'Kelas Javascript Dasar dan Unity 2D Privat dimulai dua minggu lagi. Kuota terbatas, daftar lewat admin.', 'start' => $today->copy()->subDays(3), 'end' => $today->copy()->addDays(14)],
            ['title' => 'Pameran Proyek Siswa Akhir Bulan', 'content' => 'Setiap siswa dapat memamerkan proyeknya di Galeri Proyek. Orang tua diundang menonton presentasi online.', 'start' => $today->copy()->addDays(7), 'end' => $today->copy()->addDays(21)],
            ['title' => 'Libur Nasional', 'content' => 'Kegiatan belajar mengajar diliburkan pada hari libur nasional. Pertemuan pengganti dijadwalkan oleh guru.', 'start' => $today->copy()->addDays(30), 'end' => $today->copy()->addDays(31)],
            ['title' => 'Pemeliharaan Sistem Selesai', 'content' => 'Pemeliharaan sistem telah selesai. Terima kasih atas kesabarannya.', 'start' => $today->copy()->subDays(30), 'end' => $today->copy()->subDays(25)],
        ];

        foreach ($announcements as $item) {
            Announcement::updateOrCreate(['title' => $item['title']], [
                'user_id' => $authorId,
                'content' => $item['content'],
                'start_date' => $item['start']->toDateString(),
                'end_date' => $item['end']->toDateString(),
            ]);
        }

        $this->command?->info('Demo announcements: '.count($announcements).'.');
    }
}
