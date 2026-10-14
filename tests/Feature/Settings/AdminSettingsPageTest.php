<?php

namespace Tests\Feature\Settings;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Settings\Concerns\InteractsWithSettings;
use Tests\TestCase;

/**
 * Admin › Pengaturan pages: every group renders from config/settings.php for
 * admins, other roles are refused, and sensitive groups are read-only for admins.
 */
class AdminSettingsPageTest extends TestCase
{
    use InteractsWithSettings;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function settingGroups(): array
    {
        $config = require dirname(__DIR__, 3).'/config/settings.php';

        return collect(array_keys($config['groups']))->mapWithKeys(fn ($group) => [$group => [$group]])->all();
    }

    #[DataProvider('settingGroups')]
    public function test_every_group_renders_every_field_for_admin(string $group): void
    {
        $admin = $this->userWithRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.settings.edit', $group));

        $response->assertOk();
        foreach (config('settings.fields') as $key => $field) {
            if ($field['group'] === $group) {
                $response->assertSee($field['label']);
            }
        }
        $response->assertSee(config("settings.groups.{$group}.label"));
    }

    public function test_index_redirects_to_the_first_group(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get(route('admin.settings.index'))
            ->assertRedirect(route('admin.settings.edit', 'general'));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function nonAdminRoles(): array
    {
        return ['teacher' => ['teacher'], 'student' => ['student'], 'parent' => ['parent'], 'finance' => ['finance']];
    }

    #[DataProvider('nonAdminRoles')]
    public function test_other_roles_are_forbidden(string $role): void
    {
        $user = $this->userWithRole($role);

        $this->actingAs($user)->get(route('admin.settings.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.settings.edit', 'general'))->assertForbidden();
        $this->actingAs($user)->put(route('admin.settings.update', 'general'), [])->assertForbidden();
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->get(route('admin.settings.edit', 'general'))->assertRedirect(route('login'));
    }

    public function test_unknown_group_is_not_found(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get('/admin/settings/tidak-ada')
            ->assertNotFound();
    }

    public function test_admin_sees_security_and_privacy_read_only(): void
    {
        $admin = $this->userWithRole('admin');

        foreach (['security', 'privacy'] as $group) {
            $this->actingAs($admin)->get(route('admin.settings.edit', $group))
                ->assertOk()
                ->assertSee('hanya dapat diubah oleh superadmin')
                ->assertDontSee('id="settings-reset"', false);
        }
    }

    public function test_superadmin_confirms_password_before_editing_sensitive_groups(): void
    {
        $superadmin = $this->userWithRole('superadmin');

        $this->actingAs($superadmin)
            ->get(route('admin.settings.edit', 'security'))
            ->assertRedirect(route('password.confirm'))
            ->assertSessionHas('url.intended', route('admin.settings.edit', 'security'));

        $this->actingAs($superadmin)
            ->withSession($this->passwordConfirmed())
            ->get(route('admin.settings.edit', 'security'))
            ->assertOk()
            ->assertSee('id="settings-reset"', false)
            ->assertDontSee('Hanya dapat dilihat');
    }

    public function test_appearance_shows_ab_cards_with_version_names_and_preview_links(): void
    {
        $response = $this->actingAs($this->userWithRole('admin'))->get(route('admin.settings.edit', 'appearance'));

        $response->assertOk()
            ->assertSee('Versi A · Sumi &amp; Shu (Editorial)', false)
            ->assertSee('Versi B · Asagi Terminal (Presisi)')
            ->assertSee('Pratinjau')
            ->assertSee('Aktif')
            ->assertSee('/admin/settings/preview?expires=', false)
            ->assertSee('key=appearance.theme', false);
    }

    public function test_last_change_and_editor_are_shown(): void
    {
        $admin = $this->userWithRole('admin');
        setting()->set('general.tagline', 'Tagline baru', $admin->id);

        $this->actingAs($admin)->get(route('admin.settings.edit', 'general'))
            ->assertOk()
            ->assertSee('Terakhir diubah')
            ->assertSee($admin->name)
            ->assertSee('Kembalikan ke bawaan');
    }
}
