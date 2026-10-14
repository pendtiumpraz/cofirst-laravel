<?php

namespace Tests\Feature\Admin;

use App\Models\FinancialTransaction;
use App\Models\Schedule;
use App\Models\User;
use App\Services\Admin\AdminClock;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\Feature\Admin\Concerns\AdminTestHelpers;
use Tests\TestCase;

/**
 * Admin cockpit, teacher performance, /dashboard routing and the shared
 * schedule-calendar component.
 */
class AdminDashboardTest extends TestCase
{
    use AdminTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_cockpit_renders_with_real_figures(): void
    {
        $admin = $this->admin(['name' => 'Rina Admin']);
        $class = $this->activeClass(5);
        $enrollment = $this->enroll($class);
        Schedule::factory()->create(['class_id' => $class->id, 'enrollment_id' => $enrollment->id, 'day_of_week' => AdminClock::isoWeekday()]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Rina')
            ->assertSee($class->name)
            ->assertSee('Pendapatan bulan ini');
    }

    public function test_revenue_counts_settled_payments_minus_refunds_only(): void
    {
        $payment = FinancialTransaction::factory()->paid()->create(['amount' => 2_000_000, 'paid_date' => now()]);
        FinancialTransaction::factory()->pending()->create(['amount' => 9_000_000]);
        FinancialTransaction::factory()->refundOf($payment)->create(['amount' => 500_000, 'paid_date' => now(), 'status' => 'paid']);
        FinancialTransaction::factory()->overdue()->create(['amount' => 750_000]);

        $kpis = app(AdminDashboardService::class)->build()['kpis'];

        $this->assertSame(1_500_000, $kpis['revenue_month']);
        $this->assertSame(1, $kpis['overdue_count']);
        $this->assertSame(750_000, $kpis['overdue_amount']);
    }

    public function test_empty_school_dashboard_still_renders(): void
    {
        $this->actingAs($this->superadmin())->get(route('admin.dashboard'))
            ->assertOk()->assertSee('Tidak ada kelas terjadwal hari ini.');
    }

    public function test_teacher_performance_page(): void
    {
        $teacher = $this->teacher(['name' => 'Kenji Sato']);
        $this->enroll($this->activeClass(5, $teacher));

        $this->actingAs($this->admin())->get(route('admin.teachers.ranking', ['period' => 'quarter', 'sort' => 'students']))
            ->assertOk()->assertSee('Kenji Sato');
    }

    public function test_dashboard_redirect_follows_active_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('parent', 'student');

        $this->actingAs($user)->withSession(['active_role' => 'student'])->get('/dashboard')
            ->assertRedirect(route('student.dashboard'));
        $this->actingAs($this->admin())->get('/dashboard')->assertRedirect(route('admin.dashboard'));
    }

    public function test_user_without_role_is_signed_out_instead_of_looping(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_non_admins_cannot_open_the_cockpit(): void
    {
        $this->actingAs($this->teacher())->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($this->parentUser())->get(route('admin.teachers.ranking'))->assertForbidden();
    }

    public function test_schedule_calendar_component_dedupes_group_rows(): void
    {
        $class = $this->activeClass(5);
        foreach (range(1, 3) as $i) {
            Schedule::factory()->create([
                'class_id' => $class->id, 'enrollment_id' => $this->enroll($class)->id,
                'day_of_week' => AdminClock::isoWeekday(), 'start_time' => '16:00:00', 'end_time' => '17:30:00', 'room' => 'Lab 1',
            ]);
        }

        $html = Blade::render('<x-schedule-calendar :schedules="$rows" show-students />', ['rows' => Schedule::all()]);

        $this->assertSame(1, substr_count($html, e($class->name)));
        $this->assertStringContainsString('3 siswa', $html);
        $this->assertStringContainsString('Hari ini', $html);
    }
}
