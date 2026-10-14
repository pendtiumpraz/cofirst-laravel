<?php

namespace Tests\Feature\Shell;

use App\Models\User;
use App\Support\Navigation\Navigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The dashboard shell (layouts.app) for every role, both desktop navigation variants,
 * both mobile variants, theme switching and both view conventions (component + @extends).
 */
class ShellLayoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function roles(): array
    {
        $roles = ['superadmin', 'admin', 'teacher', 'student', 'parent', 'finance'];

        return array_combine($roles, array_map(fn ($role) => [$role], $roles));
    }

    #[DataProvider('roles')]
    public function test_role_dashboard_renders_with_both_nav_variants(string $role): void
    {
        $user = $this->userForRole($role);
        $url = route(Navigation::dashboardRoute($role));

        foreach (['sidebar', 'topnav'] as $variant) {
            setting()->set('appearance.dashboard_nav', $variant);

            $response = $this->actingAs($user)->withSession(['active_role' => $role])->get($url);

            $response->assertOk();
            $response->assertSee('data-nav="'.$variant.'"', false);
            $response->assertSee('lang="id"', false);
            $response->assertSee('id="konten"', false);
            $response->assertSee('Lewati ke konten utama');
            $response->assertSee('Navigasi bawah');
            $response->assertSee('data-cf-logout>', false);
            $variant === 'sidebar'
                ? $response->assertSee('cf-sidebar', false)
                : $response->assertDontSee('class="cf-sidebar', false);
        }
    }

    public function test_extends_convention_uses_section_title_and_content(): void
    {
        $user = $this->userForRole('student');

        $response = $this->actingAs($user)->withSession(['active_role' => 'student'])->get(route('gamification.leaderboard'));

        $response->assertOk();
        // @section('title') becomes the document title "… · Coding First".
        $this->assertMatchesRegularExpression('#<title>[^<]+ · Coding First</title>#', $response->getContent());
        $response->assertSee('aria-current="page"', false);
    }

    public function test_theme_b_renders_data_theme_b_and_matching_logo(): void
    {
        setting()->set('appearance.theme', 'b');
        $user = $this->userForRole('teacher');

        $response = $this->actingAs($user)->withSession(['active_role' => 'teacher'])->get(route('teacher.dashboard'));

        $response->assertOk();
        $response->assertSee('data-theme="b"', false);
        $response->assertSee('#EE4D1C', false);          // caret of logo B ("auto" follows theme)
        $response->assertSee('brand/b/favicon.svg', false);
    }

    public function test_drawer_mobile_navigation_replaces_tab_bar(): void
    {
        setting()->set('appearance.mobile_nav', 'drawer');
        $user = $this->userForRole('parent');

        $response = $this->actingAs($user)->withSession(['active_role' => 'parent'])->get(route('parent.dashboard'));

        $response->assertOk();
        $response->assertSee('data-mobile-nav="drawer"', false);
        $response->assertSee('id="cf-drawer"', false);
        $response->assertDontSee('Navigasi bawah');
    }

    public function test_user_preferences_override_theme_and_density_only_when_allowed(): void
    {
        $user = $this->userForRole('student');
        $user->forceFill(['preferences' => ['theme' => 'b', 'density' => 'compact', 'color_mode' => 'dark']])->save();

        $blocked = $this->actingAs($user)->withSession(['active_role' => 'student'])->get(route('student.dashboard'));
        $blocked->assertSee('data-theme="a"', false);       // allow_user_theme defaults to false
        $blocked->assertSee('data-density="compact"', false); // density is always user-overridable
        $blocked->assertSee('class="dark"', false);

        setting()->set('appearance.allow_user_theme', true);
        $allowed = $this->actingAs($user)->withSession(['active_role' => 'student'])->get(route('student.dashboard'));
        $allowed->assertSee('data-theme="b"', false);
    }

    public function test_layout_renders_pushed_scripts_styles_and_title_prop(): void
    {
        $this->actingAs($this->userForRole('finance'));

        $template = <<<'BLADE'
            <x-app-layout title="Halaman Uji">
                <x-slot name="header"><h2>Judul Uji</h2></x-slot>
                @push('styles')
                    <style>.uji{}</style>
                @endpush
                @push('scripts')
                    <script>window.uji = 1;</script>
                @endpush
                <p>Isi halaman</p>
            </x-app-layout>
            BLADE;

        $view = $this->blade($template);

        $view->assertSee('<title>Halaman Uji · Coding First</title>', false);
        $view->assertSee('.uji{}', false);
        $view->assertSee('window.uji = 1;', false);
        $view->assertSee('Judul Uji');
        $view->assertSee('Isi halaman');
    }

    public function test_app_layout_degrades_for_guests_and_users_without_a_role(): void
    {
        $this->blade('<x-app-layout title="Tanpa sesi">Isi tamu</x-app-layout>')
            ->assertSee('Isi tamu')
            ->assertDontSee('data-cf-logout>', false);

        $this->actingAs(User::factory()->create());
        $this->blade('<x-app-layout title="Tanpa peran">Isi</x-app-layout>')
            ->assertSee('<title>Tanpa peran · Coding First</title>', false)
            ->assertDontSee('Navigasi bawah');
    }

    public function test_announcement_and_maintenance_notices_render(): void
    {
        setting()->setMany([
            'system.dashboard_announcement' => 'Libur nasional tanggal 17 Agustus.',
            'system.maintenance_notice' => 'Sabtu pukul 22.00 WIB.',
        ]);
        $user = $this->userForRole('admin');

        $response = $this->actingAs($user)->withSession(['active_role' => 'admin'])->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Libur nasional tanggal 17 Agustus.');
        $response->assertSee('Pemeliharaan terjadwal:');
    }

    public function test_guest_layout_split_and_centered(): void
    {
        setting()->set('appearance.auth_layout', 'split');
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Data Anda dilindungi sesuai UU PDP')
            ->assertSee('Dilindungi UU PDP');

        setting()->set('appearance.auth_layout', 'centered');
        $this->get(route('password.request'))
            ->assertOk()
            ->assertDontSee('Data Anda dilindungi sesuai UU PDP')
            ->assertSee('Dilindungi UU PDP');
    }

    public function test_public_layout_header_footer_and_whatsapp_toggle(): void
    {
        $view = $this->blade('<x-public-layout title="Tentang Kami">Konten publik</x-public-layout>');

        $view->assertSee('<title>Tentang Kami · Coding First</title>', false);
        $view->assertSee('Konten publik');
        $view->assertSee('Beranda');
        $view->assertSee('Masuk');
        $view->assertSee('privasi@codingfirst.id');
        $view->assertSee('Pengendali Data Pribadi');
        $view->assertSee('og:image', false);
        $view->assertDontSee('wa.me', false);

        setting()->setMany(['integrations.whatsapp_button' => true, 'general.whatsapp' => '6281234567890']);
        $this->blade('<x-public-layout>Isi</x-public-layout>')->assertSee('https://wa.me/6281234567890', false);
    }

    /**
     * A seeded, active user holding the role (same lookup as the route smoke test).
     *
     * Reason: the in-memory database is migrated once per test process, so whether the demo
     * data exists depends on which test class ran first; seed on demand (rolled back after).
     */
    private function userForRole(string $role): User
    {
        $find = fn () => Role::where('name', $role)->exists() ? User::role($role)->where('is_active', true)->first() : null;
        $user = $find();
        if (! $user) {
            $this->seed();
            $user = $find();
        }
        $this->assertNotNull($user, "No seeded user with role {$role}.");

        return $user;
    }
}
