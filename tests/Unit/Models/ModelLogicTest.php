<?php

namespace Tests\Unit\Models;

use App\Models\Attendance;
use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\FinancialTransaction;
use App\Models\Profile;
use App\Models\Report;
use App\Models\RewardRedemption;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Pure model logic: accessors, aliases, computed statuses (no database needed).
 */
class ModelLogicTest extends TestCase
{
    public function test_pending_payment_past_due_is_overdue(): void
    {
        $tx = new FinancialTransaction([
            'type' => 'payment', 'status' => 'pending', 'amount' => '1500000.00',
            'due_date' => Carbon::today()->subDay()->toDateString(),
        ]);

        $this->assertTrue($tx->isOverdue());
        $this->assertSame('overdue', $tx->display_status);
        $this->assertSame('Jatuh tempo', $tx->status_label);
        $this->assertSame(1500000, $tx->amount);
        $this->assertSame('Rp 1.500.000', $tx->formatted_amount);
    }

    public function test_due_today_paid_or_refund_rows_are_not_overdue(): void
    {
        $dueToday = new FinancialTransaction(['type' => 'payment', 'status' => 'pending', 'due_date' => Carbon::today()->toDateString()]);
        $paid = new FinancialTransaction(['type' => 'payment', 'status' => 'paid', 'due_date' => Carbon::today()->subMonth()->toDateString()]);
        $refund = new FinancialTransaction(['type' => 'refund', 'status' => 'pending', 'due_date' => Carbon::today()->subMonth()->toDateString()]);
        $noDue = new FinancialTransaction(['type' => 'payment', 'status' => 'pending']);

        $this->assertFalse($dueToday->isOverdue());
        $this->assertFalse($paid->isOverdue());
        $this->assertSame('paid', $paid->display_status);
        $this->assertFalse($refund->isOverdue());
        $this->assertFalse($noDue->isOverdue());
        $this->assertNull($noDue->payment_method_label);
    }

    public function test_attendance_rate_counts_present_and_late(): void
    {
        $rows = collect(['present', 'late', 'absent', 'excused'])->map(fn ($s) => new Attendance(['status' => $s]));

        $this->assertSame(50.0, Attendance::rateFor($rows));
        $this->assertSame(0.0, Attendance::rateFor([]));
        $this->assertSame('Terlambat', $rows[1]->status_label);
        $this->assertTrue($rows[1]->isAttended());
        $this->assertFalse($rows[3]->isAttended());
    }

    public function test_profile_age_child_flag_and_aliases(): void
    {
        $child = new Profile(['birth_date' => Carbon::today()->subYears(11)->toDateString(), 'phone' => '081234567890']);
        $adult = new Profile(['date_of_birth' => Carbon::today()->subYears(18)->toDateString()]);
        $unknown = new Profile;

        $this->assertSame(11, $child->age);
        $this->assertTrue($child->isChild());
        $this->assertSame('081234567890', $child->phone_number);
        $this->assertSame('081234567890', $child->phone);
        $this->assertFalse($adult->isChild());
        $this->assertSame(18, $adult->age);
        $this->assertNull($unknown->age);
        $this->assertFalse($unknown->isChild());
    }

    public function test_report_accepts_the_teacher_form_payload(): void
    {
        $report = new Report(['title' => 'Insiden', 'report_type' => 'incident', 'score' => 88, 'content' => 'x']);

        $this->assertSame('incident', $report->type);
        $this->assertSame('incident', $report->report_type);
        $this->assertSame('Insiden', $report->title);
        $this->assertSame('88.00', $report->score);
        $this->assertSame('Kejadian khusus', $report->type_label);
    }

    public function test_enrollment_and_class_labels(): void
    {
        $this->assertSame('Ditangguhkan', (new Enrollment(['status' => 'suspended']))->status_label);
        $this->assertSame(['pending', 'active'], Enrollment::SEAT_STATUSES);
        $this->assertSame('Berlangsung', (new ClassName(['status' => 'active']))->status_label);
        $this->assertSame('Kelas grup (3-5 anak)', (new ClassName(['type' => 'group_class_3_5_kids']))->type_label);
    }

    public function test_redemption_transitions(): void
    {
        $pending = new RewardRedemption(['status' => 'pending']);
        $delivered = new RewardRedemption(['status' => 'delivered']);

        $this->assertTrue($pending->canTransitionTo('approved'));
        $this->assertTrue($pending->canTransitionTo('cancelled'));
        $this->assertFalse($delivered->canTransitionTo('cancelled'));
        $this->assertFalse($delivered->canTransitionTo('approved'));
    }
}
