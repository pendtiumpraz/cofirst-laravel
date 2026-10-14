<?php

namespace Database\Seeders;

use App\Models\ClassPhoto;
use App\Models\ProjectGallery;
use App\Models\TeacherHandover;
use Database\Seeders\Data\DemoBlueprint;
use Database\Seeders\Data\DemoContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Demo project gallery entries, class photo records (no image files: photo_path
 * is NULL) and two teacher handover requests. Keyed by natural keys.
 */
class ProjectGallerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            ['raka', 'LEGO-HOL', 'Robot Penyapu Meja', 'Robot Lego Spike yang berhenti otomatis saat mendeteksi tepi meja.', true],
            ['raka', 'SCR-A', 'Game Kejar Kucing', 'Game Scratch: tangkap tikus sebanyak mungkin dalam 60 detik.', true],
            ['nadia', 'SCR-JUL', 'Kartu Ucapan Animasi', 'Kartu ulang tahun animasi dengan musik buatan sendiri.', false],
            ['bima', 'LEGO-HOL', 'Robot Pengikut Garis', 'Robot yang mengikuti garis hitam memakai sensor warna.', true],
            ['kevin', 'PY-TEEN', 'Kalkulator Nilai Rapor', 'Program Python untuk menghitung rata-rata nilai.', false],
            ['salsa', 'LEGO-HOL', 'Lengan Robot Pengambil Bola', 'Lengan robot dengan gir untuk mengambil bola kecil.', false],
            ['dian', 'PY-MEI', 'Aplikasi Daftar Tugas', 'Aplikasi konsol pencatat tugas harian dengan Python.', true],
            ['galang', 'PY-MEI', 'Bot Pengingat Minum Air', 'Skrip Python yang mengingatkan minum air setiap jam.', false],
        ];

        foreach ($projects as $index => [$studentKey, $classCode, $title, $description, $featured]) {
            ProjectGallery::updateOrCreate(
                ['student_id' => DemoContext::user($studentKey)->id, 'title' => $title],
                [
                    'class_id' => DemoContext::classByCode($classCode)->id,
                    'description' => $description,
                    'photo_path' => null,
                    'thumbnail_path' => null,
                    'is_featured' => $featured,
                    'views' => 15 + DemoContext::pick($title, 180),
                ]
            );
        }

        $photos = [
            ['SCR-A', 'budi', 'Presentasi game Scratch pertama', 2],
            ['LEGO-A', 'andi', 'Uji coba robot di arena', 3],
            ['SCR-JUL', 'budi', 'Foto bersama kelulusan Batch Juli', 8],
            ['LEGO-HOL', 'andi', 'Kompetisi mini robot liburan', 8],
        ];

        foreach ($photos as [$classCode, $teacherKey, $caption, $meeting]) {
            $date = DemoContext::meetingDates(DemoBlueprint::classes()[$classCode])[$meeting];

            ClassPhoto::updateOrCreate(
                ['class_id' => DemoContext::classByCode($classCode)->id, 'caption' => $caption],
                [
                    'title' => $caption,
                    'description' => 'Dokumentasi kegiatan kelas (foto demo tanpa berkas).',
                    'date_taken' => $date->toDateString(),
                    'uploaded_by' => DemoContext::user($teacherKey)->id,
                    'photo_path' => null,
                ]
            );
        }

        $this->handovers();

        $this->command?->info('Demo gallery: '.count($projects).' projects, '.count($photos).' class photos, 2 handovers.');
    }

    /**
     * One pending temporary handover and one approved permanent one.
     */
    private function handovers(): void
    {
        $today = Carbon::today();

        TeacherHandover::updateOrCreate(
            ['class_id' => DemoContext::classByCode('PY-TEEN')->id, 'from_teacher_id' => DemoContext::user('budi')->id, 'type' => TeacherHandover::TYPE_TEMPORARY],
            [
                'to_teacher_id' => DemoContext::user('rizky')->id,
                'student_id' => null,
                'effective_from' => $today->copy()->addWeek()->toDateString(),
                'effective_until' => $today->copy()->addWeeks(2)->toDateString(),
                'reason' => 'Pak Budi mengikuti pelatihan di luar kota selama dua minggu.',
                'handover_notes' => 'Kelas sudah sampai materi fungsi. Lanjutkan dengan latihan OOP sesuai modul.',
                'last_meeting_number' => DemoContext::pastMeetings(DemoBlueprint::classes()['PY-TEEN'])->keys()->last(),
                'status' => TeacherHandover::STATUS_PENDING,
                'requested_by' => DemoContext::user('budi')->id,
                'approved_by' => null,
                'approved_at' => null,
            ]
        );

        TeacherHandover::updateOrCreate(
            ['class_id' => DemoContext::classByCode('RBX-GM')->id, 'from_teacher_id' => DemoContext::user('sari')->id, 'type' => TeacherHandover::TYPE_PERMANENT],
            [
                'to_teacher_id' => DemoContext::user('fajar')->id,
                'student_id' => DemoContext::user('dimas')->id,
                'effective_from' => $today->copy()->subWeek()->toDateString(),
                'effective_until' => null,
                'reason' => 'Dimas pindah ke bimbingan Pak Fajar untuk proyek game lanjutan.',
                'handover_notes' => 'Dimas sudah menguasai dasar Lua dan event Touched.',
                'last_meeting_number' => 2,
                'status' => TeacherHandover::STATUS_APPROVED,
                'requested_by' => DemoContext::user('sari')->id,
                'approved_by' => DemoContext::user('admin')->id,
                'approved_at' => $today->copy()->subDays(8),
            ]
        );
    }
}
