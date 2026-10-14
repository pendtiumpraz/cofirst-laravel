<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Database\Seeders\Data\DemoContext;
use Illuminate\Database\Seeder;

/**
 * Demo testimonials written by demo parents about their own (fictional) child
 * and a class the child really attends; four are featured. Demo-only: never run
 * in production (fabricated reviews). Keyed by (user_id, title).
 */
class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['dewi', 'raka', 'PY-TEEN', 'Anak Saya Jadi Lebih Kreatif', 'Sejak belajar di Coding First, Raka lebih kreatif dan runtut dalam berpikir. Dia selalu antusias menunggu kelas.', 5, true],
            ['hendra', 'clarissa', 'SCR-A', 'Metode Belajar yang Menyenangkan', 'Gurunya sabar dan cara mengajarnya seru. Clarissa yang tadinya takut coding sekarang malah ketagihan.', 5, true],
            ['agus', 'bima', 'LEGO-HOL', 'Liburan Jadi Produktif', 'Kelas Lego Spike saat liburan membuat Bima sibuk merakit robot daripada main gawai seharian.', 5, true],
            ['ratna', 'alya', 'MB-SCH', 'Laporan Perkembangan Jelas', 'Saya selalu dapat laporan perkembangan yang detail setiap pertemuan. Komunikasinya sangat baik.', 4, true],
            ['putri', 'naura', 'SCR-B', 'Kelas Online Tetap Interaktif', 'Walaupun online, Naura tetap aktif bertanya dan mentornya responsif.', 4, false],
            ['rudi', 'gilang', 'PY-TEEN', 'Persiapan Masa Depan', 'Gilang mulai serius belajar Python untuk persiapan kuliah. Kurikulumnya terstruktur.', 5, false],
        ];

        foreach ($items as [$parentKey, $childKey, $classCode, $title, $content, $rating, $featured]) {
            Testimonial::updateOrCreate(
                ['user_id' => DemoContext::user($parentKey)->id, 'title' => $title],
                [
                    'content' => $content,
                    'rating' => $rating,
                    // Privacy by default: first name only.
                    'child_name' => strtok(DemoContext::user($childKey)->name, ' '),
                    'child_class' => DemoContext::classByCode($classCode)->name,
                    'is_active' => true,
                    'is_featured' => $featured,
                ]
            );
        }

        $this->command?->info('Demo testimonials: '.count($items).'.');
    }
}
