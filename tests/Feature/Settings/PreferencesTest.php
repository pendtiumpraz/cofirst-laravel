<?php

namespace Tests\Feature\Settings;

use App\Support\Navigation\Appearance;
use App\Support\Settings\UserPreferences;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Settings\Concerns\InteractsWithSettings;
use Tests\TestCase;

/**
 * Akun › Preferensi (users.preferences) and the shell's colour-mode endpoint.
 */
class PreferencesTest extends TestCase
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
    public static function roles(): array
    {
        $roles = ['superadmin', 'admin', 'teacher', 'student', 'parent', 'finance'];

        return array_combine($roles, array_map(fn ($role) => [$role], $roles));
    }

    #[DataProvider('roles')]
    public function test_every_role_can_open_preferences(string $role): void
    {
        $this->actingAs($this->userWithRole($role))
            ->get(route('account.preferences'))
            ->assertOk()
            ->assertSee('Preferensi')
            ->assertSee('Pesan chat baru');
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->get(route('account.preferences'))->assertRedirect(route('login'));
    }

    public function test_preferences_are_saved_and_other_keys_survive(): void
    {
        $parent = $this->userWithRole('parent');
        $parent->forceFill(['preferences' => ['leaderboard_opt_out' => true]])->save();

        $this->actingAs($parent)
            ->put(route('account.preferences.update'), [
                'color_mode' => 'dark',
                'density' => 'compact',
                'text_size' => 'lg',
                'reduce_motion' => '1',
                'locale' => 'id',
                'notifications' => ['email' => ['report' => '0', 'payment_reminder' => '1', 'chat_message' => '0']],
            ])
            ->assertRedirect(route('account.preferences'))
            ->assertSessionHas('success');

        $prefs = $parent->fresh()->preferences;
        $this->assertTrue($prefs['leaderboard_opt_out']);
        $this->assertSame('dark', $prefs['color_mode']);
        $this->assertSame('compact', $prefs['density']);
        $this->assertSame('lg', $prefs['text_size']);
        $this->assertTrue($prefs['reduce_motion']);
        $this->assertFalse(UserPreferences::wantsEmail($parent->fresh(), 'report'));
        $this->assertTrue(UserPreferences::wantsEmail($parent->fresh(), 'payment_reminder'));

        $state = Appearance::state($parent->fresh());
        $this->assertSame('compact', $state['density']);
        $this->assertTrue($state['dark']);
    }

    public function test_theme_and_colour_mode_are_ignored_when_the_owner_does_not_allow_them(): void
    {
        setting()->setMany(['appearance.allow_user_color_mode' => false, 'appearance.allow_user_theme' => false]);
        $student = $this->userWithRole('student');

        $this->actingAs($student)
            ->put(route('account.preferences.update'), [
                'color_mode' => 'dark', 'theme' => 'b', 'density' => 'comfortable', 'text_size' => 'md', 'reduce_motion' => '0', 'locale' => 'id',
            ])
            ->assertSessionHasNoErrors();

        $prefs = $student->fresh()->preferences;
        $this->assertArrayNotHasKey('color_mode', $prefs);
        $this->assertArrayNotHasKey('theme', $prefs);
    }

    public function test_theme_is_saved_when_the_owner_allows_it(): void
    {
        setting()->set('appearance.allow_user_theme', true);
        $teacher = $this->userWithRole('teacher');

        $this->actingAs($teacher)->get(route('account.preferences'))->assertSee('Asagi Terminal');

        $this->actingAs($teacher)->put(route('account.preferences.update'), [
            'theme' => 'b', 'color_mode' => 'system', 'density' => 'comfortable', 'text_size' => 'md', 'reduce_motion' => '0', 'locale' => 'id',
        ])->assertSessionHasNoErrors();

        $this->assertSame('b', Appearance::state($teacher->fresh())['theme']);
    }

    public function test_invalid_values_are_rejected(): void
    {
        $user = $this->userWithRole('student');

        $this->actingAs($user)
            ->put(route('account.preferences.update'), ['color_mode' => 'neon', 'density' => 'tiny', 'text_size' => 'xl', 'reduce_motion' => 'x', 'locale' => 'fr'])
            ->assertSessionHasErrors(['color_mode', 'density', 'text_size', 'reduce_motion', 'locale']);

        $this->assertNull($user->fresh()->preferences);
    }

    public function test_email_options_follow_the_role_and_global_switches(): void
    {
        $teacher = $this->userWithRole('teacher');
        $parent = $this->userWithRole('parent');

        $this->assertSame(['chat_message'], array_keys(UserPreferences::emailOptionsFor($teacher)));
        $this->assertSame(['report', 'payment_reminder', 'chat_message'], array_keys(UserPreferences::emailOptionsFor($parent)));

        setting()->set('notifications.payment_reminder', false);
        $this->assertFalse(UserPreferences::wantsEmail($parent, 'payment_reminder'));
        $this->assertFalse(UserPreferences::emailOptionsFor($parent)['payment_reminder']['available']);
    }

    public function test_language_preference_is_applied_on_the_next_request(): void
    {
        $user = $this->userWithRole('student');
        $user->forceFill(['preferences' => ['locale' => 'en']])->save();

        $this->actingAs($user)->get(route('account.preferences'))->assertOk();

        $this->assertSame('en', app()->getLocale());
    }

    public function test_colour_mode_endpoint_persists_the_choice(): void
    {
        $user = $this->userWithRole('finance');

        $this->actingAs($user)
            ->postJson(route('account.color-mode'), ['color_mode' => 'dark'])
            ->assertOk()
            ->assertJson(['color_mode' => 'dark']);
        $this->assertSame('dark', $user->fresh()->preference('color_mode'));

        $this->actingAs($user)->postJson(route('account.color-mode'), ['color_mode' => 'pink'])->assertUnprocessable();

        setting()->set('appearance.allow_user_color_mode', false);
        $this->actingAs($user)->postJson(route('account.color-mode'), ['color_mode' => 'light'])->assertForbidden();
        $this->assertSame('dark', $user->fresh()->preference('color_mode'));
    }
}
