<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\Auth\UserSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProfileSessionsTest extends TestCase
{
    use RefreshDatabase;

    private const WINDOWS_CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0 Safari/537.36';

    private const IPHONE_SAFARI = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1';

    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'database']);
    }

    private function seedSessions(User $user, User $stranger): void
    {
        DB::table('sessions')->insert([
            ['id' => 'laptop', 'user_id' => $user->id, 'ip_address' => '182.253.10.1', 'user_agent' => self::WINDOWS_CHROME, 'payload' => '', 'last_activity' => now()->subMinutes(30)->timestamp],
            ['id' => 'ponsel', 'user_id' => $user->id, 'ip_address' => '114.4.20.2', 'user_agent' => self::IPHONE_SAFARI, 'payload' => '', 'last_activity' => now()->subDays(2)->timestamp],
            ['id' => 'orang-lain', 'user_id' => $stranger->id, 'ip_address' => '36.70.0.9', 'user_agent' => self::WINDOWS_CHROME, 'payload' => '', 'last_activity' => now()->timestamp],
        ]);
    }

    public function test_profile_lists_only_the_users_own_devices(): void
    {
        $user = User::factory()->parent()->create();
        $stranger = User::factory()->parent()->create();
        $this->seedSessions($user, $stranger);

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSee('Chrome di Windows')
            ->assertSee('Safari di iOS')
            ->assertSee('182.253.10.1')
            ->assertDontSee('36.70.0.9')
            ->assertSee('2 perangkat lain sedang masuk.');
    }

    public function test_other_devices_can_be_signed_out_with_the_password(): void
    {
        $user = User::factory()->parent()->create();
        $stranger = User::factory()->parent()->create();
        $this->seedSessions($user, $stranger);

        $this->actingAs($user)->delete(route('profile.sessions.destroy'), ['password' => 'password'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit').'#sesi');

        $this->assertDatabaseMissing('sessions', ['id' => 'laptop']);
        $this->assertDatabaseMissing('sessions', ['id' => 'ponsel']);
        $this->assertDatabaseHas('sessions', ['id' => 'orang-lain']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_signing_out_other_devices_requires_the_correct_password(): void
    {
        $user = User::factory()->parent()->create();
        $stranger = User::factory()->parent()->create();
        $this->seedSessions($user, $stranger);

        $this->actingAs($user)->from('/profile')->delete(route('profile.sessions.destroy'), ['password' => 'keliru'])
            ->assertSessionHasErrorsIn('logoutOtherDevices', 'password')
            ->assertRedirect('/profile');

        $this->assertDatabaseHas('sessions', ['id' => 'laptop']);
    }

    public function test_user_agents_are_described_in_indonesian(): void
    {
        $this->assertSame(
            ['browser' => 'Chrome', 'platform' => 'Windows', 'device' => 'desktop', 'icon' => 'computer-desktop'],
            UserSessionService::describeAgent(self::WINDOWS_CHROME)
        );
        $this->assertSame('mobile', UserSessionService::describeAgent(self::IPHONE_SAFARI)['device']);
        $this->assertSame('Safari', UserSessionService::describeAgent(self::IPHONE_SAFARI)['browser']);

        $unknown = UserSessionService::describeAgent(null);
        $this->assertSame('Peramban tidak dikenal', $unknown['browser']);
        $this->assertSame('sistem tidak dikenal', $unknown['platform']);
    }

    public function test_guests_cannot_sign_out_devices(): void
    {
        $this->delete(route('profile.sessions.destroy'), ['password' => 'password'])->assertRedirect(route('login'));
    }
}
