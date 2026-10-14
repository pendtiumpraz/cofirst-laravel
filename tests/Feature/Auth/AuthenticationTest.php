<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('Masuk ke akun Anda')
            ->assertSee('Ingat saya di perangkat ini')
            ->assertSee('Lupa kata sandi?')
            ->assertSee('Tampilkan kata sandi')
            ->assertSee('UU No. 27 Tahun 2022');
    }

    public function test_login_screen_does_not_publish_demo_credentials(): void
    {
        $content = $this->get('/login')->assertOk()->getContent();

        foreach (['superadmin@codingfirst.com', 'admin@codingfirst.com', 'finance@codingfirst.com', 'data-password', 'Demo Account'] as $leak) {
            $this->assertStringNotContainsString($leak, $content, "Login page must not contain '{$leak}'.");
        }
    }

    public function test_login_screen_renders_in_both_auth_layouts(): void
    {
        foreach (['split', 'centered'] as $layout) {
            setting()->set('appearance.auth_layout', $layout);

            $this->get('/login')->assertOk()->assertSee('Masuk ke akun Anda');
        }
    }

    public function test_users_can_authenticate_and_land_on_their_role_dashboard(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('student.dashboard', absolute: false));
        $response->assertSessionHas('active_role', 'student');
    }

    public function test_users_with_roles_on_different_dashboards_choose_a_role_first(): void
    {
        $user = User::factory()->parent()->student()->create();

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('role.selection'));
        $response->assertSessionMissing('active_role');
    }

    public function test_superadmin_who_is_also_admin_is_not_asked_to_choose(): void
    {
        $user = User::factory()->superadmin()->admin()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard', absolute: false))
            ->assertSessionHas('active_role', 'superadmin');
    }

    public function test_login_returns_to_the_page_the_user_wanted(): void
    {
        $user = User::factory()->teacher()->create();

        $this->get('/profile')->assertRedirect(route('login'));

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/profile');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->student()->create();

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email')->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_deactivated_users_are_refused_even_with_the_right_password(): void
    {
        $user = User::factory()->parent()->inactive()->create();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => '1',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertStringContainsString('tidak aktif', session('errors')->first('email'));
        $response->assertCookieMissing(Auth::guard('web')->getRecallerName());
    }

    public function test_users_without_a_dashboard_role_are_refused(): void
    {
        $user = User::factory()->create();

        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertStringContainsString('belum memiliki peran', session('errors')->first('email'));
    }

    public function test_login_is_throttled_with_the_limit_from_settings(): void
    {
        setting()->set('security.login_max_attempts', 3);
        $user = User::factory()->student()->create();

        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'salah']);
        }

        $response = $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(Str::before(trans('auth.throttle'), ':seconds'), session('errors')->first('email'));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
