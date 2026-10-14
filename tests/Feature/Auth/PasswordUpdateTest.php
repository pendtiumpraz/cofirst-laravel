<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    private const STRONG = 'Belajar-Koding-2026';

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->from('/profile')->put('/password', [
            'current_password' => 'password',
            'password' => self::STRONG,
            'password_confirmation' => self::STRONG,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('profile.edit').'#keamanan');
        $this->assertTrue(Hash::check(self::STRONG, $user->refresh()->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->from('/profile')->put('/password', [
            'current_password' => 'wrong-password',
            'password' => self::STRONG,
            'password_confirmation' => self::STRONG,
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'current_password')->assertRedirect('/profile');
    }

    public function test_new_password_must_follow_the_password_settings(): void
    {
        $user = User::factory()->parent()->create();

        // Default policy: mixed case + numbers.
        $this->actingAs($user)->from('/profile')->put('/password', [
            'current_password' => 'password',
            'password' => 'kata-sandi-baru',
            'password_confirmation' => 'kata-sandi-baru',
        ])->assertSessionHasErrorsIn('updatePassword', 'password');

        // Minimum length comes from security.password_min_length.
        setting()->set('security.password_min_length', 20);
        $this->actingAs($user)->from('/profile')->put('/password', [
            'current_password' => 'password',
            'password' => self::STRONG,
            'password_confirmation' => self::STRONG,
        ])->assertSessionHasErrorsIn('updatePassword', 'password');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_profile_shows_the_requirements_from_settings(): void
    {
        setting()->set('security.password_min_length', 12);
        setting()->set('security.password_require_symbols', true);

        $this->actingAs(User::factory()->teacher()->create())->get('/profile')
            ->assertOk()
            ->assertSee('Minimal 12 karakter')
            ->assertSee('Mengandung simbol');
    }

    public function test_changing_the_password_signs_out_other_devices(): void
    {
        config(['session.driver' => 'database']);
        $user = User::factory()->student()->create();
        $other = User::factory()->student()->create();

        DB::table('sessions')->insert([
            ['id' => 'laptop-lama', 'user_id' => $user->id, 'ip_address' => '10.0.0.2', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0)', 'payload' => '', 'last_activity' => now()->subHour()->timestamp],
            ['id' => 'orang-lain', 'user_id' => $other->id, 'ip_address' => '10.0.0.3', 'user_agent' => 'Mozilla/5.0', 'payload' => '', 'last_activity' => now()->timestamp],
        ]);

        $this->actingAs($user)->put('/password', [
            'current_password' => 'password',
            'password' => self::STRONG,
            'password_confirmation' => self::STRONG,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('sessions', ['id' => 'laptop-lama']);
        $this->assertDatabaseHas('sessions', ['id' => 'orang-lain']);
    }
}
