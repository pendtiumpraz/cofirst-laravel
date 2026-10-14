<?php

namespace Tests\Feature\DesignSystem;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The living style guide at /admin/ui-kit (admin + superadmin only).
 */
class UiKitPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_the_ui_kit(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.ui-kit'))
            ->assertOk()
            ->assertSee('UI Kit')
            ->assertSee('data-theme="a"', false)
            ->assertSee('x-ui.metric-strip')
            ->assertSee('Menampilkan', false);
    }

    public function test_superadmin_can_preview_theme_b_in_dark_mode(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)
            ->get(route('admin.ui-kit', ['theme' => 'b', 'mode' => 'dark', 'density' => 'compact', 'stat' => 'cards']))
            ->assertOk()
            ->assertSee('data-theme="b"', false)
            ->assertSee('class="dark"', false)
            ->assertSee('data-density="compact"', false)
            ->assertSee('data-stat="cards"', false);
    }

    public function test_invalid_preview_values_fall_back_to_defaults(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.ui-kit', ['theme' => 'z', 'mode' => '<script>']))
            ->assertOk()
            ->assertSee('data-theme="a"', false)
            ->assertDontSee('<script>"', false);
    }

    public function test_error_preview_renders_field_errors(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.ui-kit', ['errors' => 1]))
            ->assertOk()
            ->assertSee('Format email tidak valid')
            ->assertSee('aria-invalid="true"', false);
    }

    public function test_student_is_forbidden(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('admin.ui-kit'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.ui-kit'))->assertRedirect(route('login'));
    }
}
