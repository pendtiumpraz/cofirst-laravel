<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\FinancialTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Admin\Concerns\AdminTestHelpers;
use Tests\TestCase;

/**
 * Admin › Kursus: fields that persist, unique slugs, stats and archiving rules.
 */
class CourseManagementTest extends TestCase
{
    use AdminTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Python Dasar',
            'description' => 'Belajar Python dari nol untuk remaja.',
            'level' => 'beginner',
            'price' => '1.500.000',
            'duration_weeks' => 12,
            'duration_hours' => 18,
            'benefits' => "Sertifikat\nMentor berpengalaman",
            'is_active' => '1',
        ], $overrides);
    }

    public function test_course_is_created_with_duration_and_price(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.courses.store'), $this->payload())->assertSessionHasNoErrors();

        $course = Course::where('name', 'Python Dasar')->firstOrFail();
        $this->assertSame(1_500_000, $course->price_rupiah);
        $this->assertSame(12, $course->duration_weeks);
        $this->assertSame(18, $course->duration_hours);
        $this->assertSame('python-dasar', $course->slug);

        $this->actingAs($admin)->get(route('admin.courses.index'))->assertOk()->assertSee('Python Dasar');
        $this->actingAs($admin)->get(route('admin.courses.show', $course))->assertOk()->assertSee('Rp 1.500.000');
        $this->actingAs($admin)->get(route('admin.courses.edit', $course))->assertOk();
    }

    public function test_duplicate_names_get_unique_slugs_instead_of_an_error(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.courses.store'), $this->payload())->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.courses.store'), $this->payload())->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing(['python-dasar', 'python-dasar-2'], Course::pluck('slug')->all());

        $this->actingAs($admin)->post(route('admin.courses.store'), $this->payload(['slug' => 'python-dasar']))
            ->assertSessionHasErrors('slug');
    }

    public function test_show_page_uses_real_statistics(): void
    {
        $admin = $this->admin();
        $class = $this->activeClass(5);
        $this->enroll($class);
        FinancialTransaction::factory()->paid()->create(['course_id' => $class->course_id, 'amount' => 2_400_000]);

        $this->actingAs($admin)->get(route('admin.courses.show', $class->course))
            ->assertOk()->assertSee('Rp 2,4 jt')->assertSee($class->name);
    }

    public function test_archive_rules(): void
    {
        $super = $this->superadmin();
        $class = $this->activeClass(5);

        $this->actingAs($super)->delete(route('admin.courses.destroy', $class->course))->assertSessionHas('error');
        $this->assertNotSoftDeleted('courses', ['id' => $class->course_id]);

        $free = Course::factory()->create();
        $this->actingAs($super)->delete(route('admin.courses.destroy', $free))->assertRedirect(route('admin.courses.index'));
        $this->assertSoftDeleted('courses', ['id' => $free->id]);

        $this->actingAs($super)->post(route('admin.courses.restore', $free->id))->assertRedirect();
        $this->assertNotSoftDeleted('courses', ['id' => $free->id]);
    }

    public function test_toggle_and_permissions(): void
    {
        $admin = $this->admin();
        $course = Course::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->post(route('admin.courses.toggle-status', $course))->assertRedirect();
        $this->assertFalse($course->fresh()->is_active);

        // Admins have no "delete courses" permission by default; students no access at all.
        $this->actingAs($admin)->delete(route('admin.courses.destroy', $course))->assertForbidden();
        $this->actingAs($this->student())->get(route('admin.courses.index'))->assertForbidden();
    }
}
