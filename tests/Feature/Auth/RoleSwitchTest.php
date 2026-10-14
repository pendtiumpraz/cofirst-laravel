<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_redirects_to_the_active_role_dashboard(): void
    {
        $user = User::factory()->teacher()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('teacher.dashboard'));
    }

    public function test_dashboard_honours_the_chosen_role_of_a_multi_role_user(): void
    {
        $user = User::factory()->parent()->student()->create();

        $this->actingAs($user)->withSession(['active_role' => 'parent'])
            ->get('/dashboard')->assertRedirect(route('parent.dashboard'));
    }

    public function test_multi_role_user_without_a_choice_is_sent_to_role_selection(): void
    {
        $user = User::factory()->parent()->student()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('role.selection'));
    }

    public function test_user_without_any_role_is_signed_out_instead_of_looping(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_role_selection_page_lists_every_held_role(): void
    {
        $user = User::factory()->teacher()->parent()->create();

        $this->actingAs($user)->get(route('role.selection'))
            ->assertOk()
            ->assertSee('Masuk sebagai siapa hari ini?')
            ->assertSee('value="teacher"', false)
            ->assertSee('value="parent"', false)
            ->assertDontSee('value="admin"', false);
    }

    public function test_single_role_user_skips_role_selection(): void
    {
        $user = User::factory()->finance()->create();

        $this->actingAs($user)->get(route('role.selection'))->assertRedirect(route('dashboard'));
    }

    public function test_choosing_a_role_regenerates_the_session_and_opens_its_dashboard(): void
    {
        $user = User::factory()->parent()->student()->create();
        $this->actingAs($user)->get(route('role.selection'))->assertOk();
        $before = session()->getId();

        $response = $this->actingAs($user)->post(route('role.set'), ['role' => 'parent']);

        $response->assertRedirect(route('parent.dashboard', absolute: false))
            ->assertSessionHas('active_role', 'parent');
        $this->assertNotSame($before, session()->getId(), 'The session id must change on a role change.');
    }

    public function test_any_held_role_can_be_activated_not_only_parent_and_student(): void
    {
        $user = User::factory()->admin()->teacher()->create();

        $this->actingAs($user)->post(route('role.set'), ['role' => 'teacher'])
            ->assertRedirect(route('teacher.dashboard', absolute: false))
            ->assertSessionHas('active_role', 'teacher');
    }

    public function test_a_role_the_user_does_not_hold_is_rejected(): void
    {
        $user = User::factory()->parent()->student()->create();

        $this->actingAs($user)->withSession(['active_role' => 'student'])
            ->from(route('role.selection'))
            ->post(route('role.set'), ['role' => 'admin'])
            ->assertSessionHasErrors('role')
            ->assertSessionHas('active_role', 'student');
    }

    public function test_switch_role_endpoint_answers_json(): void
    {
        $user = User::factory()->parent()->student()->create();

        $this->actingAs($user)->postJson(route('role.switch'), ['role' => 'student'])
            ->assertOk()
            ->assertJson(['success' => true, 'role' => 'student', 'redirect' => route('student.dashboard')]);

        $this->actingAs($user)->postJson(route('role.switch'), ['role' => 'finance'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('role');
    }
}
