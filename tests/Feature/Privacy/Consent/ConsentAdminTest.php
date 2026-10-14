<?php

namespace Tests\Feature\Privacy\Consent;

use App\Models\Privacy\ConsentPurpose;
use App\Services\Privacy\ConsentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin back-office: purpose register and the consent ledger (filters, CSV export).
 */
class ConsentAdminTest extends TestCase
{
    use ConsentTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPrivacy();
    }

    public function test_superadmin_manages_purposes(): void
    {
        $admin = $this->makeUser('superadmin', 35);

        $this->actingAs($admin)->get(route('admin.privacy.consent-purposes.index'))->assertOk()->assertSee('Publikasi foto dan karya');

        $this->actingAs($admin)->post(route('admin.privacy.consent-purposes.store'), [
            'key' => 'survey_feedback', 'name' => 'Survei kepuasan', 'description' => 'Mengirim survei singkat setelah kelas.',
            'category' => 'marketing', 'legal_basis' => 'consent', 'applies_to' => ['parent'], 'audience' => 'adults', 'is_active' => '1',
        ])->assertRedirect(route('admin.privacy.consent-purposes.index'));

        $purpose = ConsentPurpose::where('key', 'survey_feedback')->firstOrFail();
        $this->assertSame(1, $purpose->version);

        $this->actingAs($admin)->put(route('admin.privacy.consent-purposes.update', $purpose), [
            'key' => 'renamed_key', 'name' => 'Survei kepuasan orang tua', 'description' => 'Survei singkat setelah kelas.',
            'category' => 'marketing', 'legal_basis' => 'consent', 'applies_to' => ['parent'], 'audience' => 'adults',
            'is_active' => '1', 'bump_version' => '1',
        ])->assertRedirect();

        $purpose->refresh();
        $this->assertSame('survey_feedback', $purpose->key);
        $this->assertSame(2, $purpose->version);

        $this->actingAs($admin)->delete(route('admin.privacy.consent-purposes.destroy', $purpose))->assertRedirect();
        $this->assertNull(ConsentPurpose::where('key', 'survey_feedback')->first());
    }

    public function test_purpose_with_records_cannot_be_deleted(): void
    {
        $admin = $this->makeUser('superadmin', 35);
        $parent = $this->makeUser('parent', 40);
        app(ConsentService::class)->grant($parent, 'marketing_communications', 'privacy_centre', $parent);
        $purpose = ConsentPurpose::where('key', 'marketing_communications')->firstOrFail();

        $this->actingAs($admin)->delete(route('admin.privacy.consent-purposes.destroy', $purpose))->assertSessionHas('error');
        $this->assertNotNull($purpose->fresh());
    }

    public function test_invalid_purpose_is_rejected(): void
    {
        $this->actingAs($this->makeUser('superadmin', 35))->post(route('admin.privacy.consent-purposes.store'), [
            'key' => 'Bad Key', 'name' => '', 'category' => 'spam', 'legal_basis' => 'consent', 'applies_to' => [], 'audience' => 'all',
        ])->assertSessionHasErrors(['key', 'name', 'description', 'category', 'applies_to']);
    }

    public function test_consent_ledger_can_be_filtered_and_exported(): void
    {
        $admin = $this->makeUser('superadmin', 35);
        $parent = $this->makeUser('parent', 40, ['name' => '=HYPERLINK("x")', 'email' => 'ortu@example.test']);
        $other = $this->makeUser('parent', 41, ['name' => 'Orang Lain']);
        app(ConsentService::class)->grant($parent, 'marketing_communications', 'privacy_centre', $parent);
        app(ConsentService::class)->grant($other, 'testimonial_publication', 'privacy_centre', $other);

        $this->actingAs($admin)->get(route('admin.privacy.consent-records.index', ['purpose' => 'marketing_communications']))
            ->assertOk()
            ->assertSee('ortu@example.test')
            ->assertDontSee('Orang Lain');

        $csv = $this->actingAs($admin)->get(route('admin.privacy.consent-records.export', ['q' => 'ortu@example.test']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->streamedContent();

        $this->assertStringContainsString('Info program dan promo', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString('Orang Lain', $csv);
    }

    public function test_admin_can_read_the_ledger_but_not_edit_purposes(): void
    {
        $admin = $this->makeUser('admin', 35);

        $this->actingAs($admin)->get(route('admin.privacy.consent-records.index'))->assertOk()->assertSee('Belum ada catatan persetujuan');
        $this->actingAs($admin)->get(route('admin.privacy.consent-purposes.index'))->assertForbidden();
    }

    public function test_other_roles_cannot_see_the_ledger(): void
    {
        foreach (['teacher', 'finance', 'parent', 'student'] as $role) {
            $this->actingAs($this->makeUser($role, 30))->get(route('admin.privacy.consent-records.index'))->assertForbidden();
            $this->actingAs($this->makeUser($role, 30))->get(route('admin.privacy.consent-records.export'))->assertForbidden();
        }
    }
}
