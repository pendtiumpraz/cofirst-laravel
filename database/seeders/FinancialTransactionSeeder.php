<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\FinancialTransaction;
use Database\Seeders\Data\DemoBlueprint;
use Database\Seeders\Data\DemoContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Invoices and payments (whole rupiah) for the demo enrollments, covering about
 * the last six months: paid, pending, overdue (pending with a past due date),
 * a failed attempt, a cancelled registration and a refunded drop-out.
 *
 * Courses of Rp 2.800.000 or more are paid in two installments four weeks
 * apart. Children's invoices are paid by their parent (payer_id). Keyed by a
 * deterministic transaction_reference, so re-running updates the same rows.
 */
class FinancialTransactionSeeder extends Seeder
{
    private const METHODS = ['transfer', 'qris', 'virtual_account', 'ewallet', 'transfer', 'cash', 'card'];

    private int $sequence = 0;

    private int $financeId;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->financeId = DemoContext::user('finance')->id;
        $plans = DemoBlueprint::classes();
        $exceptions = DemoBlueprint::paymentExceptions();

        foreach (DemoBlueprint::enrollments() as $studentKey => $classes) {
            foreach ($classes as $code => $status) {
                if (! empty($plans[$code]['free'])) {
                    continue;
                }

                $class = DemoContext::classByCode($code);
                $enrollment = Enrollment::where('class_id', $class->id)
                    ->where('student_id', DemoContext::user($studentKey)->id)->first();

                if ($enrollment) {
                    $this->invoicesFor($enrollment, $studentKey, $code, $plans[$code], $exceptions["{$studentKey}:{$code}"] ?? null);
                }
            }
        }

        $this->command?->info("Demo financial transactions: {$this->sequence}.");
    }

    /**
     * Create the invoice rows of one enrollment.
     */
    private function invoicesFor(Enrollment $enrollment, string $studentKey, string $code, array $plan, ?string $exception): void
    {
        $price = (int) round((float) $enrollment->className->course->price);
        $installments = $price >= 2800000 && $plan['meetings'] > 1 ? 2 : 1;
        $part = (int) (round($price / $installments / 1000) * 1000);
        $start = $enrollment->enrollment_date->copy();
        $today = Carbon::today();

        for ($i = 1; $i <= $installments; $i++) {
            $amount = $i === $installments ? $price - $part * ($installments - 1) : $part;
            $issued = $start->copy()->addDays(28 * ($i - 1));
            $due = $issued->copy()->addDays(7);
            $ref = "DEMO-{$studentKey}-{$code}-{$i}";
            $label = $installments > 1 ? " (cicilan {$i}/{$installments})" : '';
            $row = ['amount' => $amount, 'transaction_date' => $issued, 'due_date' => $due, 'description' => "Biaya kursus {$enrollment->className->course->name} — {$enrollment->className->name}{$label}"];

            if ($enrollment->status === Enrollment::STATUS_CANCELLED) {
                $this->save($enrollment, $studentKey, $ref, $row + ['status' => FinancialTransaction::STATUS_CANCELLED, 'notes' => 'Pendaftaran dibatalkan sebelum kelas dimulai.']);
                break;
            }

            if ($enrollment->status === Enrollment::STATUS_PENDING) {
                $due = DemoContext::firstMeeting($plan)->subDays(2);
                $this->save($enrollment, $studentKey, $ref, ['due_date' => $due->max($today->copy()->addDay())] + $row + ['status' => FinancialTransaction::STATUS_PENDING]);
                break;
            }

            if ($enrollment->status === Enrollment::STATUS_DROPPED) {
                $payment = $this->save($enrollment, $studentKey, $ref, $row + ['status' => FinancialTransaction::STATUS_REFUNDED, 'paid_date' => $issued->copy()->addDays(2)]);
                $refundDate = DemoContext::firstMeeting($plan)->addWeeks(2)->addDays(3)->min($today);
                $this->save($enrollment, $studentKey, $ref.'-REFUND', [
                    'amount' => $amount,
                    'type' => FinancialTransaction::TYPE_REFUND,
                    'refund_of_id' => $payment->id,
                    'status' => FinancialTransaction::STATUS_PAID,
                    'transaction_date' => $refundDate,
                    'due_date' => null,
                    'paid_date' => $refundDate,
                    'description' => 'Pengembalian dana: siswa mengundurkan diri',
                ]);
                break;
            }

            if ($exception === 'failed_then_paid' && $i === 1) {
                $this->save($enrollment, $studentKey, $ref.'-FAILED', $row + ['status' => FinancialTransaction::STATUS_FAILED, 'payment_method' => 'card', 'notes' => 'Pembayaran kartu ditolak bank.']);
            }

            $unpaid = match (true) {
                $exception === 'overdue' && $i === $installments => 'overdue',
                $exception === 'pending' && $i === $installments => 'pending',
                $exception === 'second_installment_pending' && $i === 2 => 'pending',
                $issued->gt($today) => 'pending',
                default => null,
            };

            if ($unpaid === 'pending') {
                $issued = $issued->min($today->copy()->subDays(2));
                $this->save($enrollment, $studentKey, $ref, ['transaction_date' => $issued, 'due_date' => $today->copy()->addDays(5)] + $row + ['status' => FinancialTransaction::STATUS_PENDING]);
            } elseif ($unpaid === 'overdue') {
                $this->save($enrollment, $studentKey, $ref, $row + ['status' => FinancialTransaction::STATUS_PENDING, 'notes' => 'Sudah diingatkan via WhatsApp.']);
            } else {
                $paid = $issued->copy()->addDays(DemoContext::pick($ref, 6))->min($today->copy());
                $this->save($enrollment, $studentKey, $ref, $row + ['status' => FinancialTransaction::STATUS_PAID, 'paid_date' => $paid->setTime(10, 0)]);
            }
        }
    }

    /**
     * Insert or update one transaction by its reference.
     */
    private function save(Enrollment $enrollment, string $studentKey, string $reference, array $attributes): FinancialTransaction
    {
        $this->sequence++;
        $parentKey = DemoContext::parentKeyOf($studentKey);
        $paid = ($attributes['status'] ?? null) === FinancialTransaction::STATUS_PAID;

        $transaction = FinancialTransaction::withTrashed()->updateOrCreate(
            ['transaction_reference' => $reference],
            array_merge([
                'student_id' => $enrollment->student_id,
                'course_id' => $enrollment->course_id,
                'enrollment_id' => $enrollment->id,
                'payer_id' => $parentKey ? DemoContext::user($parentKey)->id : null,
                'invoice_number' => sprintf('INV-%05d', $this->sequence),
                'type' => FinancialTransaction::TYPE_PAYMENT,
                'payment_method' => self::METHODS[DemoContext::pick($reference, count(self::METHODS))],
                'paid_date' => null,
                'notes' => null,
                'refund_of_id' => null,
                'recorded_by' => $this->financeId,
                'verified_by' => $paid ? $this->financeId : null,
                'verified_at' => $paid ? ($attributes['paid_date'] ?? null) : null,
            ], $attributes)
        );

        if ($transaction->trashed()) {
            $transaction->restore();
        }

        return $transaction;
    }
}
