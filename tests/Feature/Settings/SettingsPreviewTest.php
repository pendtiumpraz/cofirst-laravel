<?php

namespace Tests\Feature\Settings;

use App\Support\Settings\AppearancePreview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Feature\Settings\Concerns\InteractsWithSettings;
use Tests\TestCase;

/**
 * "Pratinjau": an A/B option applied to the admin's own session only, then
 * applied for everyone or discarded.
 */
class SettingsPreviewTest extends TestCase
{
    use InteractsWithSettings;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_signed_link_previews_an_option_for_this_session_only(): void
    {
        $admin = $this->userWithRole('admin');
        $url = URL::temporarySignedRoute('admin.settings.preview', now()->addHour(), ['key' => 'appearance.theme', 'value' => 'b']);

        $this->actingAs($admin)->get($url)
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('appearance_preview.theme', 'b')
            ->assertSessionHas('info');

        $this->assertSame('b', setting('appearance.theme'), 'setting() follows the preview in this session.');
        $this->assertSame('a', setting()->saved('appearance.theme'), 'Nothing is saved.');

        $this->actingAs($admin)->get(route('admin.settings.edit', 'appearance'))
            ->assertOk()
            ->assertSee('Pratinjau aktif')
            ->assertSee('data-theme="b"', false);
    }

    public function test_unsigned_or_invalid_preview_links_are_refused(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->get(route('admin.settings.preview', ['key' => 'appearance.theme', 'value' => 'b']))
            ->assertForbidden();

        $invalid = URL::temporarySignedRoute('admin.settings.preview', now()->addHour(), ['key' => 'appearance.theme', 'value' => 'z']);
        $this->actingAs($admin)->get($invalid)->assertNotFound();

        $notPreviewable = URL::temporarySignedRoute('admin.settings.preview', now()->addHour(), ['key' => 'general.site_name', 'value' => 'x']);
        $this->actingAs($admin)->get($notPreviewable)->assertNotFound();

        $this->actingAs($this->userWithRole('teacher'))
            ->get(URL::temporarySignedRoute('admin.settings.preview', now()->addHour(), ['key' => 'appearance.theme', 'value' => 'b']))
            ->assertForbidden();
    }

    public function test_auth_layout_preview_page_renders_the_previewed_layout(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->withSession([AppearancePreview::SESSION_KEY => ['auth_layout' => 'centered']])
            ->get(route('admin.settings.preview.auth'))
            ->assertOk()
            ->assertSee('Mode pratinjau')
            ->assertSee('Kartu di tengah');
    }

    public function test_preview_can_be_applied_for_everyone(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->withSession([AppearancePreview::SESSION_KEY => ['theme' => 'b', 'dashboard_nav' => 'topnav']])
            ->post(route('admin.settings.preview.apply'))
            ->assertRedirect(route('admin.settings.edit', 'appearance'))
            ->assertSessionMissing(AppearancePreview::SESSION_KEY);

        $this->assertSame('b', setting()->saved('appearance.theme'));
        $this->assertSame('topnav', setting()->saved('appearance.dashboard_nav'));
        $this->assertDatabaseHas('settings', ['key' => 'appearance.theme', 'updated_by' => $admin->id]);
    }

    public function test_preview_can_be_ended_without_saving(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->withSession([AppearancePreview::SESSION_KEY => ['theme' => 'b']])
            ->delete(route('admin.settings.preview.destroy'))
            ->assertRedirect(route('admin.settings.edit', 'appearance'))
            ->assertSessionMissing(AppearancePreview::SESSION_KEY);

        $this->assertSame('a', setting('appearance.theme'));
        $this->assertDatabaseMissing('settings', ['key' => 'appearance.theme']);
    }
}
