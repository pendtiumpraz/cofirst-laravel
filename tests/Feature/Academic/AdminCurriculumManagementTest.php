<?php

namespace Tests\Feature\Academic;

use App\Models\Curriculum;
use App\Models\Material;
use App\Models\Syllabus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin › Kurikulum / Silabus: forms that pass validation, list rendering,
 * delete guards and authorization.
 */
class AdminCurriculumManagementTest extends TestCase
{
    use AcademicFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
    }

    public function test_admin_can_create_a_curriculum_with_objectives(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.curriculums.store'), [
            'course_id' => $this->course->id,
            'title' => 'Python Fast Track',
            'type' => 'fast-track',
            'status' => 'active',
            'duration_weeks' => 4,
            'description' => 'Versi singkat.',
            'objectives' => "Memahami variabel\nMembuat fungsi",
        ]);

        $curriculum = Curriculum::where('title', 'Python Fast Track')->firstOrFail();
        $response->assertRedirect(route('admin.curriculums.show', $curriculum));
        $this->assertSame(['Memahami variabel', 'Membuat fungsi'], $curriculum->objectives_list);

        $this->actingAs($this->admin)->get(route('admin.curriculums.show', $curriculum))
            ->assertOk()
            ->assertSee('Python Fast Track')
            ->assertSee('Membuat fungsi');
    }

    public function test_curriculum_form_reports_missing_fields(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.curriculums.create'))
            ->post(route('admin.curriculums.store'), ['course_id' => $this->course->id])
            ->assertRedirect(route('admin.curriculums.create'))
            ->assertSessionHasErrors(['title', 'type', 'status']);
    }

    public function test_admin_lists_render_with_titles_and_filters(): void
    {
        $this->actingAs($this->admin)->get(route('admin.curriculums.index', ['search' => 'Reguler']))
            ->assertOk()->assertSee('Python Dasar Reguler');
        $this->actingAs($this->admin)->get(route('admin.syllabuses.index', ['curriculum_id' => $this->curriculum->id]))
            ->assertOk()->assertSee('Pertemuan nomor 2');
        $this->actingAs($this->admin)->get(route('admin.materials.index', ['type' => 'document']))
            ->assertOk();
    }

    public function test_syllabus_pages_render_list_fields_without_type_error(): void
    {
        $syllabus = $this->meetings[1];
        $syllabus->update(['learning_objectives' => ['Tujuan A', 'Tujuan B'], 'activities' => "Kegiatan A\nKegiatan B"]);

        $this->actingAs($this->admin)->get(route('admin.syllabuses.show', $syllabus))
            ->assertOk()->assertSee('Tujuan B')->assertSee('Kegiatan A')->assertSee('Materi 1.1');
        $this->actingAs($this->admin)->get(route('admin.syllabuses.edit', $syllabus))->assertOk();
    }

    public function test_admin_can_create_a_meeting_and_duplicates_are_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.syllabuses.store'), [
            'curriculum_id' => $this->curriculum->id,
            'meeting_number' => 4,
            'title' => 'Proyek akhir',
            'duration_minutes' => 90,
            'status' => 'active',
            'learning_objectives' => "Satu\nDua",
        ])->assertRedirect();

        $this->assertDatabaseHas('syllabuses', ['curriculum_id' => $this->curriculum->id, 'meeting_number' => 4, 'title' => 'Proyek akhir']);

        $this->actingAs($this->admin)->post(route('admin.syllabuses.store'), [
            'curriculum_id' => $this->curriculum->id,
            'meeting_number' => 2,
            'title' => 'Duplikat',
            'duration_minutes' => 90,
            'status' => 'active',
        ])->assertSessionHasErrors('meeting_number');
    }

    public function test_toggle_status_routes_accept_post(): void
    {
        $this->actingAs($this->admin)->post(route('admin.curriculums.toggle-status', $this->curriculum))->assertRedirect();
        $this->assertSame('inactive', $this->curriculum->fresh()->status);

        $material = $this->materialOf(1);
        $this->actingAs($this->admin)->post(route('admin.materials.toggle-status', $material))->assertRedirect();
        $this->assertSame('inactive', $material->fresh()->status);
    }

    public function test_curriculum_in_use_cannot_be_archived_but_unused_one_can(): void
    {
        $this->actingAs($this->admin)->delete(route('admin.curriculums.destroy', $this->curriculum))
            ->assertSessionHas('error');
        $this->assertNotSoftDeleted($this->curriculum);

        $unused = Curriculum::factory()->create(['course_id' => $this->course->id]);
        $meeting = Syllabus::factory()->create(['curriculum_id' => $unused->id, 'meeting_number' => 1]);

        $this->actingAs($this->admin)->delete(route('admin.curriculums.destroy', $unused))
            ->assertRedirect(route('admin.curriculums.index'));
        $this->assertSoftDeleted($unused);
        $this->assertSoftDeleted($meeting);
    }

    public function test_reported_meeting_cannot_be_archived(): void
    {
        \App\Models\ClassReport::factory()->create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->class->id,
            'student_id' => $this->student->id,
            'syllabus_id' => $this->meetings[1]->id,
        ]);

        $this->actingAs($this->admin)->delete(route('admin.syllabuses.destroy', $this->meetings[1]))
            ->assertSessionHas('error');
        $this->assertNotSoftDeleted($this->meetings[1]);
    }

    public function test_materials_can_be_reordered(): void
    {
        [$first, $second] = $this->meetings[2]->materials()->orderBy('order')->get()->all();

        $this->actingAs($this->admin)->post(route('admin.materials.move', $second), ['direction' => 'up'])->assertRedirect();

        $this->assertSame(1, $second->fresh()->order);
        $this->assertSame(2, $first->fresh()->order);
    }

    public function test_non_admin_cannot_manage_curricula(): void
    {
        $this->actingAs($this->teacher)->get(route('admin.curriculums.index'))->assertForbidden();
        $this->actingAs($this->teacher)->post(route('admin.curriculums.store'), [])->assertForbidden();
        $this->actingAs($this->student)->get(route('admin.syllabuses.create'))->assertForbidden();
    }

    public function test_json_endpoints_use_the_route_parameter(): void
    {
        $this->actingAs($this->admin)->getJson(route('admin.syllabuses.by-curriculum', $this->curriculum))
            ->assertOk()->assertJsonCount(3);
        $this->actingAs($this->admin)->getJson(route('admin.materials.by-syllabus', $this->meetings[1]))
            ->assertOk()->assertJsonCount(2);
    }

    public function test_material_list_shows_no_raw_html_from_content(): void
    {
        $material = $this->materialOf(1);
        $material->update(['content' => "# Judul\n\n<script>alert('x')</script>\n\n[klik](javascript:alert(1))"]);

        $this->actingAs($this->admin)->get(route('admin.materials.show', $material))
            ->assertOk()
            ->assertDontSee("<script>alert('x')</script>", false)
            ->assertDontSee('javascript:alert(1)', false);
    }

    public function test_material_types_have_indonesian_labels(): void
    {
        $this->assertSame('Dokumen', Material::getTypes()['document']);
    }
}
