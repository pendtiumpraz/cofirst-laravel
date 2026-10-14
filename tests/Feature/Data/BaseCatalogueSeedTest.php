<?php

namespace Tests\Feature\Data;

use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Material;
use App\Models\Reward;
use App\Models\Syllabus;
use Database\Seeders\CertificateTemplateSeeder;
use Database\Seeders\CourseSeeder;
use Database\Seeders\EnhancedCurriculumSeeder;
use Database\Seeders\GamificationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Re-running the production base seeders never re-creates catalogue rows an admin
 * renamed or removed, and never adds curricula to courses an admin created.
 */
class BaseCatalogueSeedTest extends TestCase
{
    use RefreshDatabase;

    /** The seeders DatabaseSeeder runs in every environment (production included). */
    private const BASE_SEEDERS = [
        RoleSeeder::class,
        CourseSeeder::class,
        EnhancedCurriculumSeeder::class,
        GamificationSeeder::class,
        CertificateTemplateSeeder::class,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Reason: the shared in-memory DB may or may not be seeded already (depends on
        // which test class migrated it first); base seeding is idempotent either way.
        $this->seed(self::BASE_SEEDERS);
    }

    public function test_reseeding_keeps_renamed_and_removed_catalogue_rows_as_the_admin_left_them(): void
    {
        $python = Course::where('name', 'Python')->firstOrFail();
        $python->update(['name' => 'Python Dasar']);

        $material = Material::whereHas('syllabus.curriculum', fn ($q) => $q->where('course_id', $python->id))
            ->orderBy('id')->firstOrFail();
        $material->update(['title' => 'Judul dari admin']);

        $renamedReward = Reward::whereDoesntHave('redemptions')->orderBy('id')->firstOrFail();
        $renamedReward->update(['name' => 'Hadiah dari admin']);
        Reward::whereDoesntHave('redemptions')->whereKeyNot($renamedReward->id)->orderByDesc('id')->firstOrFail()->delete();

        CertificateTemplate::query()->update(['name' => 'Template Resmi']);

        $before = $this->catalogueCounts();
        $this->seed(self::BASE_SEEDERS);

        $this->assertSame($before, $this->catalogueCounts());
        $this->assertFalse(Course::withTrashed()->where('name', 'Python')->exists(), 'renamed course was re-created');
        $this->assertSame('Python Dasar', $python->fresh()->name);
        $this->assertSame('Judul dari admin', $material->fresh()->title);
    }

    public function test_courses_created_by_an_admin_get_no_seeded_curriculum(): void
    {
        $course = Course::factory()->create(['name' => 'Data Science Lanjutan']);

        $this->seed(self::BASE_SEEDERS);

        $this->assertSame(0, Curriculum::withTrashed()->where('course_id', $course->id)->count());
        // Expected use: every catalogue course still has its curriculum.
        foreach (CourseSeeder::catalogue() as $item) {
            $this->assertTrue(
                Curriculum::where('course_id', CourseSeeder::find($item['name'])->id)->exists(),
                "{$item['name']} has no curriculum"
            );
        }
    }

    public function test_an_archived_catalogue_course_is_not_restored_or_re_created(): void
    {
        $scratch = Course::where('name', 'Scratch')->firstOrFail();
        $scratch->delete();

        $this->seed(self::BASE_SEEDERS);

        $this->assertSame(0, Course::where('name', 'Scratch')->count());
        $this->assertSame(1, Course::withTrashed()->where('name', 'Scratch')->count());
        $this->assertSoftDeleted($scratch);
    }

    /**
     * Row counts of the catalogue tables (archived rows included).
     *
     * @return array<string, int>
     */
    private function catalogueCounts(): array
    {
        return [
            'courses' => Course::withTrashed()->count(),
            'curriculums' => Curriculum::withTrashed()->count(),
            'syllabuses' => Syllabus::withTrashed()->count(),
            'materials' => Material::withTrashed()->count(),
            'rewards' => Reward::count(),
            'certificate_templates' => CertificateTemplate::count(),
        ];
    }
}
