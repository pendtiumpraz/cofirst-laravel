<?php

namespace Tests\Feature\Privacy\Consent;

use App\Models\Privacy\PolicyDocument;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

/**
 * Public policy pages and the admin back-office for versioned documents.
 */
class PolicyDocumentTest extends TestCase
{
    use ConsentTestHelpers, RefreshDatabase;

    public function test_public_pages_show_the_current_versions_with_settings_filled_in(): void
    {
        $this->seedPrivacy();
        setting()->set('privacy.dpo_email', 'dpo@sekolah.test');
        setting()->set('privacy.controller_name', 'PT Contoh Edukasi');

        $this->get(route('privacy.notice'))->assertOk()
            ->assertSee('Kebijakan Privasi')
            ->assertSee('PT Contoh Edukasi')
            ->assertSee('dpo@sekolah.test')
            ->assertSee('Pasal 25')
            ->assertSee('3 × 24 jam')
            ->assertDontSee('{{dpo_email}}', false);

        foreach (['legal.terms' => 'Syarat & Ketentuan', 'privacy.cookies' => 'Kebijakan Cookie', 'privacy.children' => 'Untuk kamu, para pembelajar'] as $route => $text) {
            $this->get(route($route))->assertOk()->assertSee($text);
        }

        $this->get(route('privacy.history', ['jenis' => 'terms']))->assertOk()->assertSee('Versi 1.0');
    }

    public function test_bundled_text_is_shown_before_anything_is_published(): void
    {
        $this->seed(RoleSeeder::class);

        $this->assertSame(0, PolicyDocument::count());
        $this->get(route('privacy.notice'))->assertOk()->assertSee('Hak Anda');
        $this->get(route('privacy.history'))->assertOk()->assertSee('Belum ada versi yang diterbitkan');
    }

    public function test_markdown_is_rendered_safely(): void
    {
        $this->seedPrivacy();
        $admin = $this->makeUser('superadmin', 35);

        $this->actingAs($admin)->post(route('admin.privacy.policies.store'), [
            'type' => 'privacy_notice', 'version' => '1.1', 'title' => 'Kebijakan Privasi',
            'body' => "## Bagian\n\n<script>alert('x')</script>\n\n[klik](javascript:alert(1))",
        ])->assertRedirect();

        $doc = PolicyDocument::where('version', '1.1')->firstOrFail();
        $html = $this->actingAs($admin)->get(route('admin.privacy.policies.show', $doc))->assertOk()->getContent();

        $this->assertStringNotContainsString("<script>alert('x')</script>", $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('href="javascript:', $html);
    }

    public function test_superadmin_drafts_publishes_and_compares_versions(): void
    {
        $this->seedPrivacy();
        $admin = $this->makeUser('superadmin', 35);
        $v1 = PolicyDocument::where('type', 'terms')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.privacy.policies.index'))->assertOk()->assertSee('Kebijakan &amp; versi', false);
        $this->actingAs($admin)->get(route('admin.privacy.policies.create', ['jenis' => 'terms']))->assertOk()->assertSee('1.1');

        $this->actingAs($admin)->post(route('admin.privacy.policies.store'), [
            'type' => 'terms', 'version' => '1.1', 'title' => 'Syarat & Ketentuan',
            'body' => $v1->body."\n\n## 15. Tambahan\n\nKetentuan baru.", 'changelog' => 'Menambah bagian 15.',
        ])->assertRedirect();

        $draft = PolicyDocument::where('type', 'terms')->where('version', '1.1')->firstOrFail();
        $this->assertTrue($draft->isDraft());
        $this->assertSame(2, $draft->revision);

        $this->actingAs($admin)->get(route('admin.privacy.policies.diff', $draft))->assertOk()
            ->assertSee('Ketentuan baru.')
            ->assertSee('baris tidak berubah');

        $this->actingAs($admin)->post(route('admin.privacy.policies.publish', $draft), ['requires_reacceptance' => '1'])->assertRedirect();

        $draft->refresh();
        $this->assertTrue($draft->isPublished());
        $this->assertTrue($draft->requires_reacceptance);
        $this->get(route('legal.terms'))->assertSee('Ketentuan baru.')->assertSee('Versi 1.1');
        $this->get(route('privacy.history.show', $v1->id))->assertOk()->assertSee('Ini versi lama');
    }

    public function test_published_versions_are_immutable(): void
    {
        $this->seedPrivacy();
        $admin = $this->makeUser('superadmin', 35);
        $published = PolicyDocument::where('type', 'privacy_notice')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.privacy.policies.update', $published), [
            'version' => '1.0', 'title' => 'Diubah', 'body' => 'Isi lain',
        ])->assertRedirect(route('admin.privacy.policies.show', $published))->assertSessionHas('error');

        $this->actingAs($admin)->delete(route('admin.privacy.policies.destroy', $published))->assertSessionHas('error');
        $this->assertSame('Kebijakan Privasi', $published->fresh()->title);

        $this->expectException(LogicException::class);
        $published->update(['title' => 'Langsung']);
    }

    public function test_version_numbers_are_unique_per_type(): void
    {
        $this->seedPrivacy();
        $admin = $this->makeUser('superadmin', 35);

        $this->actingAs($admin)->post(route('admin.privacy.policies.store'), [
            'type' => 'privacy_notice', 'version' => '1.0', 'title' => 'X', 'body' => 'Y',
        ])->assertSessionHasErrors('version');
    }

    public function test_only_users_with_the_permission_manage_documents(): void
    {
        $this->seedPrivacy();

        $this->get(route('admin.privacy.policies.index'))->assertRedirect(route('login'));

        foreach (['admin', 'teacher', 'parent'] as $role) {
            $this->actingAs($this->makeUser($role, 35))->get(route('admin.privacy.policies.index'))->assertForbidden();
        }
    }
}
