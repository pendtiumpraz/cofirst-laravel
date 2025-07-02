<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class FinancialTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create(); // Inisialisasi Faker

        $enrollments = Enrollment::with('student', 'class.course')->get();

        foreach ($enrollments as $enrollment) {
            FinancialTransaction::factory()->create([
                'student_id' => $enrollment->student_id,
                'course_id' => $enrollment->class->course->id,
                'amount' => $enrollment->class->course->price,
                'transaction_date' => now(),
                'payment_method' => $faker->randomElement(['cash', 'transfer', 'card', 'ewallet']), // Menggunakan $faker
                'transaction_reference' => $faker->uuid(), // Menggunakan $faker
                'status' => 'paid',
                'notes' => 'Pembayaran lunas untuk kursus ' . $enrollment->class->course->name,
            ]);
        }
    }
}
