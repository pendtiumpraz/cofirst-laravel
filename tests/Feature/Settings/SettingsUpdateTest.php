<?php

namespace Tests\Feature\Settings;

use App\Models\Setting;
use App\Support\Settings\SettingsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\Feature\Settings\Concerns\InteractsWithSettings;
use Tests\TestCase;

/**
 * Saving a settings group: typed values, only changed keys, cache flush,
 * validation from the registry rules and per-group edit permissions.
 */
class SettingsUpdateTest extends TestCase
{
    use InteractsWithSettings;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_admin_saves_a_group_and_only_changed_keys_are_stored(): void
    {
        $admin = $this->userWithRole('admin');
        setting('general.site_name'); // warm the cache

        $this->actingAs($admin)
            ->put(route('admin.settings.update', 'general'), $this->groupPayload('general', [
                'general.site_name' => 'Coding First Academy',
                'general.timezone' => 'Asia/Makassar',
            ]))
            ->assertRedirect(route('admin.settings.edit', 'general'))
            ->assertSessionHas('success');

        $this->assertFalse(Cache::has(SettingsManager::CACHE_KEY), 'The settings cache must be flushed after saving.');
        $this->assertSame('Coding First Academy', setting('general.site_name'));
        $this->assertSame('Asia/Makassar', setting('general.timezone'));
        $this->assertSame(['general.site_name', 'general.timezone'], Setting::orderBy('key')->pluck('key')->all());
        $this->assertSame($admin->id, Setting::where('key', 'general.site_name')->value('updated_by'));
    }

    public function test_changes_are_audited_with_keys_but_never_values(): void
    {
        if (class_exists('App\\Services\\Privacy\\AuditLogger')) {
            $this->markTestSkipped('The privacy AuditLogger records settings events; covered by its own tests.');
        }

        Log::spy();
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->put(route('admin.settings.update', 'general'), $this->groupPayload('general', [
            'general.contact_phone' => '0812 3456 7890',
        ]));

        Log::shouldHaveReceived('info')->withArgs(function (string $event, array $context) use ($admin) {
            return $event === 'settings.updated'
                && $context['keys'] === ['general.contact_phone']
                && $context['actor_id'] === $admin->id
                && ! str_contains(json_encode($context), '3456');
        })->once();
    }

    public function test_saving_without_changes_stores_nothing(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->put(route('admin.settings.update', 'academic'), $this->groupPayload('academic'))
            ->assertRedirect()
            ->assertSessionHas('info');

        $this->assertSame(0, Setting::count());
    }

    public function test_values_are_cast_to_their_types(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->put(route('admin.settings.update', 'academic'), $this->groupPayload('academic', [
                'academic.session_duration_minutes' => '120',
                'academic.reports_visible_to_parents' => false,
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(120, setting('academic.session_duration_minutes'));
        $this->assertFalse(setting('academic.reports_visible_to_parents'));
    }

    public function test_invalid_values_are_rejected_with_indonesian_labels(): void
    {
        app()->setLocale('id');

        $response = $this->actingAs($this->userWithRole('admin'))
            ->from(route('admin.settings.edit', 'general'))
            ->put(route('admin.settings.update', 'general'), $this->groupPayload('general', [
                'general.site_name' => '',
                'general.contact_email' => 'bukan-email',
                'general.social_instagram' => 'javascript:alert(1)',
            ]));

        $response->assertRedirect(route('admin.settings.edit', 'general'))
            ->assertSessionHasErrors(['s.general.site_name', 's.general.contact_email', 's.general.social_instagram']);
        $this->assertStringContainsString('Nama aplikasi', session('errors')->first('s.general.site_name'));
        $this->assertSame(0, Setting::count());
    }

    public function test_landing_links_only_accept_safe_targets(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->put(route('admin.settings.update', 'landing'), $this->groupPayload('landing', ['landing.cta_primary_url' => 'javascript:alert(1)']))
            ->assertSessionHasErrors('s.landing.cta_primary_url');

        $this->actingAs($admin)
            ->put(route('admin.settings.update', 'landing'), $this->groupPayload('landing', ['landing.cta_primary_url' => '/courses']))
            ->assertSessionHasNoErrors();

        $this->assertSame('/courses', setting('landing.cta_primary_url'));
    }

    public function test_repeater_drops_empty_rows_and_validates_partial_rows(): void
    {
        $admin = $this->userWithRole('admin');
        $rows = [
            ['question' => 'Apakah ada kelas percobaan?', 'answer' => 'Ya, gratis satu kali.'],
            ['question' => '', 'answer' => ''],
            ['question' => 'Berapa lama satu sesi?', 'answer' => '90 menit.'],
        ];

        $this->actingAs($admin)
            ->put(route('admin.settings.update', 'landing'), $this->groupPayload('landing', ['landing.faq' => $rows]))
            ->assertSessionHasNoErrors();

        $this->assertSame([$rows[0], $rows[2]], setting('landing.faq'));

        $this->actingAs($admin)
            ->put(route('admin.settings.update', 'landing'), $this->groupPayload('landing', ['landing.faq' => [['question' => 'Tanpa jawaban?', 'answer' => '']]]))
            ->assertSessionHasErrors('s.landing.faq.0.answer');
    }

    public function test_multiselect_items_are_validated(): void
    {
        $superadmin = $this->userWithRole('superadmin');

        $this->actingAs($superadmin)->withSession($this->passwordConfirmed())
            ->put(route('admin.settings.update', 'security'), $this->groupPayload('security', ['security.registration_roles' => ['admin']]))
            ->assertSessionHasErrors('s.security.registration_roles.0');

        $this->actingAs($superadmin)->withSession($this->passwordConfirmed())
            ->put(route('admin.settings.update', 'security'), $this->groupPayload('security', ['security.registration_roles' => ['parent']]))
            ->assertSessionHasNoErrors();

        $this->assertSame(['parent'], setting('security.registration_roles'));
    }

    public function test_admin_cannot_change_security_or_privacy(): void
    {
        $admin = $this->userWithRole('admin');

        foreach (['security', 'privacy'] as $group) {
            $this->actingAs($admin)
                ->put(route('admin.settings.update', $group), $this->groupPayload($group))
                ->assertForbidden();
        }

        $this->assertSame(0, Setting::count());
    }

    public function test_superadmin_changes_privacy_after_confirming_password(): void
    {
        $superadmin = $this->userWithRole('superadmin');
        $payload = $this->groupPayload('privacy', ['privacy.dsr_sla_hours' => '48']);

        $this->actingAs($superadmin)
            ->put(route('admin.settings.update', 'privacy'), $payload)
            ->assertRedirect(route('password.confirm'));
        $this->assertSame(72, setting('privacy.dsr_sla_hours'));

        $this->actingAs($superadmin)->withSession($this->passwordConfirmed())
            ->put(route('admin.settings.update', 'privacy'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.settings.edit', 'privacy'));

        $this->assertSame(48, setting('privacy.dsr_sla_hours'));
    }
}
