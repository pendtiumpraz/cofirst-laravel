<?php

namespace Tests\Feature\Privacy\Consent;

use App\Models\Privacy\ConsentRecord;
use App\Services\Privacy\CookieConsentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cookie banner and preferences: essential always on, optional categories opt-in,
 * first-party cookie for everyone, consent ledger for signed-in users.
 */
class CookieConsentTest extends TestCase
{
    use ConsentTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPrivacy();
    }

    public function test_banner_and_js_api_are_rendered_on_public_pages(): void
    {
        $this->get(route('privacy.notice'))
            ->assertOk()
            ->assertSee('window.CFConsent', false)
            ->assertSee('x-data="cfCookieBanner"', false)
            ->assertSee('Tolak opsional')
            ->assertSee('Terima semua');
    }

    public function test_banner_is_attached_once_to_the_dashboard_layout(): void
    {
        $admin = $this->makeUser('superadmin', 35);

        $html = $this->actingAs($admin)->get(route('admin.privacy.policies.index'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'x-data="cfCookieBanner"'));
    }

    public function test_banner_can_be_switched_off_but_the_api_remains(): void
    {
        setting()->set('privacy.cookie_banner_enabled', false);

        $this->get(route('privacy.notice'))->assertOk()
            ->assertSee('window.CFConsent', false)
            ->assertDontSee('x-data="cfCookieBanner"', false);
    }

    public function test_visitor_choice_is_stored_in_a_first_party_cookie_only(): void
    {
        $response = $this->postJson(route('privacy.cookies.update'), ['action' => 'accept_all'])
            ->assertOk()
            ->assertJson(['ok' => true, 'choices' => ['functional' => true, 'analytics' => true]]);

        $cookie = collect($response->headers->getCookies())->firstWhere(fn ($c) => $c->getName() === CookieConsentService::COOKIE);
        $this->assertNotNull($cookie);
        $this->assertFalse($cookie->isHttpOnly());
        $this->assertSame('lax', strtolower((string) $cookie->getSameSite()));
        $value = json_decode($cookie->getValue(), true);
        $this->assertTrue($value['analytics']);
        $this->assertSame(1, $value['v']);

        $this->assertSame(0, ConsentRecord::count());
    }

    public function test_signed_in_choice_is_recorded_and_withdrawal_is_logged(): void
    {
        $user = $this->makeUser('parent', 40);

        $this->actingAs($user)->post(route('privacy.cookies.update'), ['action' => 'custom', 'functional' => '1', 'analytics' => '0', 'source' => 'page'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('consent_records', ['subject_user_id' => $user->id, 'purpose_key' => 'cookies_functional', 'decision' => 'granted', 'method' => 'banner']);
        $this->assertDatabaseHas('consent_records', ['subject_user_id' => $user->id, 'purpose_key' => 'cookies_analytics', 'decision' => 'denied']);

        $this->actingAs($user)->postJson(route('privacy.cookies.update'), ['action' => 'reject_optional'])->assertOk();

        $this->assertDatabaseHas('consent_records', ['subject_user_id' => $user->id, 'purpose_key' => 'cookies_functional', 'decision' => 'withdrawn']);
        $this->assertSame(3, ConsentRecord::where('subject_user_id', $user->id)->count());
    }

    public function test_choice_made_on_another_device_hides_the_banner(): void
    {
        $user = $this->makeUser('parent', 40);
        $this->actingAs($user)->postJson(route('privacy.cookies.update'), ['action' => 'reject_optional'])->assertOk();

        $state = app(CookieConsentService::class)->state(request(), $user);

        $this->assertTrue($state['decided']);
        $this->assertFalse($state['choices']['functional']);
    }

    public function test_children_never_get_analytics(): void
    {
        $child = $this->makeUser('student', 11);

        $this->actingAs($child)->postJson(route('privacy.cookies.update'), ['action' => 'accept_all'])
            ->assertOk()
            ->assertJsonPath('choices.analytics', false);

        $this->assertDatabaseMissing('consent_records', ['subject_user_id' => $child->id, 'purpose_key' => 'cookies_analytics', 'decision' => 'granted']);
        $this->actingAs($child)->get(route('privacy.cookies.preferences'))->assertOk()->assertSee('Tidak tersedia untuk akun anak');
    }

    public function test_preferences_page_works_without_javascript(): void
    {
        $this->get(route('privacy.cookies.preferences'))
            ->assertOk()
            ->assertSee('Pengaturan cookie')
            ->assertSee('Simpan pilihan')
            ->assertSee('Selalu aktif');
    }

    public function test_invalid_choice_is_rejected(): void
    {
        $this->postJson(route('privacy.cookies.update'), ['action' => 'track_everything'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('action');
    }
}
