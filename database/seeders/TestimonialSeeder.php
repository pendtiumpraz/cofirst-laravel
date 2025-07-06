<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Testimonial;
use App\Models\User;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all parent users
        $parents = User::role('parent')->get();
        
        if ($parents->count() === 0) {
            $this->command->info('No parent users found. Skipping testimonial seeding.');
            return;
        }
        
        $testimonials = [
            [
                'title' => 'Anak Saya Jadi Lebih Kreatif',
                'content' => 'Sejak bergabung di Coding First, anak saya menjadi lebih kreatif dan logical dalam berpikir. Dia sangat antusias setiap kali ada kelas programming. Terima kasih Coding First!',
                'rating' => 5,
                'child_name' => 'Andi Pratama',
                'child_class' => 'Web Development Basic',
                'is_featured' => true,
            ],
            [
                'title' => 'Metode Pembelajaran yang Menyenangkan',
                'content' => 'Guru-guru di Coding First sangat sabar dan metode pembelajarannya fun. Anak saya yang tadinya takut dengan coding, sekarang malah suka banget. Progressnya terlihat jelas.',
                'rating' => 5,
                'child_name' => 'Sarah Putri',
                'child_class' => 'Mobile App Development',
                'is_featured' => true,
            ],
            [
                'title' => 'Investasi Terbaik untuk Masa Depan',
                'content' => 'Di era digital ini, kemampuan programming sangat penting. Coding First memberikan fondasi yang kuat untuk anak saya. Kurikulumnya terstruktur dan up-to-date.',
                'rating' => 5,
                'child_name' => 'Rizky Ahmad',
                'child_class' => 'Python Programming',
                'is_featured' => true,
            ],
            [
                'title' => 'Perubahan yang Signifikan',
                'content' => 'Dalam 3 bulan, anak saya sudah bisa membuat website sederhana sendiri. Saya sangat puas dengan progress yang dicapai. Support dari guru juga luar biasa.',
                'rating' => 5,
                'child_name' => 'Maya Sari',
                'child_class' => 'Full Stack Development',
                'is_featured' => true,
            ],
            [
                'title' => 'Komunikasi yang Baik',
                'content' => 'Yang saya suka dari Coding First adalah komunikasinya. Saya selalu dapat update tentang perkembangan anak saya. Ada laporan berkala yang detail.',
                'rating' => 4,
                'child_name' => 'Dimas Prasetyo',
                'child_class' => 'Game Development',
                'is_featured' => true,
            ],
            [
                'title' => 'Lingkungan Belajar yang Positif',
                'content' => 'Anak saya merasa nyaman belajar di Coding First. Teman-temannya supportive dan gurunya encouraging. Ini penting untuk membangun confidence dalam belajar coding.',
                'rating' => 5,
                'child_name' => 'Alya Nabila',
                'child_class' => 'UI/UX Design & Coding',
                'is_featured' => true,
            ],
        ];
        
        foreach ($testimonials as $index => $testimonialData) {
            // Assign to a random parent
            $parent = $parents->random();
            
            Testimonial::create([
                'user_id' => $parent->id,
                'title' => $testimonialData['title'],
                'content' => $testimonialData['content'],
                'rating' => $testimonialData['rating'],
                'child_name' => $testimonialData['child_name'],
                'child_class' => $testimonialData['child_class'],
                'is_active' => true,
                'is_featured' => $testimonialData['is_featured'],
            ]);
        }
        
        $this->command->info('Testimonials seeded successfully!');
    }
}