<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialTransaction>
 *
 * States: paid(), pending(), overdue(), cancelled(), refundOf($payment).
 */
class FinancialTransactionFactory extends Factory
{
    protected $model = FinancialTransaction::class;

    /**
     * Define the model's default state (a pending invoice due in a week).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->student(),
            'course_id' => Course::factory(),
            'amount' => $this->faker->numberBetween(5, 35) * 100000,
            'type' => FinancialTransaction::TYPE_PAYMENT,
            'status' => FinancialTransaction::STATUS_PENDING,
            'payment_method' => $this->faker->randomElement(['transfer', 'qris', 'ewallet', 'cash']),
            'transaction_date' => now(),
            'due_date' => now()->addWeek()->toDateString(),
            'transaction_reference' => 'TRX-'.$this->faker->unique()->numerify('##########'),
            'description' => 'Biaya kursus',
            'notes' => null,
        ];
    }

    /** A settled payment. */
    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => FinancialTransaction::STATUS_PAID,
            'paid_date' => now(),
        ]);
    }

    /** A pending invoice. */
    public function pending(): static
    {
        return $this->state(fn () => ['status' => FinancialTransaction::STATUS_PENDING, 'paid_date' => null]);
    }

    /** A pending invoice whose due date has passed. */
    public function overdue(): static
    {
        return $this->state(fn () => [
            'status' => FinancialTransaction::STATUS_PENDING,
            'transaction_date' => now()->subMonth(),
            'due_date' => now()->subWeek()->toDateString(),
            'paid_date' => null,
        ]);
    }

    /** A cancelled invoice. */
    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => FinancialTransaction::STATUS_CANCELLED, 'paid_date' => null]);
    }

    /**
     * A paid refund row for the given payment.
     */
    public function refundOf(FinancialTransaction $payment): static
    {
        return $this->state(fn () => [
            'student_id' => $payment->student_id,
            'course_id' => $payment->course_id,
            'refund_of_id' => $payment->id,
            'amount' => $payment->amount,
            'type' => FinancialTransaction::TYPE_REFUND,
            'status' => FinancialTransaction::STATUS_PAID,
            'paid_date' => now(),
            'description' => 'Pengembalian dana',
        ]);
    }
}
