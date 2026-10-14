<?php

namespace Tests\Feature\Shell;

use App\Models\User;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\RouteAccess;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * App\Support\Navigation\Navigation: per-role menus contain only existing, allowed
 * routes, keep every link of the old hard-coded menus reachable and pick bottom tabs.
 */
class NavigationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Roles and permissions only (idempotent; rolled back with the test transaction).
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    /** Links of the old layouts (app.blade.php / navigation.blade.php) that must stay reachable. */
    private const LEGACY_LINKS = [
        'admin' => [
            'admin.dashboard', 'admin.users.index', 'admin.courses.index', 'admin.classes.index', 'admin.enrollments.index',
            'admin.schedules.index', 'admin.curriculums.index', 'admin.syllabuses.index', 'admin.materials.index',
            'class-reports.index', 'admin.class-photos.index', 'project-gallery.index', 'admin.certificate-templates.index',
            'certificates.index', 'admin.certificates.bulk-generate', 'admin.badges.index', 'admin.rewards.index',
            'admin.rewards.redemptions', 'admin.reports.index', 'admin.finance.dashboard', 'finance.reports.index',
            'admin.testimonials.index', 'chat.index', 'profile.edit',
        ],
        'superadmin' => ['admin.roles.index', 'admin.permissions.index', 'finance.transactions.index'],
        'teacher' => [
            'teacher.classes', 'teacher.curriculums.index', 'teacher.syllabuses.index', 'teacher.materials.index',
            'class-reports.index', 'teacher.class-photos.index', 'project-gallery.index', 'teacher.certificates.generate',
            'certificates.index', 'gamification.leaderboard', 'teacher.student-progress.index', 'chat.index', 'profile.edit',
        ],
        'student' => [
            'student.classes', 'student.curriculum.index', 'student.schedules.index', 'student.reports.index', 'profile.edit',
            'project-gallery.index', 'project-gallery.featured', 'certificates.index', 'gamification.index',
            'gamification.badges', 'gamification.rewards', 'gamification.leaderboard', 'chat.index',
        ],
        'parent' => [
            'parent.children.index', 'parent.progress.index', 'parent.reports.index', 'project-gallery.index',
            'certificates.index', 'gamification.badges', 'gamification.rewards', 'parent.testimonials.create',
            'parent.class-photos.index', 'chat.index', 'profile.edit',
        ],
        'finance' => ['finance.transactions.index', 'chat.index', 'profile.edit'],
    ];

    /**
     * @return array<string, array{0: string}>
     */
    public static function roles(): array
    {
        $roles = ['superadmin', 'admin', 'teacher', 'student', 'parent', 'finance'];

        return array_combine($roles, array_map(fn ($role) => [$role], $roles));
    }

    #[DataProvider('roles')]
    public function test_menu_contains_only_existing_routes_the_role_may_open(string $role): void
    {
        $user = User::factory()->{$role}()->create();
        $sections = Navigation::for($user, $role);

        $this->assertNotEmpty($sections);
        foreach (Navigation::flatten($sections) as $item) {
            $this->assertTrue(Route::has($item['route']), "Route {$item['route']} does not exist.");
            $this->assertTrue(RouteAccess::allows($user, $item['route']), "{$role} may not open {$item['route']}.");
            $this->assertSame(route($item['route']), $item['url']);
            $this->assertNotSame('', trim($item['label']));
            $this->assertFileExists(base_path("vendor/blade-ui-kit/blade-heroicons/resources/svg/o-{$item['icon']}.svg"));
        }
    }

    #[DataProvider('roles')]
    public function test_every_legacy_menu_link_stays_reachable(string $role): void
    {
        $user = User::factory()->{$role}()->create();
        $reachable = collect(Navigation::flatten(Navigation::for($user, $role)))
            ->merge(Navigation::accountItems($user))
            ->pluck('route')
            ->all();

        $expected = self::LEGACY_LINKS[$role] ?? [];
        if ($role === 'superadmin') {
            $expected = array_merge(self::LEGACY_LINKS['admin'], $expected);
        }

        foreach ($expected as $route) {
            $this->assertContains($route, $reachable, "{$role}: legacy link {$route} is no longer reachable.");
        }
    }

    #[DataProvider('roles')]
    public function test_mobile_tabs_are_four_reachable_menu_items(string $role): void
    {
        $user = User::factory()->{$role}()->create();
        $tabs = Navigation::mobileTabs($user, $role);
        $menuRoutes = collect(Navigation::flatten(Navigation::for($user, $role)))->pluck('route')->all();

        $this->assertCount(min(4, count($menuRoutes)), $tabs);
        $this->assertSame(Navigation::dashboardRoute($role), $tabs[0]['route']);
        foreach ($tabs as $tab) {
            $this->assertContains($tab['route'], $menuRoutes);
            $this->assertFileExists(base_path("vendor/blade-ui-kit/blade-heroicons/resources/svg/s-{$tab['icon']}.svg"));
        }
    }

    public function test_student_tabs_follow_the_spec(): void
    {
        $user = User::factory()->student()->create();

        $this->assertSame(['Beranda', 'Kelas', 'Jadwal', 'Pencapaian'], array_column(Navigation::mobileTabs($user), 'label'));
    }

    public function test_superadmin_only_items_are_hidden_from_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $superadmin = User::factory()->superadmin()->create();

        $adminRoutes = collect(Navigation::flatten(Navigation::for($admin)))->pluck('route');
        $superRoutes = collect(Navigation::flatten(Navigation::for($superadmin)))->pluck('route');

        $this->assertNotContains('admin.roles.index', $adminRoutes);
        $this->assertNotContains('admin.permissions.index', $adminRoutes);
        $this->assertContains('admin.roles.index', $superRoutes);
    }

    public function test_multi_role_user_sees_only_the_active_role_menu(): void
    {
        $user = User::factory()->parent()->create();
        $user->assignRole('student');

        $this->assertSame(['parent', 'student'], Navigation::rolesOf($user));
        $this->assertSame('parent', Navigation::activeRole($user));        // first held role by default
        $this->assertSame('student', Navigation::activeRole($user, 'student'));
        $this->assertSame('parent', Navigation::activeRole($user, 'admin')); // not held → ignored

        $studentMenu = collect(Navigation::flatten(Navigation::for($user, 'student')))->pluck('route');
        $this->assertContains('student.classes', $studentMenu);
        $this->assertNotContains('parent.children.index', $studentMenu);
    }

    public function test_user_without_dashboard_role_gets_an_empty_menu(): void
    {
        $user = User::factory()->create();

        $this->assertSame([], Navigation::for($user));
        $this->assertSame([], Navigation::mobileTabs($user));
        $this->assertNull(Navigation::activeRole($user));
    }

    public function test_modules_built_in_parallel_appear_once_their_routes_exist(): void
    {
        $this->registerRoute('admin/pengaturan-uji', 'admin.settings.index', 'role:admin|superadmin');
        $this->registerRoute('admin/ui-kit-uji', 'admin.ui-kit', 'role:admin|superadmin');
        $this->registerRoute('admin/privasi-uji', 'admin.privacy.dashboard', 'role:admin|superadmin');
        $this->registerRoute('admin/privasi-uji/permintaan', 'admin.privacy.requests.index', 'role:admin|superadmin');
        $this->registerRoute('akun/privasi-uji', 'account.privacy.index');

        $admin = User::factory()->admin()->create();
        $superadmin = User::factory()->superadmin()->create();
        $adminSections = collect(Navigation::for($admin))->keyBy('key');
        $adminRoutes = collect(Navigation::flatten($adminSections->all()))->pluck('route');

        $this->assertContains('admin.settings.index', $adminRoutes);
        $this->assertNotContains('admin.ui-kit', $adminRoutes, 'UI Kit is superadmin-only.');
        $this->assertContains('admin.ui-kit', collect(Navigation::flatten(Navigation::for($superadmin)))->pluck('route'));
        $this->assertSame(['Dasbor PDP', 'Permintaan Data'], collect($adminSections['privasi']['items'])->pluck('label')->all());
        $this->assertContains('account.privacy.index', collect(Navigation::accountItems($admin))->pluck('route'));

        $teacher = User::factory()->teacher()->create();
        $this->assertNotContains('admin.privacy.dashboard', collect(Navigation::flatten(Navigation::for($teacher)))->pluck('route'));
    }

    private function registerRoute(string $uri, string $name, ?string $middleware = null): void
    {
        if (Route::has($name)) {
            return;
        }

        Route::middleware(array_filter(['web', 'auth', $middleware]))->get($uri, fn () => 'ok')->name($name);
        Route::getRoutes()->refreshNameLookups();
    }
}
