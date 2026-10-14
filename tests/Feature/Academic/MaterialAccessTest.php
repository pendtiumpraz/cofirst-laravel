<?php

namespace Tests\Feature\Academic;

use App\Models\Material;
use App\Models\MaterialAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Material files live on the private disk and open only for the right people;
 * students see a material once its meeting is unlocked; opens are recorded sparingly.
 */
class MaterialAccessTest extends TestCase
{
    use AcademicFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_admin_upload_goes_to_the_private_disk_with_a_random_name(): void
    {
        $this->actingAs($this->admin)->post(route('admin.materials.store'), [
            'syllabus_id' => $this->meetings[1]->id,
            'title' => 'Lembar Kerja Variabel',
            'type' => 'document',
            'status' => 'active',
            'external_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'file' => UploadedFile::fake()->create('lembar kerja.pdf', 120, 'application/pdf'),
        ])->assertRedirect(route('admin.syllabuses.show', $this->meetings[1]));

        $material = Material::where('title', 'Lembar Kerja Variabel')->firstOrFail();

        $this->assertStringStartsWith('materials/', $material->file_path);
        $this->assertStringNotContainsString('lembar', $material->file_path);
        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $material->external_url);
        $this->assertSame(1, $material->meeting_start);
        Storage::disk('local')->assertExists($material->file_path);
        Storage::disk('public')->assertMissing($material->file_path);

        $this->actingAs($this->admin)->get(route('admin.materials.download', $material))
            ->assertOk()
            ->assertDownload('lembar-kerja-variabel.pdf');
    }

    public function test_material_range_must_start_at_its_meeting(): void
    {
        $this->actingAs($this->admin)->post(route('admin.materials.store'), [
            'syllabus_id' => $this->meetings[1]->id,
            'title' => 'Salah rentang',
            'type' => 'document',
            'status' => 'active',
            'content' => 'Isi',
            'meeting_start' => 3,
            'meeting_end' => 3,
        ])->assertSessionHasErrors('meeting_start');
    }

    public function test_disallowed_file_types_are_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.materials.store'), [
            'syllabus_id' => $this->meetings[1]->id,
            'title' => 'Skrip',
            'type' => 'document',
            'status' => 'active',
            'file' => UploadedFile::fake()->create('evil.svg', 5, 'image/svg+xml'),
        ])->assertSessionHasErrors('file');
    }

    public function test_student_cannot_open_a_locked_material(): void
    {
        $this->actingAs($this->student)->get(route('student.materials.show', $this->materialOf(2)))->assertForbidden();
    }

    public function test_completed_meeting_unlocks_material_and_records_one_visit_per_window(): void
    {
        $this->completeMeeting(1);
        $material = $this->materialOf(1);

        $this->actingAs($this->student)->get(route('student.materials.show', $material))
            ->assertOk()->assertSee($material->title);
        $this->actingAs($this->student)->get(route('student.materials.show', $material))->assertOk();

        $this->assertSame(1, MaterialAccess::where('student_id', $this->student->id)->where('material_id', $material->id)->count());
        $this->actingAs($this->student)->get(route('student.materials.show', $this->materialOf(2)))->assertForbidden();
    }

    public function test_inactive_material_stays_closed_even_when_unlocked(): void
    {
        $this->completeMeeting(1);
        $material = $this->materialOf(1);
        $material->update(['status' => 'inactive']);

        $this->actingAs($this->student)->get(route('student.materials.show', $material))->assertForbidden();
    }

    public function test_student_download_requires_enrollment_and_unlock(): void
    {
        $material = $this->materialOf(1);
        Storage::disk('local')->put('materials/abc.pdf', '%PDF-1.4');
        $material->update(['file_path' => 'materials/abc.pdf']);

        $this->actingAs($this->student)->get(route('student.materials.download', $material))->assertForbidden();

        $this->completeMeeting(1);
        $this->actingAs($this->student)->get(route('student.materials.download', $material))->assertOk();

        $outsider = \App\Models\User::factory()->student()->create();
        $this->actingAs($outsider)->get(route('student.materials.download', $material))->assertForbidden();
    }

    public function test_legacy_public_files_are_moved_to_the_private_disk_on_access(): void
    {
        $material = $this->materialOf(1);
        Storage::disk('public')->put('materials/1700000000_lama.pdf', '%PDF-1.4');
        $material->update(['file_path' => 'materials/1700000000_lama.pdf']);

        $this->actingAs($this->teacher)->get(route('teacher.materials.download', $material))->assertOk();

        Storage::disk('local')->assertExists('materials/1700000000_lama.pdf');
        Storage::disk('public')->assertMissing('materials/1700000000_lama.pdf');
    }

    public function test_teacher_reads_only_materials_of_their_curricula(): void
    {
        $material = $this->materialOf(2);
        $this->actingAs($this->teacher)->get(route('teacher.materials.show', $material))->assertOk();
        $this->actingAs($this->teacher)->get(route('teacher.materials.index'))->assertOk()->assertSee($material->title);

        $this->actingAs($this->otherTeacher)->get(route('teacher.materials.show', $material))->assertForbidden();
    }

    public function test_unlock_mode_all_opens_every_active_material(): void
    {
        setting()->set('academic.material_unlock_mode', 'all');

        $this->actingAs($this->student)->get(route('student.materials.show', $this->materialOf(3)))->assertOk();
    }

    public function test_student_materials_pages_render(): void
    {
        $this->completeMeeting(1);

        $this->actingAs($this->student)->get(route('student.materials.index'))->assertOk()->assertSee('Kelas Python A');
        $this->actingAs($this->student)->get(route('student.materials.by-class', $this->class))
            ->assertOk()->assertSee('Materi 1.1')->assertSee('Materi 3.2');
    }
}
