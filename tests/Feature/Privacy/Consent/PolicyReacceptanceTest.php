<?php

namespace Tests\Feature\Privacy\Consent;

use App\Models\Privacy\PolicyAcceptance;
use App\Models\Privacy\PolicyDocument;
use App\Models\User;
use App\Services\Privacy\PolicyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * EnsurePolicyAccepted: a new privacy notice / terms version that requires re-acceptance
 * sends signed-in users to the acceptance page (UU PDP Pasal 21 ayat (2)).
 */
class PolicyReacceptanceTest extends TestCase
{
    use ConsentTestHelpers, RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPrivacy();
        Route::middleware('web')->get('/_uji/halaman', fn () => 'halaman biasa')->name('testing.page');
        $this->user = $this->makeUser('parent', 40);
    }

    /** Publish a new privacy notice version. */
    private function publishNotice(bool $reaccept = true, ?\DateTimeInterface $effective = null): PolicyDocument
    {
        $doc = PolicyDocument::create([
            'type' => 'privacy_notice', 'revision' => PolicyDocument::nextRevision('privacy_notice'), 'version' => '2.0',
            'title' => 'Kebijakan Privasi', 'body' => "## Perubahan\n\nPenyedia email baru.", 'changelog' => 'Penyedia email baru.',
            'status' => 'draft',
        ]);

        app(PolicyService::class)->publish($doc, $effective ? \Illuminate\Support\Carbon::instance($effective) : null, $reaccept, $this->makeUser('superadmin', 30));

        return $doc;
    }

    public function test_seeded_first_version_does_not_interrupt_users(): void
    {
        $this->actingAs($this->user)->get('/_uji/halaman')->assertOk()->assertSee('halaman biasa');
    }

    public function test_new_version_redirects_until_accepted(): void
    {
        $doc = $this->publishNotice();

        $this->actingAs($this->user)->get('/_uji/halaman')->assertRedirect(route('privacy.accept'));

        $this->actingAs($this->user)->get(route('privacy.accept'))
            ->assertOk()
            ->assertSee('Kami memperbarui kebijakan kami')
            ->assertSee('Penyedia email baru.');

        $this->actingAs($this->user)->post(route('privacy.accept.store'), ['accept' => '1', 'documents' => [$doc->id]])
            ->assertRedirect(url('/_uji/halaman'));

        $this->assertDatabaseHas('policy_acceptances', ['user_id' => $this->user->id, 'policy_document_id' => $doc->id, 'context' => 'reacceptance']);
        $this->actingAs($this->user)->get('/_uji/halaman')->assertOk();
    }

    public function test_acceptance_checkbox_is_required(): void
    {
        $doc = $this->publishNotice();

        $this->actingAs($this->user)->post(route('privacy.accept.store'), ['documents' => [$doc->id]])
            ->assertSessionHasErrors('accept');

        $this->assertSame(0, PolicyAcceptance::where('policy_document_id', $doc->id)->count());
    }

    public function test_privacy_pages_and_json_requests_stay_reachable(): void
    {
        $this->publishNotice();

        $this->actingAs($this->user)->get(route('privacy.notice'))->assertOk()->assertSee('Versi 2.0');
        $this->actingAs($this->user)->get(route('privacy.cookies.preferences'))->assertOk();
        $this->actingAs($this->user)->getJson('/_uji/halaman')->assertOk();
    }

    public function test_setting_can_turn_reacceptance_off(): void
    {
        $this->publishNotice();
        setting()->set('privacy.policy_reacceptance_required', false);

        $this->actingAs($this->user)->get('/_uji/halaman')->assertOk();
    }

    public function test_minor_change_without_reacceptance_does_not_redirect(): void
    {
        $this->publishNotice(reaccept: false);

        $this->actingAs($this->user)->get('/_uji/halaman')->assertOk();
    }

    public function test_scheduled_version_applies_only_from_its_effective_date(): void
    {
        $this->publishNotice(true, now()->addDays(14));

        $this->actingAs($this->user)->get('/_uji/halaman')->assertOk();
        $this->get(route('privacy.notice'))->assertSee('Akan ada perubahan')->assertSee('Versi 1.0');

        $this->travel(15)->days();
        $this->actingAs($this->user)->get('/_uji/halaman')->assertRedirect(route('privacy.accept'));
    }

    public function test_guests_are_never_redirected(): void
    {
        $this->publishNotice();

        $this->get('/_uji/halaman')->assertOk();
    }
}
