<?php

namespace Tests\Feature\Academic;

use App\Models\ClassProgress;
use App\Models\ClassReport;
use App\Models\MaterialAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Maintenance commands: progress backfill from reports, retention purge of
 * material access rows (UU PDP storage limitation), legacy file migration.
 */
class AcademicCommandsTest extends TestCase
{
    use AcademicFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
    }

    public function test_rebuild_progress_backfills_completed_meetings(): void
    {
        ClassReport::factory()->create([
            'teacher_id' => $this->teacher->id, 'class_id' => $this->class->id, 'student_id' => $this->student->id,
            'meeting_number' => 2, 'syllabus_id' => null,
        ]);

        $this->artisan('academic:rebuild-progress')->assertSuccessful();

        $row = ClassProgress::where('student_id', $this->student->id)->where('meeting_number', 2)->firstOrFail();
        $this->assertSame('completed', $row->status);
        $this->assertSame($this->meetings[2]->id, $row->syllabus_id);
    }

    public function test_rebuild_skips_meetings_outside_the_curriculum(): void
    {
        ClassReport::factory()->create([
            'teacher_id' => $this->teacher->id, 'class_id' => $this->class->id, 'student_id' => $this->student->id,
            'meeting_number' => 42,
        ]);

        $this->artisan('academic:rebuild-progress', ['--class' => $this->class->id])->assertSuccessful();

        $this->assertSame(0, ClassProgress::count());
    }

    public function test_purge_removes_only_expired_access_rows(): void
    {
        $material = $this->materialOf(1);
        MaterialAccess::create(['student_id' => $this->student->id, 'material_id' => $material->id, 'accessed_at' => now()->subDays(400)]);
        MaterialAccess::create(['student_id' => $this->student->id, 'material_id' => $material->id, 'accessed_at' => now()->subDays(3)]);

        $this->artisan('academic:purge-material-access')->assertSuccessful();

        $this->assertSame(1, MaterialAccess::count());
    }

    public function test_secure_files_moves_public_material_files(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('public')->put('materials/lama.pdf', '%PDF');
        $this->materialOf(1)->update(['file_path' => 'materials/lama.pdf']);

        $this->artisan('academic:secure-material-files')->assertSuccessful();

        Storage::disk('local')->assertExists('materials/lama.pdf');
        Storage::disk('public')->assertMissing('materials/lama.pdf');
    }
}
