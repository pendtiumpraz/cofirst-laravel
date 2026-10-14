<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use Database\Seeders\Data\DemoBlueprint;
use Database\Seeders\Data\DemoContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Enroll every demo student in 1-3 classes (see DemoBlueprint::enrollments()).
 *
 * Statuses agree with the class status; one dropped (refunded) and one cancelled
 * registration exist. Capacity (max_students) is checked before saving; the
 * natural key (class_id, student_id) makes re-running idempotent.
 */
class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = DemoBlueprint::classes();
        $exceptions = DemoBlueprint::paymentExceptions();
        $count = 0;

        foreach (DemoBlueprint::enrollments() as $studentKey => $classes) {
            $student = DemoContext::user($studentKey);

            foreach ($classes as $code => $status) {
                $class = DemoContext::classByCode($code);
                $plan = $plans[$code];

                $existing = Enrollment::where('class_id', $class->id)->where('student_id', $student->id)->first();
                $takesSeat = in_array($status, Enrollment::SEAT_STATUSES, true);

                if ($takesSeat && ! $existing && ! $class->hasCapacity()) {
                    $this->command?->warn("Kelas {$class->name} penuh, {$student->name} dilewati.");

                    continue;
                }

                Enrollment::updateOrCreate(
                    ['class_id' => $class->id, 'student_id' => $student->id],
                    [
                        'course_id' => $class->course_id,
                        'enrollment_date' => $this->enrollmentDate($plan, $status, $studentKey)->toDateString(),
                        'status' => $status,
                        'payment_status' => $this->paymentStatus($plan, $status, $exceptions["{$studentKey}:{$code}"] ?? null),
                        'notes' => $this->notes($status),
                        'is_active' => in_array($status, Enrollment::SEAT_STATUSES, true),
                    ]
                );
                $count++;
            }
        }

        $this->command?->info("Demo enrollments: {$count}.");
    }

    /**
     * Registration date: 1-2 weeks before the first meeting, or recently for
     * upcoming classes.
     */
    private function enrollmentDate(array $plan, string $status, string $studentKey): Carbon
    {
        $first = DemoContext::firstMeeting($plan);

        if ($first->isFuture() || in_array($status, ['pending', 'cancelled'], true)) {
            return Carbon::today()->subDays(DemoContext::pick($studentKey.$plan['name'], 10) + 1);
        }

        return $first->copy()->subDays(7 + DemoContext::pick($studentKey.$plan['name'], 8));
    }

    /**
     * payment_status consistent with the invoices FinancialTransactionSeeder creates.
     */
    private function paymentStatus(array $plan, string $status, ?string $exception): string
    {
        return match (true) {
            ! empty($plan['free']) => 'paid',
            $status === 'dropped' => 'refunded',
            in_array($status, ['pending', 'cancelled'], true) => 'unpaid',
            $exception === 'overdue' => 'unpaid',
            in_array($exception, ['pending', 'second_installment_pending'], true) => 'pending',
            default => 'paid',
        };
    }

    /**
     * Admin note for unusual statuses.
     */
    private function notes(string $status): ?string
    {
        return match ($status) {
            'dropped' => 'Mengundurkan diri setelah pertemuan ke-2; biaya dikembalikan.',
            'cancelled' => 'Pendaftaran dibatalkan sebelum kelas dimulai.',
            'pending' => 'Menunggu pembayaran dan konfirmasi admin.',
            default => null,
        };
    }
}
