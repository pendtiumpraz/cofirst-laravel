<?php

namespace Tests\Feature\Settings;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Settings\Concerns\InteractsWithSettings;
use Tests\TestCase;

/**
 * "Kembalikan ke bawaan" per field and per group.
 */
class SettingsResetTest extends TestCase
{
    use InteractsWithSettings;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_one_field_is_reset_to_its_default(): void
    {
        $admin = $this->userWithRole('admin');
        setting()->setMany(['general.site_name' => 'Nama Lain', 'general.tagline' => 'Tagline lain'], $admin->id);

        $this->actingAs($admin)
            ->post(route('admin.settings.reset', 'general'), ['field' => 'general.site_name'])
            ->assertRedirect(route('admin.settings.edit', 'general'))
            ->assertSessionHas('success');

        $this->assertSame('Coding First', setting('general.site_name'));
        $this->assertSame('Tagline lain', setting('general.tagline'));
        $this->assertDatabaseMissing('settings', ['key' => 'general.site_name']);
    }

    public function test_whole_group_is_reset(): void
    {
        $admin = $this->userWithRole('admin');
        setting()->setMany(['finance.invoice_prefix' => 'TAG', 'finance.payment_due_days' => 14, 'general.site_name' => 'Tetap'], $admin->id);

        $this->actingAs($admin)
            ->post(route('admin.settings.reset', 'finance'))
            ->assertSessionHas('success');

        $this->assertSame('INV', setting('finance.invoice_prefix'));
        $this->assertSame(7, setting('finance.payment_due_days'));
        $this->assertSame(['general.site_name'], Setting::pluck('key')->all());
    }

    public function test_resetting_when_nothing_is_customised_is_harmless(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post(route('admin.settings.reset', 'pwa'))
            ->assertRedirect(route('admin.settings.edit', 'pwa'))
            ->assertSessionHas('info');
    }

    public function test_field_of_another_group_is_rejected(): void
    {
        $admin = $this->userWithRole('admin');
        setting()->set('security.password_min_length', 12);

        $this->actingAs($admin)
            ->post(route('admin.settings.reset', 'general'), ['field' => 'security.password_min_length'])
            ->assertSessionHasErrors('field');

        $this->assertSame(12, setting('security.password_min_length'));
    }

    public function test_admin_cannot_reset_privacy(): void
    {
        setting()->set('privacy.dsr_sla_hours', 48);

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('admin.settings.reset', 'privacy'))
            ->assertForbidden();

        $this->assertSame(48, setting('privacy.dsr_sla_hours'));
    }
}
