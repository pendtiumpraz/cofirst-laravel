<?php

namespace Tests\Feature\Shell;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration of the app shell with the design system (design review, 13 Okt 2026):
 * the shell renders the real <x-ui.*> components and token classes, not private fallbacks.
 */
class ShellIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_shell_uses_design_system_components(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $view = $this->blade('<x-app-layout title="Integrasi">Isi</x-app-layout>');

        $view->assertSee('class="toast-region"', false);         // <x-ui.toast/>
        $view->assertSee('class="segmented', false);               // <x-ui.color-mode-toggle/> in the account menu
        $view->assertSee("localStorage.getItem('cf-color-mode')", false); // <x-ui.color-mode-script/>
        $view->assertSee('class="avatar', false);                  // <x-ui.avatar/>
        $view->assertSee('class="kbd"', false);                    // search trigger hint
        $view->assertSee('nav-label', false);                      // menu group labels
        $view->assertSee('bg-sidebar', false);                     // theme-aware sidebar surface
        $view->assertDontSee('cfColorMode', false);                // the private fallback is gone
    }

    public function test_account_button_has_one_accessible_name_and_palette_groups_are_named(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Ratna Dewi']);
        $this->actingAs($admin);

        $html = (string) $this->blade('<x-app-layout title="Integrasi">Isi</x-app-layout>');

        $this->assertStringContainsString('aria-label="Menu akun Ratna Dewi"', $html);
        $this->assertStringNotContainsString('<span class="sr-only">Menu akun', $html);
        // The unlabelled first menu group is 'Utama' in the command palette; only account items are 'Akun'.
        // (JSON inside @js() escapes quotes as ", hence the short wildcards.)
        $this->assertMatchesRegularExpression('/Beranda.{1,30}section.{1,14}Utama/', $html);
        $this->assertDoesNotMatchRegularExpression('/Beranda.{1,30}section.{1,14}Akun/', $html);
    }

    public function test_stat_never_truncates_a_figure(): void
    {
        $html = (string) $this->blade('<x-ui.stat label="Kehadiran rata-rata" value="Rp 48,2 jt" />');

        $this->assertStringContainsString('Rp 48,2 jt', $html);
        $this->assertStringNotContainsString('truncate', $html);
    }

    public function test_ui_kit_header_shows_the_brand_mark_of_the_previewed_theme(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.ui-kit', ['theme' => 'a']))->assertOk()->assertSee('#AE4027', false);
        $this->get(route('admin.ui-kit', ['theme' => 'b']))->assertOk()->assertSee('#EE4D1C', false);
    }

    public function test_centered_auth_layout_shows_the_stacked_lockup_with_descriptor(): void
    {
        setting()->set('appearance.auth_layout', 'centered');

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Sekolah coding · Mentor industri Jepang');
    }

    public function test_failure_case_guest_never_gets_shell_menus_or_account_button(): void
    {
        $html = (string) $this->blade('<x-app-layout title="Tamu">Isi</x-app-layout>');

        $this->assertStringNotContainsString('Menu akun', $html);
        $this->assertStringNotContainsString('id="cf-palette"', $html);
    }
}
