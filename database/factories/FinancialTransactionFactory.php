<?php

namespace Database\Factories;

use App\Models\FinancialTransaction;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class FinancialTransactionFactory extends Factory
{
    protected $model = FinancialTransaction::class;

    public function definition()
    {
        return [
            'student_id' => User::factory()->create()->assignRole('student'), // Diperbaiki dari user_id
            'course_id' => Course::factory(),
            'amount' => $this->faker->numberBetween(100000, 500000),
            'transaction_date' => $this->faker->date(), // Kolom baru
            'payment_method' => $this->faker->randomElement(['cash', 'transfer', 'card', 'ewallet']), // Kolom baru
            'transaction_reference' => $this->faker->uuid(), // Kolom baru
            'status' => $this->faker->randomElement(['pending', 'paid', 'failed', 'refunded']),
            'notes' => $this->faker->sentence,
            // 'processed_by' dihapus karena tidak ada di migrasi
        ];
    }
}