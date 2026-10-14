<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class EnsureUserIsActiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_users_keep_their_session(): void
    {
        $user = User::factory()->student()->create();

        $this->actingAs($user)->get('/profile')->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_deactivated_user_is_signed_out_on_the_next_request(): void
    {
        $user = User::factory()->teacher()->create();
        $this->actingAs($user)->get('/profile')->assertOk();

        $user->forceFill(['is_active' => false])->save();

        $response = $this->actingAs($user->fresh())->get('/profile');

        $response->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_a_deactivated_user_is_refused_through_a_remember_me_cookie(): void
    {
        $user = User::factory()->parent()->create(['remember_token' => str_repeat('r', 60)]);
        $guard = Auth::guard('web');
        $cookie = $user->id.'|'.$user->remember_token.'|'.$guard->hashPasswordForCookie($user->password);

        // Sanity check: the same cookie signs an active user in.
        $this->withCookie($guard->getRecallerName(), $cookie)->get('/profile')->assertOk();
        $this->assertAuthenticatedAs($user);

        Auth::guard('web')->logoutCurrentDevice();
        $this->flushSession();
        $user->forceFill(['is_active' => false])->save();
        $this->app['auth']->forgetGuards();

        $this->withCookie($guard->getRecallerName(), $cookie)->get('/profile')
            ->assertRedirect(route('login'));
        $this->assertGuest();
        // logout() rotated the token, so the stolen cookie is useless from now on.
        $this->assertNotSame(str_repeat('r', 60), $user->fresh()->remember_token);
    }

    public function test_json_requests_of_a_deactivated_user_get_401(): void
    {
        $user = User::factory()->student()->inactive()->create();

        $this->actingAs($user)->getJson('/profile')->assertUnauthorized()->assertJsonStructure(['message']);
        $this->assertGuest();
    }
}
